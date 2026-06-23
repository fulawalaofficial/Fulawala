@extends('admin.layout')
@section('title','Event Bookings')
@section('content')
<h1 class="text-3xl font-black mb-6">Event Bookings</h1>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Customer</th><th class="p-3">Type</th><th class="p-3">Date</th><th class="p-3 text-left">Venue</th><th class="p-3">Budget</th><th class="p-3">Status</th></tr></thead><tbody>
@foreach($bookings as $b)<tr class="border-t"><td class="p-3 text-center">#{{ $b->id }}</td><td class="p-3">{{ $b->user->name ?? '-' }}</td><td class="p-3 text-center">{{ $b->event_type }}</td><td class="p-3 text-center">{{ $b->event_date?->format('d M Y') }}</td><td class="p-3">{{ $b->venue_address }}</td><td class="p-3 text-center">₹{{ $b->budget }}</td><td class="p-3 text-center">{{ $b->booking_status }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $bookings->links() }}</div>
@endsection
