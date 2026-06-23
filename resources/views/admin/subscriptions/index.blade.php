@extends('admin.layout')
@section('title','Subscriptions')
@section('content')
<h1 class="text-3xl font-black mb-6">Subscriptions</h1>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Customer</th><th class="p-3 text-left">Packet</th><th class="p-3">Start</th><th class="p-3">End</th><th class="p-3">Payment</th><th class="p-3">Status</th></tr></thead><tbody>
@foreach($subscriptions as $sub)<tr class="border-t"><td class="p-3 text-center">#{{ $sub->id }}</td><td class="p-3">{{ $sub->user->name ?? '-' }}</td><td class="p-3">{{ $sub->packet->packet_name ?? '-' }}</td><td class="p-3 text-center">{{ $sub->start_date?->format('d M Y') }}</td><td class="p-3 text-center">{{ $sub->end_date?->format('d M Y') }}</td><td class="p-3 text-center">{{ $sub->payment_status }}</td><td class="p-3 text-center">{{ $sub->subscription_status }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $subscriptions->links() }}</div>
@endsection
