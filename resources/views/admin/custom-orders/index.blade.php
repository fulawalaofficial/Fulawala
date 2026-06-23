@extends('admin.layout')
@section('title','Custom Orders')
@section('content')
<h1 class="text-3xl font-black mb-6">Custom Flower Orders</h1>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Customer</th><th class="p-3">Amount</th><th class="p-3">Date</th><th class="p-3">Slot</th><th class="p-3">Payment</th><th class="p-3">Status</th></tr></thead><tbody>
@foreach($orders as $order)<tr class="border-t"><td class="p-3 text-center">#{{ $order->id }}</td><td class="p-3">{{ $order->user->name ?? '-' }}</td><td class="p-3 text-center">₹{{ $order->total_amount }}</td><td class="p-3 text-center">{{ $order->delivery_date?->format('d M Y') }}</td><td class="p-3 text-center">{{ $order->delivery_slot }}</td><td class="p-3 text-center">{{ $order->payment_status }}</td><td class="p-3 text-center">{{ $order->order_status }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $orders->links() }}</div>
@endsection
