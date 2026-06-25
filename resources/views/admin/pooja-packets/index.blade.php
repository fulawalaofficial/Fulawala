@extends('admin.layout')

@section('title','Pooja Packets')

@section('content')

<div class="space-y-8">

    <!-- Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-orange-600 via-orange-500 to-amber-400 p-8 shadow-xl">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-white"></div>
            <div class="absolute bottom-4 left-1/3 h-24 w-24 rounded-full border border-white"></div>
            <div class="absolute -bottom-16 left-10 h-44 w-44 rounded-full bg-white"></div>
        </div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-bold text-white backdrop-blur">
                    <span>🌸</span>
                    <span>Pooja Packet Management</span>
                </div>

                <h1 class="text-3xl font-black text-white sm:text-4xl">
                    Pooja Packets
                </h1>

                <p class="mt-3 max-w-2xl text-orange-50">
                    Create, manage and organize daily, weekly and monthly pooja flower packets for your customers.
                </p>
            </div>

            <a href="{{ route('admin.pooja-packets.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-6 py-4 text-sm font-black text-orange-700 shadow-lg transition hover:-translate-y-1 hover:shadow-xl">
                <span class="text-lg">＋</span>
                <span>Add New Packet</span>
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <p class="font-bold">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Stats -->
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500">Total Packets</p>
                    <p class="mt-2 text-3xl font-black text-orange-700">{{ $stats['total'] }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-2xl">
                    📦
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500">Active Packets</p>
                    <p class="mt-2 text-3xl font-black text-emerald-700">{{ $stats['active'] }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-2xl">
                    ✅
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500">Inactive Packets</p>
                    <p class="mt-2 text-3xl font-black text-red-600">{{ $stats['inactive'] }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-2xl">
                    ⏸️
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500">Average Price</p>
                    <p class="mt-2 text-3xl font-black text-amber-700">
                        ₹{{ number_format($stats['average_price'], 2) }}
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-2xl">
                    💰
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Box -->
    <div class="rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.pooja-packets.index') }}" class="grid gap-4 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-6">
                <label class="mb-2 block text-sm font-black text-gray-700">
                    Search Packet
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by packet name, flower, type..."
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div class="lg:col-span-3">
                <label class="mb-2 block text-sm font-black text-gray-700">
                    Status
                </label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold outline-none transition focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Status</option>
                    <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex gap-3 lg:col-span-3">
                <button
                    type="submit"
                    class="flex-1 rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-100 transition hover:bg-orange-700">
                    Filter
                </button>

                <a href="{{ route('admin.pooja-packets.index') }}"
                   class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-3 text-sm font-black text-gray-600 transition hover:bg-gray-100">
                    Clear
                </a>
            </div>

        </form>
    </div>

    <!-- Packets Table -->
    <div class="overflow-hidden rounded-3xl border border-orange-100 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-900">
                    Packet List
                </h2>
                <p class="text-sm text-gray-500">
                    Showing {{ $packets->count() }} packets on this page.
                </p>
            </div>

            <div class="rounded-full bg-orange-50 px-4 py-2 text-sm font-bold text-orange-700">
                Total Result: {{ $packets->total() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead>
                    <tr class="bg-orange-50 text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="px-6 py-4">Packet</th>
                        <th class="px-6 py-4">Included Flowers</th>
                        <th class="px-6 py-4 text-center">Monthly Price</th>
                        <th class="px-6 py-4 text-center">Weekly Price</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($packets as $packet)
                        @php
                            $flowers = $packet->included_flowers;

                            if (is_string($flowers)) {
                                $decodedFlowers = json_decode($flowers, true);
                                $flowers = json_last_error() === JSON_ERROR_NONE ? $decodedFlowers : explode(',', $flowers);
                            }

                            $flowers = array_values(array_filter((array) $flowers));
                        @endphp

                        <tr class="transition hover:bg-orange-50/50">

                            <!-- Packet Info -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-14 w-14 overflow-hidden rounded-2xl bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center text-2xl shadow-sm">
                                        @if(!empty($packet->image))
                                            <img
                                                src="{{ filter_var($packet->image, FILTER_VALIDATE_URL) ? $packet->image : asset($packet->image) }}"
                                                alt="{{ $packet->packet_name }}"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            🌼
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-black text-gray-900">
                                            {{ $packet->packet_name }}
                                        </p>

                                        <p class="mt-1 max-w-xs truncate text-xs text-gray-500">
                                            {{ $packet->description ?: 'No description added' }}
                                        </p>

                                        @if(!empty($packet->daily_quantity))
                                            <p class="mt-1 text-xs font-bold text-orange-700">
                                                Qty: {{ $packet->daily_quantity }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Flowers -->
                            <td class="px-6 py-5">
                                <div class="flex max-w-sm flex-wrap gap-2">
                                    @forelse($flowers as $flower)
                                        <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                                            {{ $flower }}
                                        </span>
                                    @empty
                                        <span class="text-xs font-semibold text-gray-400">
                                            No flowers added
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Monthly Price -->
                            <td class="px-6 py-5 text-center">
                                <span class="font-black text-gray-900">
                                    ₹{{ number_format($packet->monthly_price, 2) }}
                                </span>
                            </td>

                            <!-- Weekly Price -->
                            <td class="px-6 py-5 text-center">
                                @if($packet->weekly_price)
                                    <span class="font-black text-gray-900">
                                        ₹{{ number_format($packet->weekly_price, 2) }}
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-gray-400">Not set</span>
                                @endif
                            </td>

                            <!-- Package Type -->
                            <td class="px-6 py-5 text-center">
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                                    {{ $packet->package_type ?: 'General' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-5 text-center">
                                @if($packet->status === 'Active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-600">
                                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.pooja-packets.edit', $packet) }}"
                                       class="rounded-xl bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 transition hover:bg-orange-100">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.pooja-packets.destroy', $packet) }}"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Are you sure you want to delete this pooja packet?')"
                                            class="rounded-xl bg-red-50 px-4 py-2 text-xs font-black text-red-600 transition hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-3xl bg-orange-50 text-4xl">
                                        🌸
                                    </div>

                                    <h3 class="text-xl font-black text-gray-900">
                                        No pooja packets found
                                    </h3>

                                    <p class="mt-2 text-sm text-gray-500">
                                        Create your first pooja packet or change your search filters.
                                    </p>

                                    <a href="{{ route('admin.pooja-packets.create') }}"
                                       class="mt-5 inline-flex rounded-2xl bg-orange-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-100 transition hover:bg-orange-700">
                                        Add Packet
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div>
        {{ $packets->links() }}
    </div>

</div>

@endsection