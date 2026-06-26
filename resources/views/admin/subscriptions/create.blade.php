@extends('admin.layout')

@section('title', 'Create Subscription')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-orange-600 uppercase tracking-wider">Manual Entry</p>
            <h1 class="text-3xl font-black text-slate-900">Create Subscription</h1>
            <p class="text-slate-500 mt-1">Add a pooja packet subscription manually for any customer.</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="inline-flex items-center justify-center rounded-2xl border border-orange-100 bg-white px-5 py-3 font-black text-slate-700 hover:bg-orange-50 transition">
            ← Back to Subscriptions
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700">
            <div class="font-black mb-2">Please fix these errors:</div>
            <ul class="list-disc ml-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li class="font-semibold">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @csrf

        <div class="xl:col-span-2 rounded-3xl bg-white border border-orange-100 shadow-sm p-6 space-y-6">
            <div>
                <h2 class="text-xl font-black text-slate-900">Subscription Details</h2>
                <p class="text-sm text-slate-400 mt-1">Select customer, packet and subscription period.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id"
                            required
                            class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('user_id') == $customer->id)>
                                {{ $customer->name }} - {{ $customer->mobile ?? $customer->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Pooja Packet <span class="text-red-500">*</span>
                    </label>
                    <select name="packet_id"
                            id="packet_id"
                            required
                            class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                        <option value="" data-price="0">Select Packet</option>
                        @foreach($packets as $packet)
                            <option value="{{ $packet->id }}"
                                    data-price="{{ $packet->monthly_price }}"
                                    @selected(old('packet_id') == $packet->id)>
                                {{ $packet->packet_name }} - ₹{{ number_format((float) $packet->monthly_price, 2) }}/month
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Duration <span class="text-red-500">*</span>
                    </label>
                    <select name="duration"
                            id="duration"
                            required
                            class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                        @foreach([1, 3, 6, 12] as $month)
                            <option value="{{ $month }}" @selected(old('duration', 1) == $month)>
                                {{ $month }} Month{{ $month > 1 ? 's' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           value="{{ old('start_date', now()->toDateString()) }}"
                           required
                           class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Payment Status <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_status"
                            required
                            class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                        @foreach(['Pending', 'Paid', 'Failed'] as $item)
                            <option value="{{ $item }}" @selected(old('payment_status', 'Paid') === $item)>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Subscription Status <span class="text-red-500">*</span>
                    </label>
                    <select name="subscription_status"
                            required
                            class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                        @foreach(['Active', 'Paused', 'Cancelled', 'Expired'] as $item)
                            <option value="{{ $item }}" @selected(old('subscription_status', 'Active') === $item)>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Amount
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-400">₹</span>
                        <input type="number"
                               name="amount"
                               id="amount"
                               value="{{ old('amount') }}"
                               min="0"
                               step="0.01"
                               placeholder="Auto calculated from packet price × duration"
                               class="w-full rounded-2xl border border-orange-100 pl-9 pr-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">
                        Leave empty or allow auto calculation. You can also edit the amount manually.
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-slate-700 mb-2">
                        Delivery Address <span class="text-red-500">*</span>
                    </label>
                    <textarea name="address"
                              rows="4"
                              required
                              placeholder="Enter full delivery address..."
                              class="w-full rounded-2xl border border-orange-100 px-4 py-3 focus:outline-none focus:ring-4 focus:ring-orange-100">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl bg-gradient-to-br from-orange-500 to-red-500 shadow-lg shadow-orange-200 p-6 text-white">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl mb-4">
                    🙏
                </div>
                <h3 class="text-2xl font-black">Subscription Summary</h3>
                <p class="text-white/80 text-sm mt-2">
                    Amount and end date will be calculated automatically after choosing packet, duration and start date.
                </p>

                <div class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-white/20 pb-3">
                        <span class="text-white/70">Monthly Price</span>
                        <span class="font-black" id="summary_price">₹0.00</span>
                    </div>

                    <div class="flex justify-between gap-4 border-b border-white/20 pb-3">
                        <span class="text-white/70">Duration</span>
                        <span class="font-black" id="summary_duration">1 Month</span>
                    </div>

                    <div class="flex justify-between gap-4 border-b border-white/20 pb-3">
                        <span class="text-white/70">End Date</span>
                        <span class="font-black" id="summary_end_date">-</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-white/70">Total</span>
                        <span class="font-black text-xl" id="summary_total">₹0.00</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white border border-orange-100 shadow-sm p-6">
                <button type="submit"
                        class="w-full rounded-2xl bg-slate-900 px-5 py-4 text-white font-black hover:bg-slate-800 transition shadow-lg">
                    Save Subscription
                </button>

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="mt-3 w-full inline-flex justify-center rounded-2xl border border-orange-100 px-5 py-4 font-black text-slate-600 hover:bg-orange-50 transition">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    const packetSelect = document.getElementById('packet_id');
    const durationSelect = document.getElementById('duration');
    const startDateInput = document.getElementById('start_date');
    const amountInput = document.getElementById('amount');

    const summaryPrice = document.getElementById('summary_price');
    const summaryDuration = document.getElementById('summary_duration');
    const summaryEndDate = document.getElementById('summary_end_date');
    const summaryTotal = document.getElementById('summary_total');

    function formatMoney(value) {
        return '₹' + Number(value || 0).toFixed(2);
    }

    function formatDate(date) {
        if (!date || isNaN(date.getTime())) return '-';

        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function calculateEndDate(startDate, duration) {
        if (!startDate) return null;

        const date = new Date(startDate);
        date.setMonth(date.getMonth() + Number(duration || 1));
        date.setDate(date.getDate() - 1);

        return date;
    }

    function updateSummary() {
        const selectedPacket = packetSelect.options[packetSelect.selectedIndex];
        const monthlyPrice = Number(selectedPacket?.dataset?.price || 0);
        const duration = Number(durationSelect.value || 1);
        const total = monthlyPrice * duration;

        summaryPrice.textContent = formatMoney(monthlyPrice);
        summaryDuration.textContent = duration + ' Month' + (duration > 1 ? 's' : '');

        const endDate = calculateEndDate(startDateInput.value, duration);
        summaryEndDate.textContent = formatDate(endDate);

        summaryTotal.textContent = formatMoney(total);

        if (!amountInput.dataset.edited) {
            amountInput.value = total ? total.toFixed(2) : '';
        }
    }

    packetSelect.addEventListener('change', updateSummary);
    durationSelect.addEventListener('change', updateSummary);
    startDateInput.addEventListener('change', updateSummary);

    amountInput.addEventListener('input', function () {
        amountInput.dataset.edited = 'true';
        summaryTotal.textContent = formatMoney(amountInput.value);
    });

    updateSummary();
</script>
@endsection