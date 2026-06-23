@extends('admin.layout')
@section('title','Quotations')
@section('content')
<h1 class="text-3xl font-black mb-6">Quotation Management</h1>
<form method="POST" action="{{ route('admin.quotations.store') }}" class="bg-white border rounded-2xl p-5 grid md:grid-cols-2 gap-4 mb-6">@csrf
    <select name="booking_id" class="border rounded-lg px-4 py-3"><option value="">Select booking</option>@foreach($bookings as $b)<option value="{{ $b->id }}">#{{ $b->id }} - {{ $b->event_type }} - {{ $b->user->name ?? '' }}</option>@endforeach</select>
    <input name="total_amount" placeholder="Total amount" class="border rounded-lg px-4 py-3">
    <input name="advance_amount" placeholder="Advance amount" class="border rounded-lg px-4 py-3">
    <input name="terms" placeholder="Terms" class="border rounded-lg px-4 py-3">
    <textarea name="decoration_details" placeholder="Decoration details" class="border rounded-lg px-4 py-3 md:col-span-2"></textarea>
    <button class="bg-orange-600 text-white rounded-lg py-3 font-bold md:col-span-2">Send Quotation</button>
</form>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Customer</th><th class="p-3">Total</th><th class="p-3">Advance</th><th class="p-3">Balance</th><th class="p-3">Status</th></tr></thead><tbody>
@foreach($quotations as $q)<tr class="border-t"><td class="p-3 text-center">#{{ $q->id }}</td><td class="p-3">{{ $q->booking->user->name ?? '-' }}</td><td class="p-3 text-center">₹{{ $q->total_amount }}</td><td class="p-3 text-center">₹{{ $q->advance_amount }}</td><td class="p-3 text-center">₹{{ $q->balance_amount }}</td><td class="p-3 text-center">{{ $q->quotation_status }}</td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $quotations->links() }}</div>
@endsection
