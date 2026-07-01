@extends('admin.layout')

@section('title', 'Create Subscription')

@section('content')
<div class="max-w-6xl mx-auto pb-10">
    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-orange-600 font-black uppercase tracking-widest text-sm">Admin Panel</p>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mt-1">Create Subscription</h1>
            <p class="text-slate-500 mt-2">Choose customer, delivery address, pooja packet and duration.</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="inline-flex items-center justify-center px-5 py-3 rounded-2xl bg-white border border-orange-100 text-slate-700 font-bold shadow-sm hover:bg-orange-50">
            ← Back
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-3xl bg-red-50 border border-red-200 p-5 text-red-700">
            <p class="font-black mb-2">Please fix these errors:</p>
            <ul class="list-disc ml-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="subscriptionForm"
          action="{{ route('admin.subscriptions.store') }}"
          method="POST"
          class="space-y-6">
        @csrf

        <div class="bg-white rounded-[2rem] border border-orange-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-5 text-white">
                <h2 class="text-xl font-black">1. Customer & Delivery Address</h2>
                <p class="text-orange-50 text-sm mt-1">Select customer and choose one saved address or add a new address.</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block font-black text-slate-800 mb-2">Select Customer</label>
                        <select name="user_id"
                                id="user_id"
                                class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="">-- Select Customer --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('user_id') == $customer->id)>
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
                        <label class="block font-black text-slate-800 mb-2">Quick Help</label>
                        <div class="rounded-2xl bg-orange-50 border border-orange-100 p-4 text-sm text-slate-600">
                            If no saved address is found, add a delivery address directly below. It will be saved for this customer.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-black text-slate-800 mb-2">Customer Addresses</label>

                    <div id="addressList"
                         class="rounded-3xl border border-orange-100 bg-slate-50 p-4 min-h-[120px]">
                        <div class="text-slate-500">Please select a customer first.</div>
                    </div>

                    <p id="addressError" class="hidden text-red-600 text-sm mt-2 font-semibold">
                        Please select an address or add a new delivery address.
                    </p>

                    @error('address_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    @error('new_address')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-orange-100 shadow-sm overflow-hidden">
            <div class="bg-slate-900 p-5 text-white">
                <h2 class="text-xl font-black">2. Packet & Subscription Plan</h2>
                <p class="text-slate-300 text-sm mt-1">End date is calculated with fixed 30 days per month.</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block font-black text-slate-800 mb-2">Select Pooja Packet</label>
                        <select name="packet_id"
                                id="packet_id"
                                class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
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

                    <div>
                        <label class="block font-black text-slate-800 mb-2">Duration</label>
                        <select name="duration"
                                id="duration"
                                class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
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
                </div>

                <div class="grid md:grid-cols-4 gap-5">
                    <div>
                        <label class="block font-black text-slate-800 mb-2">Start Date</label>
                        <input type="date"
                               name="start_date"
                               id="start_date"
                               value="{{ old('start_date') }}"
                               class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                        @error('start_date')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 mb-2">End Date</label>
                        <input type="text"
                               id="end_date_display"
                               readonly
                               placeholder="Auto calculate"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-700">
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 mb-2">Total Days</label>
                        <input type="text"
                               id="total_days"
                               readonly
                               placeholder="Auto"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-700">
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 mb-2">Amount</label>
                        <input type="text"
                               id="amount"
                               readonly
                               placeholder="₹0.00"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-700 font-black">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-orange-100 shadow-sm overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-black text-slate-900 mb-5">3. Status</h2>

                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block font-black text-slate-800 mb-2">Payment Status</label>
                        <select name="payment_status"
                                class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @foreach (['Pending', 'Paid', 'Failed'] as $item)
                                <option value="{{ $item }}" @selected(old('payment_status', 'Pending') === $item)>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-black text-slate-800 mb-2">Subscription Status</label>
                        <select name="subscription_status"
                                class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            @foreach (['Active', 'Paused', 'Cancelled', 'Expired'] as $item)
                                <option value="{{ $item }}" @selected(old('subscription_status', 'Active') === $item)>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('admin.subscriptions.index') }}"
                       class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-center">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-7 py-3 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-black shadow-lg shadow-orange-200">
                        Create Subscription
                    </button>
                </div>
            </div>
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
    const subscriptionForm = document.getElementById('subscriptionForm');
    const addressError = document.getElementById('addressError');

    const oldAddressId = @json(old('address_id'));
    const oldNewAddress = @json(old('new_address'));
    const addressBaseUrl = "{{ url('/admin/subscriptions/customer-addresses') }}";

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

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
        const amount = monthlyPrice * duration;

        totalDaysInput.value = totalDays ? `${totalDays} Days` : '';
        amountInput.value = amount ? `₹${amount.toFixed(2)}` : '₹0.00';

        if (!startDateValue || !duration) {
            endDateDisplay.value = '';
            return;
        }

        const parts = startDateValue.split('-').map(Number);
        const endDate = new Date(parts[0], parts[1] - 1, parts[2]);

        endDate.setDate(endDate.getDate() + totalDays - 1);

        endDateDisplay.value = formatDate(endDate);
    }

    function newAddressBox(showByDefault = false) {
        const checked = showByDefault ? 'checked' : '';
        const display = showByDefault ? 'block' : 'none';

        return `
            <div class="mt-4 rounded-3xl border border-dashed border-orange-300 bg-orange-50 p-4">
                <label class="flex gap-3 items-center cursor-pointer">
                    <input type="checkbox"
                           id="use_new_address"
                           ${checked}
                           class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                    <span class="font-black text-slate-800">Add new delivery address</span>
                </label>

                <div id="new_address_wrap" style="display:${display};" class="mt-4">
                    <textarea name="new_address"
                              id="new_address"
                              rows="4"
                              placeholder="Enter full delivery address..."
                              class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-slate-800 focus:border-orange-500 focus:ring-orange-500">${escapeHtml(oldNewAddress)}</textarea>
                    <p class="text-sm text-slate-500 mt-2">This address will be saved and used for this subscription.</p>
                </div>
            </div>
        `;
    }

    function bindAddressEvents() {
        document.querySelectorAll('.address-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    document.querySelectorAll('.address-checkbox').forEach(other => {
                        if (other !== this) {
                            other.checked = false;
                        }
                    });

                    const useNewAddress = document.getElementById('use_new_address');
                    const newAddressWrap = document.getElementById('new_address_wrap');

                    if (useNewAddress) {
                        useNewAddress.checked = false;
                    }

                    if (newAddressWrap) {
                        newAddressWrap.style.display = 'none';
                    }
                }
            });
        });

        const useNewAddress = document.getElementById('use_new_address');
        const newAddressWrap = document.getElementById('new_address_wrap');

        if (useNewAddress && newAddressWrap) {
            useNewAddress.addEventListener('change', function () {
                if (this.checked) {
                    document.querySelectorAll('.address-checkbox').forEach(other => {
                        other.checked = false;
                    });

                    newAddressWrap.style.display = 'block';

                    const newAddress = document.getElementById('new_address');
                    if (newAddress) {
                        newAddress.focus();
                    }
                } else {
                    newAddressWrap.style.display = 'none';
                }
            });
        }
    }

    async function loadCustomerAddresses(selectedAddressId = null) {
        const userId = customerSelect.value;

        addressError.classList.add('hidden');

        if (!userId) {
            addressList.innerHTML = `
                <div class="text-slate-500">
                    Please select a customer first.
                </div>
            `;
            return;
        }

        addressList.innerHTML = `
            <div class="flex items-center gap-3 text-slate-500">
                <div class="h-5 w-5 rounded-full border-2 border-orange-400 border-t-transparent animate-spin"></div>
                Loading customer addresses...
            </div>
        `;

        try {
            const response = await fetch(`${addressBaseUrl}/${userId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!data.addresses || data.addresses.length === 0) {
                addressList.innerHTML = `
                    <div class="rounded-3xl bg-yellow-50 border border-yellow-200 p-5">
                        <p class="text-yellow-900 font-black">No saved address found for this customer.</p>
                        <p class="text-yellow-700 text-sm mt-1">
                            Add a new delivery address below to continue.
                        </p>
                    </div>

                    ${newAddressBox(true)}
                `;

                bindAddressEvents();
                return;
            }

            const addressCards = data.addresses.map(address => {
                const checked = String(address.id) === String(selectedAddressId) ? 'checked' : '';

                return `
                    <label class="flex gap-4 items-start bg-white border border-slate-200 rounded-3xl p-4 mb-3 cursor-pointer hover:border-orange-400 hover:shadow-sm transition">
                        <input type="checkbox"
                               name="address_id"
                               value="${address.id}"
                               ${checked}
                               class="address-checkbox mt-1 rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                        <span>
                            <span class="block font-black text-slate-800">Delivery Address</span>
                            <span class="block text-slate-600 text-sm mt-1">${escapeHtml(address.label)}</span>
                        </span>
                    </label>
                `;
            }).join('');

            const showNewAddress = oldNewAddress && !selectedAddressId;

            addressList.innerHTML = `
                <div class="mb-3 text-sm font-bold text-slate-500">
                    ${data.addresses.length} saved address${data.addresses.length > 1 ? 'es' : ''} found
                </div>

                ${addressCards}

                ${newAddressBox(showNewAddress)}
            `;

            bindAddressEvents();

        } catch (error) {
            addressList.innerHTML = `
                <div class="rounded-3xl bg-red-50 border border-red-200 p-5">
                    <p class="text-red-700 font-black">Unable to load addresses.</p>
                    <p class="text-red-600 text-sm mt-1">Please refresh the page and try again.</p>
                </div>
            `;
        }
    }

    customerSelect.addEventListener('change', () => loadCustomerAddresses());
    packetSelect.addEventListener('change', calculateSubscription);
    durationSelect.addEventListener('change', calculateSubscription);
    startDateInput.addEventListener('change', calculateSubscription);

    subscriptionForm.addEventListener('submit', function (event) {
        const selectedAddress = document.querySelector('.address-checkbox:checked');
        const newAddress = document.getElementById('new_address');

        const hasNewAddress = newAddress && newAddress.value.trim().length > 0;

        if (!selectedAddress && !hasNewAddress) {
            event.preventDefault();
            addressError.classList.remove('hidden');
            addressList.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        calculateSubscription();

        if (customerSelect.value) {
            loadCustomerAddresses(oldAddressId);
        }
    });
</script>
@endsection