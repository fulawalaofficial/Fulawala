@extends('admin.layout')

@section('title', 'Daily Deliveries')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-8 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">
                    🚚 Fulawala Delivery Center
                </div>

                <h1 class="mt-4 text-3xl md:text-4xl font-black tracking-tight">
                    Daily Subscription Deliveries
                </h1>

                <p class="mt-2 max-w-2xl text-white/90">
                    Track daily pooja packet deliveries, assign delivery boys, update delivery status and manage failed delivery reasons easily.
                </p>

                <div class="mt-4 rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold backdrop-blur">
                    Auto command:
                    <code class="rounded-lg bg-black/20 px-2 py-1">php artisan app:generate-daily-deliveries</code>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.daily-deliveries.generate-today') }}">
                @csrf
                <button
                    type="submit"
                    class="rounded-2xl bg-white px-6 py-4 text-sm font-black text-orange-700 shadow-lg transition hover:scale-[1.02] hover:bg-orange-50"
                >
                    ✨ Generate Today Deliveries
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-semibold text-green-800 shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-800 shadow-sm">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Total</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['total'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">📦</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Today</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['today'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">📅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Pending</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['pending'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-100 text-2xl">⏳</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Delivered</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['delivered'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">✅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Failed</p>
                    <h3 class="mt-2 text-3xl font-black text-gray-900">{{ $stats['failed'] ?? 0 }}</h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-100 text-2xl">⚠️</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.daily-deliveries.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-black text-gray-700">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="ID, customer, packet, delivery boy..."
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Status</option>
                    @foreach($statusOptions as $item)
                        <option value="{{ $item }}" @selected(($filters['status'] ?? '') == $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Delivery Boy</label>
                <select
                    name="delivery_boy_id"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Boys</option>
                    @foreach($deliveryBoys as $boy)
                        <option value="{{ $boy->id }}" @selected(($filters['delivery_boy_id'] ?? '') == $boy->id)>
                            {{ $boy->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">From Date</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">To Date</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div class="md:col-span-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('admin.daily-deliveries.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>

                <button
                    type="submit"
                    class="rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-3 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:scale-[1.01]"
                >
                    Apply Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Deliveries --}}
    <div class="space-y-4">
        @forelse($deliveries as $d)
            @php
                $statusText = $d->delivery_status ?: 'Pending';

                $statusClass = match(strtolower((string) $statusText)) {
                    'delivered', 'completed' => 'bg-green-100 text-green-700 border-green-200',
                    'failed', 'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                    'out for delivery' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'assigned' => 'bg-purple-100 text-purple-700 border-purple-200',
                    default => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                };

                $deliveryDate = $d->delivery_date
                    ? \Illuminate\Support\Carbon::parse($d->delivery_date)->format('d M Y')
                    : '-';

                $customer = $d->subscription->user ?? null;
                $packet = $d->subscription->packet ?? null;
            @endphp

            <details class="group overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm transition hover:shadow-xl hover:shadow-orange-100">
                <summary class="cursor-pointer list-none p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-12 md:items-center">

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Delivery</p>
                            <div class="mt-1 flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 text-lg font-black text-white">
                                    #{{ $d->id }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900">Delivery #{{ $d->id }}</p>
                                    <p class="text-xs font-semibold text-gray-500">
                                        {{ $d->created_at ? $d->created_at->format('d M Y, h:i A') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Date & Time</p>
                            <p class="mt-1 font-black text-gray-900">{{ $deliveryDate }}</p>
                            <p class="text-xs text-gray-500">{{ $d->fixed_delivery_time ?: '-' }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Packet</p>
                            <p class="mt-1 font-black text-gray-900">{{ $packet->packet_name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">
                                Subscription #{{ $d->subscription_id }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Customer</p>
                            <p class="mt-1 font-black text-gray-900">{{ $customer->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $customer->mobile ?? $customer->email ?? '-' }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Delivery Boy</p>
                            <p class="mt-1 font-black text-gray-900">{{ $d->deliveryBoy->name ?? 'Not Assigned' }}</p>
                            <p class="text-xs text-gray-500">{{ $d->deliveryBoy->mobile ?? $d->deliveryBoy->email ?? '-' }}</p>
                        </div>

                        <div class="md:col-span-1">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>

                        <div class="md:col-span-1 text-right">
                            <span class="inline-flex rounded-2xl bg-gray-100 px-4 py-2 text-xs font-black text-gray-700 transition group-open:bg-orange-100 group-open:text-orange-700">
                                View
                            </span>
                        </div>
                    </div>
                </summary>

                <div class="border-t border-orange-100 bg-gradient-to-br from-orange-50/80 to-white p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                        {{-- Details --}}
                        <div class="lg:col-span-2 rounded-3xl border border-orange-100 bg-white p-5">
                            <h3 class="mb-5 text-lg font-black text-gray-900">Delivery Details</h3>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase text-orange-600">Packet Name</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $packet->packet_name ?? '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase text-orange-600">Fixed Delivery Time</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $d->fixed_delivery_time ?: '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Customer Name</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $customer->name ?? '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Customer Contact</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $customer->mobile ?? $customer->email ?? '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Delivery Boy</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $d->deliveryBoy->name ?? 'Not Assigned' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Current Status</p>
                                    <p class="mt-1">
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </p>
                                </div>

                                @if($d->failed_reason)
                                    <div class="md:col-span-2 rounded-2xl border border-red-100 bg-red-50 p-4">
                                        <p class="text-xs font-black uppercase text-red-600">Failed Reason</p>
                                        <p class="mt-1 font-semibold leading-6 text-red-800">{{ $d->failed_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Quick Update --}}
                        <div class="rounded-3xl border border-orange-100 bg-white p-5">
                            <h3 class="mb-5 text-lg font-black text-gray-900">Quick Update</h3>

                            <form method="POST" action="{{ route('admin.daily-deliveries.update-status', $d) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Assign Delivery Boy</label>
                                    <select
                                        name="delivery_boy_id"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="">Not Assigned</option>
                                        @foreach($deliveryBoys as $boy)
                                            <option value="{{ $boy->id }}" @selected($d->delivery_boy_id == $boy->id)>
                                                {{ $boy->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Delivery Status</label>
                                    <select
                                        name="delivery_status"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                        @foreach($statusOptions as $item)
                                            <option value="{{ $item }}" @selected($d->delivery_status == $item)>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Failed Reason</label>
                                    <textarea
                                        name="failed_reason"
                                        rows="4"
                                        placeholder="Only required when delivery failed..."
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >{{ old('failed_reason', $d->failed_reason) }}</textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full rounded-2xl bg-gray-900 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                                >
                                    Update Delivery
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <div class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-12 text-center">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-100 text-4xl">
                    🚚
                </div>
                <h3 class="mt-5 text-2xl font-black text-gray-900">No deliveries found</h3>
                <p class="mt-2 text-gray-500">Try generating today’s deliveries or changing your filter options.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="rounded-3xl border border-orange-100 bg-white p-4 shadow-sm">
        {{ $deliveries->links() }}
    </div>
</div>
@endsection