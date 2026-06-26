@extends('admin.layout')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">
                    📊 Fulawala Business Insights
                </div>

                <h1 class="mt-4 text-3xl md:text-4xl font-black tracking-tight">
                    Reports Dashboard
                </h1>

                <p class="mt-2 max-w-2xl text-white/90">
                    Track revenue, subscriptions, custom orders, event bookings, payments and business growth in one premium dashboard.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">₹{{ number_format((float) ($report['total_revenue'] ?? 0), 2) }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Total Revenue</p>
                </div>

                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">{{ $report['customers'] ?? 0 }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Customers</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
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

            <div class="flex items-end">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-3 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:scale-[1.01]"
                >
                    Apply Report
                </button>
            </div>

            <div class="flex items-end">
                <a
                    href="{{ route('admin.reports.index') }}"
                    class="w-full rounded-2xl border border-gray-200 bg-white px-8 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Revenue Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Total Revenue</p>
                    <h3 class="mt-2 text-2xl font-black text-gray-900">
                        ₹{{ number_format((float) ($report['total_revenue'] ?? 0), 2) }}
                    </h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">💰</div>
            </div>
        </div>

        <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Subscription Revenue</p>
                    <h3 class="mt-2 text-2xl font-black text-gray-900">
                        ₹{{ number_format((float) ($report['subscription_revenue'] ?? 0), 2) }}
                    </h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-2xl">📅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Custom Order Revenue</p>
                    <h3 class="mt-2 text-2xl font-black text-gray-900">
                        ₹{{ number_format((float) ($report['custom_order_revenue'] ?? 0), 2) }}
                    </h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">🌼</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-gray-500">Event Revenue</p>
                    <h3 class="mt-2 text-2xl font-black text-gray-900">
                        ₹{{ number_format((float) ($report['event_revenue'] ?? 0), 2) }}
                    </h3>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">🎉</div>
            </div>
        </div>
    </div>

    {{-- Business Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Orders</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $report['orders'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">📦</div>
            </div>
        </div>

        <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Subscriptions</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $report['subscriptions'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-2xl">📅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Customers</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $report['customers'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">👥</div>
            </div>
        </div>

        <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Event Bookings</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $report['event_bookings'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-100 text-2xl">🎉</div>
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Pending Orders</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $report['pending_orders'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-100 text-2xl">⏳</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Avg Order Value</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-xl font-black text-gray-900">
                    ₹{{ number_format((float) ($report['average_order_value'] ?? 0), 2) }}
                </h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">📈</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-gray-900">Monthly Revenue</h2>
                <p class="text-sm font-semibold text-gray-500">Last 6 months paid payment performance.</p>
            </div>
            <div class="h-80">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-gray-900">Revenue Breakdown</h2>
                <p class="text-sm font-semibold text-gray-500">Revenue source wise overview.</p>
            </div>
            <div class="h-80">
                <canvas id="revenueBreakdownChart"></canvas>
            </div>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-gray-900">Order Status</h2>
                <p class="text-sm font-semibold text-gray-500">Custom order status distribution.</p>
            </div>
            <div class="h-80">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-xl font-black text-gray-900">Subscription Status</h2>
                <p class="text-sm font-semibold text-gray-500">Subscription status distribution.</p>
            </div>
            <div class="h-80">
                <canvas id="subscriptionStatusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-900">Recent Payments</h2>
                <p class="text-sm font-semibold text-gray-500">Latest payment activity from customers.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-100">
            <table class="w-full text-sm">
                <thead class="bg-orange-50">
                    <tr>
                        <th class="p-4 text-center font-black text-gray-700">ID</th>
                        <th class="p-4 text-left font-black text-gray-700">Payment Type</th>
                        <th class="p-4 text-center font-black text-gray-700">Amount</th>
                        <th class="p-4 text-center font-black text-gray-700">Status</th>
                        <th class="p-4 text-center font-black text-gray-700">Date</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($recentPayments as $payment)
                        @php
                            $status = $payment->payment_status ?: 'Pending';

                            $statusClass = strtolower($status) === 'paid'
                                ? 'bg-green-100 text-green-700 border-green-200'
                                : 'bg-yellow-100 text-yellow-700 border-yellow-200';
                        @endphp

                        <tr class="transition hover:bg-orange-50/60">
                            <td class="p-4 text-center font-black text-gray-900">#{{ $payment->id }}</td>
                            <td class="p-4 font-bold text-gray-800">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_type ?? '-')) }}
                            </td>
                            <td class="p-4 text-center font-black text-gray-900">
                                ₹{{ number_format((float) ($payment->amount ?? 0), 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-semibold text-gray-600">
                                {{ $payment->created_at ? $payment->created_at->format('d M Y, h:i A') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center font-semibold text-gray-500">
                                No recent payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const reportChartData = @json($chartData);

    function makeChart(canvasId, type, labels, values) {
        const element = document.getElementById(canvasId);

        if (!element) {
            return;
        }

        new Chart(element, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: type === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: type !== 'bar' && type !== 'line'
                    }
                },
                scales: type === 'doughnut' || type === 'pie' ? {} : {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    makeChart(
        'monthlyRevenueChart',
        'line',
        reportChartData.monthlyLabels,
        reportChartData.monthlyValues
    );

    makeChart(
        'revenueBreakdownChart',
        'doughnut',
        reportChartData.revenueLabels,
        reportChartData.revenueValues
    );

    makeChart(
        'orderStatusChart',
        'bar',
        reportChartData.orderStatusLabels,
        reportChartData.orderStatusValues
    );

    makeChart(
        'subscriptionStatusChart',
        'pie',
        reportChartData.subscriptionStatusLabels,
        reportChartData.subscriptionStatusValues
    );
</script>
@endsection