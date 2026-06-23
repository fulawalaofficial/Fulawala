@extends('admin.layout')
@section('title','Customers')
@section('content')
<h1 class="text-3xl font-black mb-6">Customer Management</h1>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3">ID</th><th class="p-3 text-left">Name</th><th class="p-3">Mobile</th><th class="p-3">Email</th><th class="p-3">Status</th></tr></thead><tbody>@foreach($customers as $c)<tr class="border-t"><td class="p-3 text-center">#{{ $c->id }}</td><td class="p-3 font-bold">{{ $c->name }}</td><td class="p-3 text-center">{{ $c->mobile }}</td><td class="p-3 text-center">{{ $c->email }}</td><td class="p-3 text-center">{{ $c->status }}</td></tr>@endforeach</tbody></table></div><div class="mt-4">{{ $customers->links() }}</div>
@endsection
