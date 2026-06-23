@extends('admin.layout')
@section('title','Flower Products')
@section('content')
<div class="flex justify-between items-center mb-6"><h1 class="text-3xl font-black">Flower Products</h1><a href="{{ route('admin.flowers.create') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg">Add Flower</a></div>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3 text-left">Name</th><th class="p-3">Category</th><th class="p-3">Price</th><th class="p-3">Stock</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($flowers as $flower)<tr class="border-t"><td class="p-3 font-bold">{{ $flower->flower_name }}</td><td class="p-3 text-center">{{ $flower->category }}</td><td class="p-3 text-center">₹{{ $flower->price }}/{{ $flower->unit }}</td><td class="p-3 text-center">{{ $flower->stock_status }}</td><td class="p-3 text-center">{{ $flower->status }}</td><td class="p-3 text-center"><a class="text-orange-700 font-bold" href="{{ route('admin.flowers.edit',$flower) }}">Edit</a><form method="POST" action="{{ route('admin.flowers.destroy',$flower) }}" class="inline">@csrf @method('DELETE') <button class="text-red-600 ml-2" onclick="return confirm('Delete flower?')">Delete</button></form></td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $flowers->links() }}</div>
@endsection
