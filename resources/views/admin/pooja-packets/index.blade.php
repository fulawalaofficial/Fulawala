@extends('admin.layout')

@section('title', 'Pooja Packets')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Subscription Management</p>
            <h1 class="text-3xl font-black text-slate-900">Pooja Packets</h1>
            <p class="text-slate-500 mt-1">Manage daily flower packets, pricing, duration and included flowers.</p>
        </div>

        <a href="{{ route('admin.pooja-packets.create') }}"
           class="inline-flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg shadow-orange-200 transition">
            + Add Packet
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white border border-orange-100 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-semibold">Total Packets</p>
            <h2 class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</h2>
        </div>

        <div class="bg-white border border-green-100 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-semibold">Active Packets</p>
            <h2 class="text-3xl font-black text-green-600 mt-1">{{ $stats['active'] }}</h2>
        </div>

        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-semibold">Inactive Packets</p>
            <h2 class="text-3xl font-black text-red-500 mt-1">{{ $stats['inactive'] }}</h2>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.pooja-packets.index') }}"
          class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <div class="grid md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 mb-1">Search Packet</label>
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search by packet name, type or description"
                       class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1">Status</label>
                <select name="status"
                        class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                    <option value="">All Status</option>
                    <option value="Active" @selected(($status ?? '') === 'Active')>Active</option>
                    <option value="Inactive" @selected(($status ?? '') === 'Inactive')>Inactive</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="w-full bg-slate-900 hover:bg-slate-800 text-white px-4 py-3 rounded-xl font-bold transition">
                    Filter
                </button>

                <a href="{{ route('admin.pooja-packets.index') }}"
                   class="px-4 py-3 rounded-xl border border-slate-200 font-bold text-slate-600 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-orange-50 border-b border-orange-100">
                    <tr>
                        <th class="p-4 text-left text-slate-700">Packet</th>
                        <th class="p-4 text-left text-slate-700">Included Flowers</th>
                        <th class="p-4 text-center text-slate-700">Duration</th>
                        <th class="p-4 text-center text-slate-700">MRP Total</th>
                        <th class="p-4 text-center text-slate-700">Sale Total</th>
                        <th class="p-4 text-center text-slate-700">Monthly Price</th>
                        <th class="p-4 text-center text-slate-700">Status</th>
                        <th class="p-4 text-center text-slate-700">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($packets as $packet)
                        <tr class="border-b border-slate-100 hover:bg-orange-50/40 transition">
                            <td class="p-4">
                                <div class="font-black text-slate-900">{{ $packet->packet_name }}</div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $packet->package_type ?: 'General Packet' }}
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="space-y-1">
                                    @foreach($packet->flower_items as $item)
                                        <div class="inline-flex items-center bg-slate-100 text-slate-700 rounded-full px-3 py-1 mr-1 mb-1 text-xs font-semibold">
                                            {{ $item['flower_name'] }}
                                            @if(!empty($item['quantity']))
                                                - {{ $item['quantity'] }} {{ $item['unit'] }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="p-4 text-center font-bold text-slate-700">
                                {{ $packet->duration_label }}
                            </td>

                            <td class="p-4 text-center font-bold text-slate-700">
                                ₹{{ number_format($packet->items_total_mrp, 2) }}
                            </td>

                            <td class="p-4 text-center font-bold text-green-700">
                                ₹{{ number_format($packet->items_total_sale, 2) }}
                            </td>

                            <td class="p-4 text-center font-black text-orange-700">
                                ₹{{ number_format((float) $packet->monthly_price, 2) }}
                            </td>

                            <td class="p-4 text-center">
                                @if($packet->status === 'Active')
                                    <span class="inline-flex px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-black">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('admin.pooja-packets.edit', $packet) }}"
                                       class="px-3 py-2 rounded-lg bg-orange-100 text-orange-700 font-black hover:bg-orange-200 transition">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.pooja-packets.destroy', $packet) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Delete this pooja packet?')"
                                                class="px-3 py-2 rounded-lg bg-red-100 text-red-700 font-black hover:bg-red-200 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center">
                                <div class="text-slate-400 text-lg font-bold">No pooja packets found.</div>
                                <a href="{{ route('admin.pooja-packets.create') }}"
                                   class="inline-flex mt-4 bg-orange-600 text-white px-5 py-3 rounded-xl font-bold">
                                    Add First Packet
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $packets->links() }}
    </div>

</div>
@endsection