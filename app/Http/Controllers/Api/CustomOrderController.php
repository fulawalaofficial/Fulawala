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
     * Create a custom order.
     *
     * This method does not mark the order as paid.
     * Payment will be handled separately through Razorpay.
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
                 * Create a new address only when address_id
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

                $products = FlowerProduct::whereIn('id', $productIds)
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

                /*
                 * Order remains pending until Razorpay verification.
                 */
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

            return response()->json([
                'status' => true,
                'message' => 'Custom order created. Complete payment to confirm the order.',
                'data' => $order->fresh()->load([
                    'items.flower',
                    'address',
                ]),
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
     * Return the logged-in user's custom orders.
     */
    public function myOrders(Request $request)
    {
        $orders = $request->user()
            ->customOrders()
            ->with([
                'items.flower',
                'address',
            ])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }
}