<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use App\Models\FlowerProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomOrderController extends Controller
{
    /**
     * Create a new custom flower order.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.flower_product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('flower_products', 'id')
                    ->where(function ($query) {
                        $query->where('status', 'Active');
                    }),
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'delivery_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'delivery_slot' => [
                'required',
                'string',
                'max:100',
            ],

            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    }),
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
                'required_without:address_id',
            ],

            'address_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'number' => [
                'nullable',
                'string',
                'max:20',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:10',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        try {
            $order = DB::transaction(function () use ($user, $data) {
                $addressId = $data['address_id'] ?? null;

                /*
                 * Create an address only when an existing address_id
                 * was not provided.
                 */
                if (!$addressId) {
                    $address = Address::create([
                        'user_id' => $user->id,
                        'address_type' => $data['address_type'] ?? 'home',
                        'name' => $data['name'] ?? $user->name ?? 'Customer',
                        'number' => $data['number'] ?? $user->mobile ?? '',
                        'address' => $data['address'],
                        'city' => $data['city'] ?? '',
                        'state' => $data['state'] ?? '',
                        'pincode' => $data['pincode'] ?? '',
                        'landmark' => $data['landmark'] ?? null,
                        'is_default' => false,
                    ]);

                    $addressId = $address->id;
                }

                $productIds = collect($data['items'])
                    ->pluck('flower_product_id')
                    ->unique()
                    ->values();

                $products = FlowerProduct::query()
                    ->whereIn('id', $productIds)
                    ->where('status', 'Active')
                    ->get()
                    ->keyBy('id');

                $subtotal = 0;
                $lineItems = [];

                foreach ($data['items'] as $item) {
                    $product = $products->get(
                        $item['flower_product_id']
                    );

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => [
                                'One or more selected flower products are unavailable.',
                            ],
                        ]);
                    }

                    $quantity = (int) $item['quantity'];
                    $price = round((float) $product->price, 2);
                    $lineTotal = round($price * $quantity, 2);

                    $subtotal += $lineTotal;

                    $lineItems[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $lineTotal,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $deliveryCharge = $subtotal > 0 ? 40.00 : 0.00;
                $totalAmount = round(
                    $subtotal + $deliveryCharge,
                    2
                );

                $order = CustomOrder::create([
                    'user_id' => $user->id,
                    'address_id' => $addressId,
                    'delivery_date' => $data['delivery_date'],
                    'delivery_slot' => $data['delivery_slot'],
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryCharge,
                    'total_amount' => $totalAmount,
                    'payment_status' => 'Pending',
                    'order_status' => 'Payment Pending',
                ]);

                foreach ($lineItems as $lineItem) {
                    CustomOrderItem::create([
                        'order_id' => $order->id,
                        'flower_product_id' =>
                            $lineItem['product']->id,

                        'quantity' =>
                            $lineItem['quantity'],

                        'unit' =>
                            $lineItem['product']->unit,

                        'price' =>
                            $lineItem['price'],

                        'total' =>
                            $lineItem['total'],
                    ]);
                }

                return $order;
            });

            $freshOrder = $order
                ->fresh()
                ->load([
                    'items.flower',
                    'address',
                ]);

            return response()->json([
                'status' => true,
                'message' =>
                    'Custom order created. Complete payment to confirm the order.',
                'data' => $this->transformOrder($freshOrder),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create custom order.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Return custom orders for the logged-in user.
     */
    public function myOrders(Request $request)
    {
        try {
            $orders = $request->user()
                ->customOrders()
                ->with([
                    'items.flower',
                    'address',
                ])
                ->latest('id')
                ->get()
                ->map(function (CustomOrder $order) {
                    return $this->transformOrder($order);
                })
                ->values();

            return response()->json([
                'status' => true,
                'message' => 'Orders loaded successfully.',
                'data' => $orders,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Unable to load orders.',
                'data' => [],
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * Convert an order into a frontend-safe response.
     *
     * Important:
     * "address" is returned as a string, not an object.
     */
    private function transformOrder(CustomOrder $order): array
    {
        $address = $order->address;

        $addressText = $this->formatAddress($address);

        return [
            'id' => (int) $order->id,
            'user_id' => (int) $order->user_id,
            'address_id' => $order->address_id
                ? (int) $order->address_id
                : null,

            'delivery_date' => $order->delivery_date
                ? $order->delivery_date->format('Y-m-d')
                : null,

            'delivery_slot' => (string) ($order->delivery_slot ?? ''),

            'subtotal' => number_format(
                (float) $order->subtotal,
                2,
                '.',
                ''
            ),

            'delivery_charge' => number_format(
                (float) $order->delivery_charge,
                2,
                '.',
                ''
            ),

            'total_amount' => number_format(
                (float) $order->total_amount,
                2,
                '.',
                ''
            ),

            'payment_status' => (string) (
                $order->payment_status ?? 'Pending'
            ),

            'order_status' => (string) (
                $order->order_status ?? 'Pending'
            ),

            /*
             * Frontend-safe address string.
             */
            'address' => $addressText,
            'address_text' => $addressText,
            'delivery_address' => $addressText,

            /*
             * Original structured address is available separately.
             * Do not display this object directly inside React Native Text.
             */
            'address_data' => $address
                ? [
                    'id' => (int) $address->id,
                    'user_id' => (int) $address->user_id,
                    'address_type' => (string) (
                        $address->address_type ?? ''
                    ),
                    'name' => (string) ($address->name ?? ''),
                    'number' => (string) ($address->number ?? ''),
                    'address' => (string) ($address->address ?? ''),
                    'city' => (string) ($address->city ?? ''),
                    'state' => (string) ($address->state ?? ''),
                    'pincode' => (string) ($address->pincode ?? ''),
                    'landmark' => (string) ($address->landmark ?? ''),
                    'is_default' => (bool) $address->is_default,
                ]
                : null,

            /*
             * Return item data as simple scalar values.
             */
            'items' => $order->items
                ->map(function (CustomOrderItem $item) {
                    return [
                        'id' => (int) $item->id,

                        'order_id' => (int) $item->order_id,

                        'flower_product_id' =>
                            (int) $item->flower_product_id,

                        'flower_name' => (string) (
                            $item->flower?->flower_name
                            ?? 'Flower item'
                        ),

                        'quantity' => (int) $item->quantity,

                        'unit' => (string) ($item->unit ?? ''),

                        'price' => number_format(
                            (float) $item->price,
                            2,
                            '.',
                            ''
                        ),

                        'total' => number_format(
                            (float) $item->total,
                            2,
                            '.',
                            ''
                        ),
                    ];
                })
                ->values()
                ->all(),

            'created_at' => $order->created_at
                ? $order->created_at->toISOString()
                : null,

            'updated_at' => $order->updated_at
                ? $order->updated_at->toISOString()
                : null,
        ];
    }

    /**
     * Convert the Address model into readable text.
     */
    private function formatAddress(?Address $address): string
    {
        if (!$address) {
            return 'Address not added';
        }

        $parts = collect([
            $address->name,
            $address->number,
            $address->address,
            $address->city,
            $address->state,
            $address->pincode,
            $address->landmark,
        ])
            ->map(function ($value) {
                return is_scalar($value)
                    ? trim((string) $value)
                    : '';
            })
            ->filter(function ($value) {
                return $value !== '';
            })
            ->values();

        return $parts->isNotEmpty()
            ? $parts->implode(', ')
            : 'Address not added';
    }
}