@extends('admin.layout')
@section('title','Daily Deliveries')
@section('content')
<h1 class="text-3xl font-black mb-6">Daily Subscription Deliveries</h1>
<p class="mb-4 text-gray-500">Run <code>php artisan app:generate-daily-deliveries</code> to auto-generate deliveries for active subscriptions.</p>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3">Date</th><th class="p-3 text-left">Subscription</th><th class="p-3">Fixed Time</th><th class="p-3">Delivery Boy</th><th class="p-3">Status</th></tr></thead><tbody>
@foreach($deliveries as $d)<tr class="border-t"><td class="p-3 text-center">#{{ $d->id }}</td><td class="p-3 text-center">{{ $d->delivery_date?->format('d M Y') }}</td><td class="p-3">{{ $d->subscription->packet->packet_name ?? '-' }}</td><td class="p-3 text-center">{{ $d->fixed_delivery_time }}</td><td class="p-3 text-center">{{ $d->deliveryBoy->name ?? '-' }}</td><td class="p-3 text-center">{{ $d->delivery_status }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $deliveries->links() }}</div>
@endsection
