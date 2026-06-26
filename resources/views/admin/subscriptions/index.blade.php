@extends('admin.layout')

@section('title', 'Subscriptions')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-700 font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-orange-600 uppercase tracking-wider">Admin Panel</p>
            <h1 class="text-3xl font-black text-slate-900">Subscriptions</h1>
            <p class="text-slate-500 mt-1">Manage customer pooja packet plans, payments and delivery period.</p>
        </div>

        <a href="{{ route('admin.subscriptions.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-600 px-5 py-3 text-white font-black shadow-lg shadow-orange-200 hover:bg-orange-700 transition">
            <span class="text-xl leading-none">+</span>
            Create Subscription
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="rounded-3xl bg-white border border-orange-100 p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-bold">Total</p>
            <h3 class="text-3xl font-black text-slate-900 mt-2">{{ $stats['total'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">All subscriptions</p>
        </div>

        <div class="rounded-3xl bg-white border border-green-100 p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-bold">Active</p>
            <h3 class="text-3xl font-black text-green-600 mt-2">{{ $stats['active'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Currently running</p>
        </div>

        <div class="rounded-3xl bg-white border border-blue-100 p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-bold">Paid</p>
            <h3 class="text-3xl font-black text-blue-600 mt-2">{{ $stats['paid'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Payment completed</p>
        </div>

        <div class="rounded-3xl bg-white border border-yellow-100 p-5 shadow-sm">
            <p class="text-sm text-slate-500 font-bold">Pending</p>
            <h3 class="text-3xl font-black text-yellow-600 mt-2">{{ $stats['pending'] }}</h3>
            <p class="text-xs text-slate-400 mt-1">Payment pending</p>
        </div>

        <div class="rounded-3xl bg-gradient-to-br from-orange-500 to-red-500 p-5 shadow-lg shadow-orange-200 text-white">
            <p class="text-sm text-white/80 font-bold">Paid Revenue</p>
            <h3 class="text-3xl font-black mt-2">₹{{ number_format($stats['revenue'], 2) }}</h3>
            <p class="text-xs text-white/70 mt-1">From paid subscriptions</p>
        </div>
    </div>

    <div class="rounded-3xl bg-white border border-orange-100 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
            <div class="lg:col-span-5">
                <label class="block text-sm font-black text-slate-700 mb-2">Search</label>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Search by ID, customer, mobile, email or packet..."
                       class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-black text-slate-700 mb-2">Status</label>
                <select name="status"
                        class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                    <option value="">All Status</option>
                    @foreach(['Active', 'Paused', 'Cancelled', 'Expired'] as $item)
                        <option value="{{ $item }}" @selected($status === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-black text-slate-700 mb-2">Payment</label>
                <select name="payment"
                        class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                    <option value="">All Payments</option>
                    @foreach(['Pending', 'Paid', 'Failed'] as $item)
                        <option value="{{ $item }}" @selected($payment === $item)>{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3 flex gap-3">
                <button type="submit"
                        class="flex-1 rounded-2xl bg-slate-900 px-5 py-3 text-white font-black hover:bg-slate-800 transition">
                    Filter
                </button>

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="rounded-2xl border border-orange-100 px-5 py-3 font-black text-slate-600 hover:bg-orange-50 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-3xl bg-white border border-orange-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-orange-50 text-slate-700">
                        <th class="px-5 py-4 text-left font-black">ID</th>
                        <th class="px-5 py-4 text-left font-black">Customer</th>
                        <th class="px-5 py-4 text-left font-black">Packet</th>
                        <th class="px-5 py-4 text-center font-black">Duration</th>
                        <th class="px-5 py-4 text-center font-black">Period</th>
                        <th class="px-5 py-4 text-right font-black">Amount</th>
                        <th class="px-5 py-4 text-center font-black">Payment</th>
                        <th class="px-5 py-4 text-center font-black">Status</th>
                        <th class="px-5 py-4 text-left font-black">Address</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-orange-50">
                    @forelse($subscriptions as $sub)
                        @php
                            $addressText = $sub->address->address
                                ?? $sub->address->address_line
                                ?? $sub->address->full_address
                                ?? $sub->address->line1
                                ?? $sub->address->street_address
                                ?? '-';

                            $paymentClass = match($sub->payment_status) {
                                'Paid' => 'bg-green-50 text-green-700 border-green-200',
                                'Failed' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                            };

                            $statusClass = match($sub->subscription_status) {
                                'Active' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'Paused' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                'Cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                'Expired' => 'bg-slate-50 text-slate-700 border-slate-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp

                        <tr class="hover:bg-orange-50/50 transition">
                            <td class="px-5 py-4 font-black text-slate-900">
                                #{{ $sub->id }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-black text-slate-800">
                                    {{ $sub->user->name ?? 'Customer removed' }}
                                </div>
                                <div class="text-xs text-slate-400 mt-1">
                                    {{ $sub->user->mobile ?? $sub->user->email ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-700">
                                    {{ $sub->packet->packet_name ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-orange-700 font-black border border-orange-100">
                                    {{ $sub->duration }} Month{{ $sub->duration > 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="font-bold text-slate-700">
                                    {{ $sub->start_date?->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    to {{ $sub->end_date?->format('d M Y') }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-right font-black text-slate-900">
                                ₹{{ number_format((float) $sub->amount, 2) }}
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-black border {{ $paymentClass }}">
                                    {{ $sub->payment_status }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-black border {{ $statusClass }}">
                                    {{ $sub->subscription_status }}
                                </span>
                            </td>

                            <td class="px-5 py-4 max-w-xs">
                                <div class="text-slate-500 line-clamp-2">
                                    {{ $addressText }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="mx-auto w-16 h-16 rounded-3xl bg-orange-50 flex items-center justify-center text-3xl mb-4">
                                    📦
                                </div>
                                <h3 class="text-lg font-black text-slate-800">No subscriptions found</h3>
                                <p class="text-slate-400 mt-1">Create a manual subscription or change your filters.</p>

                                <a href="{{ route('admin.subscriptions.create') }}"
                                   class="inline-flex mt-5 rounded-2xl bg-orange-600 px-5 py-3 text-white font-black hover:bg-orange-700">
                                    Create Subscription
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-5 py-4 border-t border-orange-50">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection