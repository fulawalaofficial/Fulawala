@extends('admin.layout')
@section('title','Pooja Packets')
@section('content')
<div class="flex justify-between items-center mb-6"><h1 class="text-3xl font-black">Pooja Packets</h1><a href="{{ route('admin.pooja-packets.create') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg">Add Packet</a></div>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3 text-left">Name</th><th class="p-3 text-left">Flowers</th><th class="p-3">Monthly Price</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($packets as $packet)<tr class="border-t"><td class="p-3 font-bold">{{ $packet->packet_name }}</td><td class="p-3">{{ implode(', ', $packet->included_flowers ?? []) }}</td><td class="p-3 text-center">₹{{ $packet->monthly_price }}</td><td class="p-3 text-center">{{ $packet->status }}</td><td class="p-3 text-center"><a class="text-orange-700 font-bold" href="{{ route('admin.pooja-packets.edit',$packet) }}">Edit</a><form method="POST" action="{{ route('admin.pooja-packets.destroy',$packet) }}" class="inline">@csrf @method('DELETE') <button class="text-red-600 ml-2" onclick="return confirm('Delete packet?')">Delete</button></form></td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $packets->links() }}</div>
@endsection
