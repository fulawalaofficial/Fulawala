@extends('admin.layout')

@section('title', 'Create Subscription')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Create Subscription</h1>
            <p class="text-gray-500 mt-1">Select customer, address, packet and subscription duration.</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
            <p class="font-bold mb-2">Please fix these errors:</p>
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.subscriptions.store') }}" method="POST"
          class="bg-white rounded-3xl border border-orange-100 shadow-sm p-6 space-y-6">
        @csrf

        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Select Customer</label>
                <select name="user_id" id="user_id"
                        class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="">-- Select Customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"
                            @selected(old('user_id') == $customer->id)>
                            {{ $customer->name }}
                            @if($customer->mobile)
                                - {{ $customer->mobile }}
                            @endif
                            @if($customer->email)
                                - {{ $customer->email }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Select Pooja Packet</label>
                <select name="packet_id" id="packet_id"
                        class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="">-- Select Packet --</option>
                    @foreach ($packets as $packet)
                        <option value="{{ $packet->id }}"
                                data-monthly-price="{{ (float) $packet->monthly_price }}"
                            @selected(old('packet_id') == $packet->id)>
                            {{ $packet->packet_name }} - ₹{{ number_format((float) $packet->monthly_price, 2) }}/month
                        </option>
                    @endforeach
                </select>
                @error('packet_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block font-bold text-gray-700 mb-2">Customer Addresses</label>

            <div id="addressList"
                 class="rounded-2xl border border-gray-200 bg-gray-50 p-4 min-h-[90px]">
                <p class="text-gray-500">Please select a customer first.</p>
            </div>

            @error('address_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid md:grid-cols-4 gap-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Duration</label>
                <select name="duration" id="duration"
                        class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    @foreach ([1, 2, 3, 6, 12] as $month)
                        <option value="{{ $month }}" @selected((int) old('duration', 1) === $month)>
                            {{ $month }} Month{{ $month > 1 ? 's' : '' }} - {{ $month * 30 }} Days
                        </option>
                    @endforeach
                </select>
                @error('duration')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Start Date</label>
                <input type="date"
                       name="start_date"
                       id="start_date"
                       value="{{ old('start_date') }}"
                       class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                @error('start_date')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">End Date</label>
                <input type="text"
                       id="end_date_display"
                       readonly
                       placeholder="Auto calculate"
                       class="w-full rounded-xl border-gray-300 bg-gray-100 text-gray-700">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Total Days</label>
                <input type="text"
                       id="total_days"
                       readonly
                       placeholder="Auto"
                       class="w-full rounded-xl border-gray-300 bg-gray-100 text-gray-700">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Amount</label>
                <input type="text"
                       id="amount"
                       readonly
                       placeholder="₹0.00"
                       class="w-full rounded-xl border-gray-300 bg-gray-100 text-gray-700">
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Payment Status</label>
                <select name="payment_status"
                        class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    @foreach (['Pending', 'Paid', 'Failed'] as $item)
                        <option value="{{ $item }}" @selected(old('payment_status', 'Pending') === $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
                @error('payment_status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-bold text-gray-700 mb-2">Subscription Status</label>
                <select name="subscription_status"
                        class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    @foreach (['Active', 'Paused', 'Cancelled', 'Expired'] as $item)
                        <option value="{{ $item }}" @selected(old('subscription_status', 'Active') === $item)>
                            {{ $item }}
                        </option>
                    @endforeach
                </select>
                @error('subscription_status')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.subscriptions.index') }}"
               class="px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold">
                Cancel
            </a>

            <button type="submit"
                    class="px-6 py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold shadow">
                Create Subscription
            </button>
        </div>
    </form>
</div>

<script>
    const customerSelect = document.getElementById('user_id');
    const packetSelect = document.getElementById('packet_id');
    const durationSelect = document.getElementById('duration');
    const startDateInput = document.getElementById('start_date');
    const addressList = document.getElementById('addressList');
    const endDateDisplay = document.getElementById('end_date_display');
    const totalDaysInput = document.getElementById('total_days');
    const amountInput = document.getElementById('amount');

    const oldAddressId = @json(old('address_id'));
    const addressBaseUrl = "{{ url('/admin/subscriptions/customer-addresses') }}";

    function formatDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function calculateSubscription() {
        const duration = parseInt(durationSelect.value || '0');
        const startDateValue = startDateInput.value;

        const selectedPacket = packetSelect.options[packetSelect.selectedIndex];
        const monthlyPrice = selectedPacket
            ? parseFloat(selectedPacket.getAttribute('data-monthly-price') || '0')
            : 0;

        const totalDays = duration * 30;
        totalDaysInput.value = totalDays ? `${totalDays} Days` : '';

        const amount = monthlyPrice * duration;
        amountInput.value = amount ? `₹${amount.toFixed(2)}` : '₹0.00';

        if (!startDateValue || !duration) {
            endDateDisplay.value = '';
            return;
        }

        const parts = startDateValue.split('-').map(Number);
        const endDate = new Date(parts[0], parts[1] - 1, parts[2]);

        // Inclusive calculation:
        // 1 month = 30 days means start date + 29 days.
        endDate.setDate(endDate.getDate() + totalDays - 1);

        endDateDisplay.value = formatDate(endDate);
    }

    async function loadCustomerAddresses(selectedAddressId = null) {
        const userId = customerSelect.value;

        if (!userId) {
            addressList.innerHTML = `<p class="text-gray-500">Please select a customer first.</p>`;
            return;
        }

        addressList.innerHTML = `<p class="text-gray-500">Loading addresses...</p>`;

        try {
            const response = await fetch(`${addressBaseUrl}/${userId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.addresses || data.addresses.length === 0) {
                addressList.innerHTML = `
                    <div class="rounded-xl bg-yellow-50 border border-yellow-200 p-4">
                        <p class="text-yellow-800 font-semibold">No address found for this customer.</p>
                        <p class="text-yellow-700 text-sm mt-1">
                            Please add address for this customer first, then create subscription.
                        </p>
                    </div>
                `;
                return;
            }

            addressList.innerHTML = data.addresses.map(address => {
                const checked = String(address.id) === String(selectedAddressId) ? 'checked' : '';

                return `
                    <label class="flex gap-3 items-start bg-white border border-gray-200 rounded-xl p-4 mb-3 cursor-pointer hover:border-orange-300">
                        <input type="checkbox"
                               name="address_id"
                               value="${address.id}"
                               ${checked}
                               class="address-checkbox mt-1 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-gray-700 font-medium">${address.label}</span>
                    </label>
                `;
            }).join('');

            document.querySelectorAll('.address-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        document.querySelectorAll('.address-checkbox').forEach(other => {
                            if (other !== this) {
                                other.checked = false;
                            }
                        });
                    }
                });
            });

        } catch (error) {
            addressList.innerHTML = `
                <div class="rounded-xl bg-red-50 border border-red-200 p-4">
                    <p class="text-red-700 font-semibold">Unable to load addresses.</p>
                </div>
            `;
        }
    }

    customerSelect.addEventListener('change', () => loadCustomerAddresses());
    packetSelect.addEventListener('change', calculateSubscription);
    durationSelect.addEventListener('change', calculateSubscription);
    startDateInput.addEventListener('change', calculateSubscription);

    document.addEventListener('DOMContentLoaded', function () {
        calculateSubscription();

        if (customerSelect.value) {
            loadCustomerAddresses(oldAddressId);
        }
    });
</script>
@endsection