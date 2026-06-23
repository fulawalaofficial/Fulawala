<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use App\Models\FlowerProduct;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomOrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required','array','min:1'],
            'items.*.flower_product_id' => ['required','exists:flower_products,id'],
            'items.*.quantity' => ['required','integer','min:1'],
            'delivery_date' => ['required','date'],
            'delivery_slot' => ['required','string'],
            'address' => ['nullable','string'],
            'address_id' => ['nullable','exists:addresses,id'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $addressId = $data['address_id'] ?? null;
            if (!$addressId) {
                $address = Address::create([
                    'user_id' => $request->user()->id,
                    'address' => $data['address'] ?? 'Default address',
                    'city' => '', 'state' => '', 'pincode' => '',
                    'is_default' => false,
                ]);
                $addressId = $address->id;
            }

            $subtotal = 0;
            $lineItems = [];
            foreach ($data['items'] as $item) {
                $product = FlowerProduct::findOrFail($item['flower_product_id']);
                $total = (float) $product->price * (int) $item['quantity'];
                $subtotal += $total;
                $lineItems[] = compact('product', 'item', 'total');
            }

            $deliveryCharge = $subtotal > 0 ? 40 : 0;
            $order = CustomOrder::create([
                'user_id' => $request->user()->id,
                'address_id' => $addressId,
                'delivery_date' => $data['delivery_date'],
                'delivery_slot' => $data['delivery_slot'],
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $subtotal + $deliveryCharge,
                'payment_status' => 'Paid',
                'order_status' => 'Order Placed',
            ]);

            foreach ($lineItems as $line) {
                CustomOrderItem::create([
                    'order_id' => $order->id,
                    'flower_product_id' => $line['product']->id,
                    'quantity' => $line['item']['quantity'],
                    'unit' => $line['product']->unit,
                    'price' => $line['product']->price,
                    'total' => $line['total'],
                ]);
            }

            Payment::create([
                'user_id' => $request->user()->id,
                'payment_type' => 'custom_order',
                'reference_id' => $order->id,
                'amount' => $order->total_amount,
                'razorpay_order_id' => 'mock_order_'.uniqid(),
                'razorpay_payment_id' => 'mock_payment_'.uniqid(),
                'payment_status' => 'Paid',
            ]);

            return response()->json($order->load(['items.flower','address']), 201);
        });
    }

    public function myOrders(Request $request)
    {
        return response()->json($request->user()->customOrders()->with(['items.flower','address'])->latest()->get());
    }
}
