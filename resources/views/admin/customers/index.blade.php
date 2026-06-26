@extends('admin.layout')

@section('title', 'Customers')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">
                    👤 Fulawala Customer Center
                </div>

                <h1 class="mt-4 text-3xl md:text-4xl font-black tracking-tight">
                    Customer Management
                </h1>

                <p class="mt-2 max-w-2xl text-white/90">
                    View all customers, track orders, subscriptions, event bookings, spending amount and manage active or inactive customer status.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Total Customers</p>
                </div>

                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">₹{{ number_format((float) ($stats['order_revenue'] ?? 0), 2) }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Order Revenue</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash / Validation --}}
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

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <p class="font-black">Please fix these errors:</p>
            <ul class="mt-2 list-disc pl-5 text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Total</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['total'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">👥</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Active</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['active'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">✅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Inactive</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['inactive'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-100 text-2xl">⛔</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Orders</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['orders'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">📦</div>
            </div>
        </div>

        <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Subscriptions</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['subscriptions'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-2xl">📅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Events</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['event_bookings'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-100 text-2xl">🎉</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="md:col-span-3">
                <label class="mb-2 block text-sm font-black text-gray-700">Search Customer</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search by ID, name, mobile, email..."
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
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') == $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-gray-900 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                >
                    Filter
                </button>

                <a
                    href="{{ route('admin.customers.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Customer Cards --}}
    <div class="space-y-4">
        @forelse($customers as $c)
            @php
                $statusText = $c->status ?: 'Inactive';

                $statusClass = $statusText === 'Active'
                    ? 'bg-green-100 text-green-700 border-green-200'
                    : 'bg-red-100 text-red-700 border-red-200';

                $avatar = strtoupper(substr($c->name ?? 'C', 0, 1));
            @endphp

            <details class="group overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm transition hover:shadow-xl hover:shadow-orange-100">
                <summary class="cursor-pointer list-none p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-12 md:items-center">

                        <div class="md:col-span-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Customer</p>
                            <div class="mt-1 flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 text-lg font-black text-white">
                                    {{ $avatar }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900">{{ $c->name }}</p>
                                    <p class="text-xs font-semibold text-gray-500">Customer ID #{{ $c->id }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Mobile</p>
                            <p class="mt-1 font-black text-gray-900">{{ $c->mobile ?: '-' }}</p>
                        </div>

                        <div class="md:col-span-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Email</p>
                            <p class="mt-1 break-all font-black text-gray-900">{{ $c->email }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-2xl bg-blue-50 px-2 py-2">
                                    <p class="text-[10px] font-black uppercase text-blue-700">Orders</p>
                                    <p class="font-black text-blue-900">{{ $c->custom_orders_count ?? 0 }}</p>
                                </div>
                                <div class="rounded-2xl bg-purple-50 px-2 py-2">
                                    <p class="text-[10px] font-black uppercase text-purple-700">Subs</p>
                                    <p class="font-black text-purple-900">{{ $c->subscriptions_count ?? 0 }}</p>
                                </div>
                                <div class="rounded-2xl bg-yellow-50 px-2 py-2">
                                    <p class="text-[10px] font-black uppercase text-yellow-700">Events</p>
                                    <p class="font-black text-yellow-900">{{ $c->event_bookings_count ?? 0 }}</p>
                                </div>
                            </div>
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

                        {{-- Customer Details --}}
                        <div class="rounded-3xl border border-orange-100 bg-white p-5">
                            <h3 class="mb-4 text-lg font-black text-gray-900">Customer Details</h3>

                            <div class="space-y-4 text-sm">
                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase text-orange-600">Name</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $c->name }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Mobile</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $c->mobile ?: '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Email</p>
                                    <p class="mt-1 break-all font-black text-gray-900">{{ $c->email }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Joined</p>
                                    <p class="mt-1 font-black text-gray-900">
                                        {{ $c->created_at ? $c->created_at->format('d M Y, h:i A') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Activity Summary --}}
                        <div class="lg:col-span-2 rounded-3xl border border-orange-100 bg-white p-5">
                            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-black text-gray-900">Activity Summary</h3>
                                    <p class="text-sm font-semibold text-gray-500">
                                        Customer shopping and booking overview.
                                    </p>
                                </div>

                                <span class="inline-flex w-fit rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="text-xs font-black uppercase text-blue-700">Custom Orders</p>
                                    <p class="mt-2 text-3xl font-black text-blue-900">{{ $c->custom_orders_count ?? 0 }}</p>
                                </div>

                                <div class="rounded-2xl bg-green-50 p-4">
                                    <p class="text-xs font-black uppercase text-green-700">Order Amount</p>
                                    <p class="mt-2 text-xl font-black text-green-900">
                                        ₹{{ number_format((float) ($c->custom_orders_total ?? 0), 2) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-purple-50 p-4">
                                    <p class="text-xs font-black uppercase text-purple-700">Subscriptions</p>
                                    <p class="mt-2 text-3xl font-black text-purple-900">{{ $c->subscriptions_count ?? 0 }}</p>
                                </div>

                                <div class="rounded-2xl bg-yellow-50 p-4">
                                    <p class="text-xs font-black uppercase text-yellow-700">Event Bookings</p>
                                    <p class="mt-2 text-3xl font-black text-yellow-900">{{ $c->event_bookings_count ?? 0 }}</p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-3xl border border-orange-100 bg-orange-50 p-5">
                                <h4 class="font-black text-gray-900">Quick Status Update</h4>
                                <p class="mt-1 text-sm font-semibold text-gray-600">
                                    Disable customer access by changing status to inactive.
                                </p>

                                <form method="POST" action="{{ route('admin.customers.update-status', $c) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                                    @csrf
                                    @method('PATCH')

                                    <select
                                        name="status"
                                        class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected($c->status == $status)>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="rounded-2xl bg-gray-900 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                                    >
                                        Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <div class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-12 text-center">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-100 text-4xl">
                    👤
                </div>
                <h3 class="mt-5 text-2xl font-black text-gray-900">No customers found</h3>
                <p class="mt-2 text-gray-500">Try changing your search or filter options.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="rounded-3xl border border-orange-100 bg-white p-4 shadow-sm">
        {{ $customers->links() }}
    </div>
</div>
@endsection