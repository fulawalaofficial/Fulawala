@extends('admin.layout')

@section('title', 'Custom Orders')

@section('content')
<div class="space-y-6">

    {{-- Page heading --}}
    <div>
        <p class="text-sm font-bold text-orange-600 uppercase tracking-wide">
            Order Management
        </p>

        <h1 class="text-3xl font-black text-slate-900">
            Custom Flower Orders
        </h1>

        <p class="mt-1 text-slate-500">
            View orders and update delivery status.
        </p>
    </div>

    {{-- Success message --}}
    @if(session('success'))
        <div
            class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-green-700"
        >
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span class="font-semibold">
                    {{ session('success') }}
                </span>
            </div>
        </div>
    @endif

    {{-- Error message --}}
    @if(session('error'))
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700"
        >
            <div class="flex items-center gap-2">
                <span>❌</span>
                <span class="font-semibold">
                    {{ session('error') }}
                </span>
            </div>
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-700"
        >
            <p class="font-bold">Please fix the following error:</p>

            <ul class="mt-2 list-disc pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-sm">
                <thead class="bg-orange-100 text-slate-800">
                    <tr>
                        <th class="p-4 text-center font-bold">
                            ID
                        </th>

                        <th class="p-4 text-left font-bold">
                            Customer
                        </th>

                        <th class="p-4 text-center font-bold">
                            Amount
                        </th>

                        <th class="p-4 text-center font-bold">
                            Delivery Date
                        </th>

                        <th class="p-4 text-center font-bold">
                            Slot
                        </th>

                        <th class="p-4 text-center font-bold">
                            Payment
                        </th>

                        <th class="p-4 text-center font-bold">
                            Current Status
                        </th>

                        <th class="p-4 text-center font-bold">
                            Update Status
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $order)
                        @php
                            $statusClasses = match($order->order_status) {
                                'Order Placed' =>
                                    'bg-blue-100 text-blue-700 border-blue-200',

                                'Confirmed' =>
                                    'bg-indigo-100 text-indigo-700 border-indigo-200',

                                'Preparing' =>
                                    'bg-yellow-100 text-yellow-700 border-yellow-200',

                                'Ready for Delivery' =>
                                    'bg-purple-100 text-purple-700 border-purple-200',

                                'Out for Delivery' =>
                                    'bg-orange-100 text-orange-700 border-orange-200',

                                'Delivered' =>
                                    'bg-green-100 text-green-700 border-green-200',

                                'Cancelled' =>
                                    'bg-red-100 text-red-700 border-red-200',

                                default =>
                                    'bg-slate-100 text-slate-700 border-slate-200',
                            };

                            $paymentClasses =
                                strtolower((string) $order->payment_status) === 'paid'
                                    ? 'bg-green-100 text-green-700 border-green-200'
                                    : 'bg-yellow-100 text-yellow-700 border-yellow-200';
                        @endphp

                        <tr
                            class="border-t border-slate-100 transition hover:bg-orange-50/40"
                        >
                            <td class="p-4 text-center font-bold text-slate-700">
                                #{{ $order->id }}
                            </td>

                            <td class="p-4">
                                <div class="font-bold text-slate-800">
                                    {{ $order->user?->name ?? 'Unknown Customer' }}
                                </div>

                                @if($order->user?->email)
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $order->user->email }}
                                    </div>
                                @endif
                            </td>

                            <td class="p-4 text-center font-bold text-slate-800">
                                ₹{{ number_format((float) $order->total_amount, 2) }}
                            </td>

                            <td class="p-4 text-center text-slate-700">
                                {{ $order->delivery_date?->format('d M Y') ?? '-' }}
                            </td>

                            <td class="p-4 text-center text-slate-700">
                                {{ $order->delivery_slot ?: '-' }}
                            </td>

                            <td class="p-4 text-center">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $paymentClasses }}"
                                >
                                    {{ $order->payment_status ?: 'Pending' }}
                                </span>
                            </td>

                            <td class="p-4 text-center">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $statusClasses }}"
                                >
                                    {{ $order->order_status ?: 'Order Placed' }}
                                </span>
                            </td>

                            <td class="p-4">
                                <form
                                    action="{{ route('admin.custom-orders.update-status', $order) }}"
                                    method="POST"
                                    class="flex items-center justify-center gap-2"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <select
                                        name="order_status"
                                        required
                                        class="min-w-[165px] rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100"
                                    >
                                        @foreach($orderStatuses as $status)
                                            <option
                                                value="{{ $status }}"
                                                @selected($order->order_status === $status)
                                            >
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-orange-700"
                                    >
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="8"
                                class="p-12 text-center"
                            >
                                <div class="text-4xl">
                                    🌸
                                </div>

                                <p class="mt-3 font-bold text-slate-700">
                                    No custom orders found
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    New customer orders will appear here.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div>
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection