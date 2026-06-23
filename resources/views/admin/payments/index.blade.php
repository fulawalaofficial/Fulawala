@extends('admin.layout')
@section('title','Payments')
@section('content')
<h1 class="text-3xl font-black mb-6">Payment Management</h1>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Customer</th><th class="p-3">Type</th><th class="p-3">Amount</th><th class="p-3">Razorpay Order</th><th class="p-3">Status</th><th class="p-3">Date</th></tr></thead><tbody>
@foreach($payments as $p)<tr class="border-t"><td class="p-3 text-center">#{{ $p->id }}</td><td class="p-3">{{ $p->user->name ?? '-' }}</td><td class="p-3 text-center">{{ $p->payment_type }}</td><td class="p-3 text-center">₹{{ $p->amount }}</td><td class="p-3 text-center">{{ $p->razorpay_order_id }}</td><td class="p-3 text-center">{{ $p->payment_status }}</td><td class="p-3 text-center">{{ $p->created_at?->format('d M Y') }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $payments->links() }}</div>
@endsection
