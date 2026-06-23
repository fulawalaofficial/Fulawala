@extends('admin.layout')
@section('title','Flower Product Form')
@section('content')
<h1 class="text-3xl font-black mb-6">{{ $flower->exists ? 'Edit' : 'Add' }} Flower Product</h1>
<form method="POST" action="{{ $flower->exists ? route('admin.flowers.update',$flower) : route('admin.flowers.store') }}" class="bg-white border rounded-2xl p-6 grid md:grid-cols-2 gap-4">@csrf @if($flower->exists) @method('PUT') @endif
    <input name="flower_name" value="{{ old('flower_name',$flower->flower_name) }}" placeholder="Flower name" class="border rounded-lg px-4 py-3">
    <input name="category" value="{{ old('category',$flower->category) }}" placeholder="Category" class="border rounded-lg px-4 py-3">
    <input name="price" value="{{ old('price',$flower->price) }}" placeholder="Price" class="border rounded-lg px-4 py-3">
    <input name="unit" value="{{ old('unit',$flower->unit) }}" placeholder="Unit e.g. 500g, 20 pieces" class="border rounded-lg px-4 py-3">
    <select name="stock_status" class="border rounded-lg px-4 py-3"><option @selected(old('stock_status',$flower->stock_status)==='In Stock')>In Stock</option><option @selected(old('stock_status',$flower->stock_status)==='Out of Stock')>Out of Stock</option></select>
    <select name="status" class="border rounded-lg px-4 py-3"><option @selected(old('status',$flower->status)==='Active')>Active</option><option @selected(old('status',$flower->status)==='Inactive')>Inactive</option></select>
    <textarea name="description" placeholder="Description" class="border rounded-lg px-4 py-3 md:col-span-2">{{ old('description',$flower->description) }}</textarea>
    <button class="bg-orange-600 text-white px-5 py-3 rounded-lg font-bold md:col-span-2">Save Flower</button>
</form>
@endsection
