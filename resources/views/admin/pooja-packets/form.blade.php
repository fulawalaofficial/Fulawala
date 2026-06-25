@extends('admin.layout')

@section('title', $packet->exists ? 'Edit Pooja Packet' : 'Add Pooja Packet')

@section('content')
@php
    $durationOptions = [
        1 => 'One Month',
        2 => 'Two Months',
        3 => 'Three Months',
        6 => 'Six Months',
        12 => 'One Year',
    ];

    $items = [];

    if (is_array(old('flower_ids'))) {
        foreach (old('flower_ids') as $index => $flowerId) {
            $items[] = [
                'flower_id' => $flowerId,
                'unit' => old('flower_units.' . $index),
                'quantity' => old('quantities.' . $index),
                'mrp_price' => old('mrp_prices.' . $index),
                'sale_price' => old('sale_prices.' . $index),
            ];
        }
    } else {
        $items = $packet->flower_items;
    }

    if (empty($items)) {
        $items[] = [
            'flower_id' => '',
            'unit' => '',
            'quantity' => '',
            'mrp_price' => '',
            'sale_price' => '',
        ];
    }
@endphp

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">
                {{ $packet->exists ? 'Update Packet' : 'Create Packet' }}
            </p>
            <h1 class="text-3xl font-black text-slate-900">
                {{ $packet->exists ? 'Edit Pooja Packet' : 'Add Pooja Packet' }}
            </h1>
            <p class="text-slate-500 mt-1">
                Select multiple flowers and set unit, quantity, MRP, sale price and packet duration.
            </p>
        </div>

        <a href="{{ route('admin.pooja-packets.index') }}"
           class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition">
            Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <div class="font-black mb-2">Please fix these errors:</div>
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $packet->exists ? route('admin.pooja-packets.update', $packet) : route('admin.pooja-packets.store') }}"
          class="space-y-6">

        @csrf

        @if($packet->exists)
            @method('PUT')
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-b border-orange-100 px-6 py-5">
                <h2 class="text-xl font-black text-slate-900">Packet Basic Details</h2>
                <p class="text-sm text-slate-500 mt-1">Enter packet name, duration, price and status.</p>
            </div>

            <div class="p-6 grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Packet Name *</label>
                    <input type="text"
                           name="packet_name"
                           value="{{ old('packet_name', $packet->packet_name) }}"
                           placeholder="Example: Daily Pooja Premium Packet"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Packet Duration *</label>
                    <select name="duration_months"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                        @foreach($durationOptions as $value => $label)
                            <option value="{{ $value }}" @selected((int) old('duration_months', $packet->duration_months ?: 1) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Monthly Price *</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="monthly_price"
                           value="{{ old('monthly_price', $packet->monthly_price) }}"
                           placeholder="Example: 999"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Weekly Price</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="weekly_price"
                           value="{{ old('weekly_price', $packet->weekly_price) }}"
                           placeholder="Example: 299"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Daily Quantity</label>
                    <input type="text"
                           name="daily_quantity"
                           value="{{ old('daily_quantity', $packet->daily_quantity) }}"
                           placeholder="Example: 250 gm / 1 packet daily"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Package Type</label>
                    <input type="text"
                           name="package_type"
                           value="{{ old('package_type', $packet->package_type) }}"
                           placeholder="Example: Basic / Premium / Temple Special"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Image URL</label>
                    <input type="text"
                           name="image"
                           value="{{ old('image', $packet->image) }}"
                           placeholder="Optional image path or URL"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Status *</label>
                    <select name="status"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                        <option value="Active" @selected(old('status', $packet->status ?: 'Active') === 'Active')>Active</option>
                        <option value="Inactive" @selected(old('status', $packet->status) === 'Inactive')>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-slate-700 mb-1">Description</label>
                    <textarea name="description"
                              rows="4"
                              placeholder="Write packet details..."
                              class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">{{ old('description', $packet->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-orange-50 border-b border-slate-100 px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Select Flowers</h2>
                    <p class="text-sm text-slate-500 mt-1">Add multiple flowers with quantity, unit, MRP and sale price.</p>
                </div>

                <button type="button"
                        id="addFlowerRow"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-black shadow-lg shadow-green-100 transition">
                    + Add Flower
                </button>
            </div>

            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-4 mb-5">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                        <p class="text-sm font-bold text-slate-500">Total MRP</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1">
                            ₹<span id="totalMrp">0.00</span>
                        </h3>
                    </div>

                    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-5">
                        <p class="text-sm font-bold text-orange-600">Total Sale Price</p>
                        <h3 class="text-2xl font-black text-orange-700 mt-1">
                            ₹<span id="totalSale">0.00</span>
                        </h3>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left min-w-[240px]">Flower *</th>
                                <th class="p-3 text-left min-w-[120px]">Unit *</th>
                                <th class="p-3 text-left min-w-[120px]">Quantity *</th>
                                <th class="p-3 text-left min-w-[130px]">MRP *</th>
                                <th class="p-3 text-left min-w-[130px]">Sale Price *</th>
                                <th class="p-3 text-center min-w-[90px]">Action</th>
                            </tr>
                        </thead>

                        <tbody id="flowerRows">
                            @foreach($items as $item)
                                <tr data-flower-row class="border-b border-slate-100">
                                    <td class="p-3">
                                        <select name="flower_ids[]"
                                                class="flower-select w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                            <option value="">Select Flower</option>
                                            @foreach($flowers as $flower)
                                                <option value="{{ $flower->id }}"
                                                        data-unit="{{ $flower->unit }}"
                                                        data-price="{{ $flower->price }}"
                                                        @selected((string) ($item['flower_id'] ?? '') === (string) $flower->id)>
                                                    {{ $flower->flower_name }} - ₹{{ $flower->price }} / {{ $flower->unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="p-3">
                                        <input type="text"
                                               name="flower_units[]"
                                               value="{{ $item['unit'] ?? '' }}"
                                               placeholder="kg / gm / pcs"
                                               class="unit-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="quantities[]"
                                               value="{{ $item['quantity'] ?? '' }}"
                                               placeholder="Qty"
                                               class="qty-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="mrp_prices[]"
                                               value="{{ $item['mrp_price'] ?? '' }}"
                                               placeholder="MRP"
                                               class="mrp-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="sale_prices[]"
                                               value="{{ $item['sale_price'] ?? '' }}"
                                               placeholder="Sale Price"
                                               class="sale-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button"
                                                data-remove-row
                                                class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg font-black transition">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-slate-500 mt-3">
                    Tip: When you select a flower, unit, MRP and sale price are auto-filled from flower product price. You can edit them manually.
                </p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 md:justify-end">
            <a href="{{ route('admin.pooja-packets.index') }}"
               class="px-6 py-3 rounded-xl border border-slate-200 text-slate-700 font-black hover:bg-slate-50 text-center">
                Cancel
            </a>

            <button type="submit"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-orange-200 transition">
                {{ $packet->exists ? 'Update Packet' : 'Save Packet' }}
            </button>
        </div>
    </form>
</div>

<template id="flowerRowTemplate">
    <tr data-flower-row class="border-b border-slate-100">
        <td class="p-3">
            <select name="flower_ids[]"
                    class="flower-select w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                <option value="">Select Flower</option>
                @foreach($flowers as $flower)
                    <option value="{{ $flower->id }}"
                            data-unit="{{ $flower->unit }}"
                            data-price="{{ $flower->price }}">
                        {{ $flower->flower_name }} - ₹{{ $flower->price }} / {{ $flower->unit }}
                    </option>
                @endforeach
            </select>
        </td>

        <td class="p-3">
            <input type="text"
                   name="flower_units[]"
                   placeholder="kg / gm / pcs"
                   class="unit-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number"
                   step="0.01"
                   min="0"
                   name="quantities[]"
                   placeholder="Qty"
                   class="qty-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number"
                   step="0.01"
                   min="0"
                   name="mrp_prices[]"
                   placeholder="MRP"
                   class="mrp-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number"
                   step="0.01"
                   min="0"
                   name="sale_prices[]"
                   placeholder="Sale Price"
                   class="sale-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3 text-center">
            <button type="button"
                    data-remove-row
                    class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg font-black transition">
                Remove
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const flowerRows = document.getElementById('flowerRows');
    const addFlowerRowButton = document.getElementById('addFlowerRow');
    const flowerRowTemplate = document.getElementById('flowerRowTemplate');
    const totalMrpElement = document.getElementById('totalMrp');
    const totalSaleElement = document.getElementById('totalSale');

    function parseAmount(value) {
        const number = parseFloat(value);
        return isNaN(number) ? 0 : number;
    }

    function calculateTotals() {
        let totalMrp = 0;
        let totalSale = 0;

        flowerRows.querySelectorAll('[data-flower-row]').forEach(function (row) {
            const quantity = parseAmount(row.querySelector('.qty-input')?.value);
            const mrp = parseAmount(row.querySelector('.mrp-input')?.value);
            const sale = parseAmount(row.querySelector('.sale-input')?.value);

            totalMrp += quantity * mrp;
            totalSale += quantity * sale;
        });

        totalMrpElement.textContent = totalMrp.toFixed(2);
        totalSaleElement.textContent = totalSale.toFixed(2);
    }

    function fillFlowerDetails(select) {
        const selectedOption = select.options[select.selectedIndex];
        const row = select.closest('[data-flower-row]');

        if (!selectedOption || !selectedOption.value || !row) {
            return;
        }

        const unitInput = row.querySelector('.unit-input');
        const mrpInput = row.querySelector('.mrp-input');
        const saleInput = row.querySelector('.sale-input');

        const unit = selectedOption.dataset.unit || '';
        const price = selectedOption.dataset.price || '';

        unitInput.value = unit;
        mrpInput.value = price;
        saleInput.value = price;

        calculateTotals();
    }

    function bindRowEvents(row) {
        const select = row.querySelector('.flower-select');

        if (select) {
            select.addEventListener('change', function () {
                fillFlowerDetails(select);
            });
        }

        row.querySelectorAll('input').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
        });
    }

    flowerRows.querySelectorAll('[data-flower-row]').forEach(bindRowEvents);

    addFlowerRowButton.addEventListener('click', function () {
        const clone = flowerRowTemplate.content.cloneNode(true);
        const newRow = clone.querySelector('[data-flower-row]');

        flowerRows.appendChild(clone);
        bindRowEvents(newRow);
        calculateTotals();
    });

    flowerRows.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-remove-row]');

        if (!removeButton) {
            return;
        }

        const rows = flowerRows.querySelectorAll('[data-flower-row]');

        if (rows.length <= 1) {
            alert('At least one flower is required.');
            return;
        }

        removeButton.closest('[data-flower-row]').remove();
        calculateTotals();
    });

    calculateTotals();
});
</script>
@endsection