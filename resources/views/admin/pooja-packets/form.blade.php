@extends('admin.layout')
@section('title','Pooja Packet Form')
@section('content')
<h1 class="text-3xl font-black mb-6">{{ $packet->exists ? 'Edit' : 'Add' }} Pooja Packet</h1>
<form method="POST" action="{{ $packet->exists ? route('admin.pooja-packets.update',$packet) : route('admin.pooja-packets.store') }}" class="bg-white border rounded-2xl p-6 grid md:grid-cols-2 gap-4">@csrf @if($packet->exists) @method('PUT') @endif
    <input name="packet_name" value="{{ old('packet_name',$packet->packet_name) }}" placeholder="Packet name" class="border rounded-lg px-4 py-3">
    <input name="monthly_price" value="{{ old('monthly_price',$packet->monthly_price) }}" placeholder="Monthly price" class="border rounded-lg px-4 py-3">
    <input name="weekly_price" value="{{ old('weekly_price',$packet->weekly_price) }}" placeholder="Weekly price optional" class="border rounded-lg px-4 py-3">
    <input name="daily_quantity" value="{{ old('daily_quantity',$packet->daily_quantity) }}" placeholder="Daily quantity" class="border rounded-lg px-4 py-3">
    <input name="package_type" value="{{ old('package_type',$packet->package_type) }}" placeholder="Package type" class="border rounded-lg px-4 py-3">
    <select name="status" class="border rounded-lg px-4 py-3"><option @selected(old('status',$packet->status)==='Active')>Active</option><option @selected(old('status',$packet->status)==='Inactive')>Inactive</option></select>
    <textarea name="included_flowers" placeholder="Included flowers comma separated" class="border rounded-lg px-4 py-3 md:col-span-2">{{ old('included_flowers', is_array($packet->included_flowers) ? implode(', ', $packet->included_flowers) : $packet->included_flowers) }}</textarea>
    <textarea name="description" placeholder="Description" class="border rounded-lg px-4 py-3 md:col-span-2">{{ old('description',$packet->description) }}</textarea>
    <button class="bg-orange-600 text-white px-5 py-3 rounded-lg font-bold md:col-span-2">Save Packet</button>
</form>
@endsection
