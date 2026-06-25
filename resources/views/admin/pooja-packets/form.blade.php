@extends('admin.layout')

@section('title', $packet->exists ? 'Edit Pooja Package' : 'Add Pooja Package')

@section('content')
@php
    $items = [];

    if (is_array(old('flower_ids'))) {
        foreach (old('flower_ids') as $index => $flowerId) {
            $items[] = [
                'flower_id' => $flowerId,
                'unit' => old('flower_units.' . $index),
                'quantity' => old('quantities.' . $index),
                'price' => old('prices.' . $index),
                'mrp_price' => old('flower_mrp_prices.' . $index),
                'sale_price' => old('flower_sale_prices.' . $index),
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
            'price' => '',
            'mrp_price' => '',
            'sale_price' => '',
        ];
    }

    $selectedPackageType = old('package_type', $packet->package_type ?: 'Monthly');
    $selectedStatus = old('status', $packet->status ?: 'Active');
@endphp

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-orange-600 uppercase tracking-wide">
                {{ $packet->exists ? 'Update Package' : 'Create Package' }}
            </p>
            <h1 class="text-3xl font-black text-slate-900">
                {{ $packet->exists ? 'Edit Pooja Package' : 'Add Pooja Package' }}
            </h1>
            <p class="text-slate-500 mt-1">
                Select flowers, enter quantity, and package total will calculate automatically.
            </p>
        </div>

        <a href="{{ route('admin.pooja-packets.index') }}"
           class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-bold hover:bg-slate-50 transition">
            Back
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
          enctype="multipart/form-data"
          action="{{ $packet->exists ? route('admin.pooja-packets.update', $packet) : route('admin.pooja-packets.store') }}"
          class="space-y-6">

        @csrf

        @if($packet->exists)
            @method('PUT')
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-b border-orange-100 px-6 py-5">
                <h2 class="text-xl font-black text-slate-900">Package Details</h2>
                <p class="text-sm text-slate-500 mt-1">Enter package name, type, photo and description.</p>
            </div>

            <div class="p-6 grid md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Package Name *</label>
                    <input type="text"
                           name="packet_name"
                           value="{{ old('packet_name', $packet->packet_name) }}"
                           placeholder="Example: Premium Daily Pooja Package"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Package Type *</label>
                    <select name="package_type"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                        <option value="Monthly" {{ $selectedPackageType == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="Three Month" {{ $selectedPackageType == 'Three Month' ? 'selected' : '' }}>Three Month</option>
                        <option value="Six Month" {{ $selectedPackageType == 'Six Month' ? 'selected' : '' }}>Six Month</option>
                        <option value="One Year" {{ $selectedPackageType == 'One Year' ? 'selected' : '' }}>One Year</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Auto Package MRP</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-bold">₹</span>
                        <input type="text"
                               id="packageMrpDisplay"
                               value="{{ old('mrp_price', $packet->mrp_price) }}"
                               readonly
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-4 py-3 font-black text-slate-800 outline-none">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Calculated from flower MRP × quantity.</p>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Auto Package Sale Price</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-green-500 font-bold">₹</span>
                        <input type="text"
                               id="packageSaleDisplay"
                               value="{{ old('sale_price', $packet->sale_price) }}"
                               readonly
                               class="w-full bg-green-50 border border-green-200 rounded-xl pl-8 pr-4 py-3 font-black text-green-700 outline-none">
                    </div>
                    <p class="text-xs text-slate-500 mt-1">This amount will save as package sale price.</p>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Package Photo</label>
                    <input type="file"
                           name="image"
                           accept="image/*"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">

                    @if($packet->image)
                        <div class="mt-3">
                            <img src="{{ asset($packet->image) }}"
                                 alt="Package Photo"
                                 class="w-28 h-28 rounded-2xl object-cover border border-orange-100">
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Status *</label>
                    <select name="status"
                            class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                        <option value="Active" {{ $selectedStatus == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $selectedStatus == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-slate-700 mb-1">Description</label>
                    <textarea name="description"
                              rows="4"
                              placeholder="Write package description..."
                              class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">{{ old('description', $packet->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-orange-50 border-b border-slate-100 px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Package Flowers</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Select flower and quantity. Unit, price and totals will fill automatically.
                    </p>
                </div>

                <button type="button"
                        id="addFlowerRow"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-black shadow-lg shadow-green-100 transition">
                    + Add Flower
                </button>
            </div>

            <div class="p-6">

                <div class="grid md:grid-cols-5 gap-4 mb-5">
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                        <p class="text-xs font-black text-slate-500 uppercase">Flower Rows</p>
                        <h3 class="text-2xl font-black text-slate-900 mt-1" id="totalRows">0</h3>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                        <p class="text-xs font-black text-blue-600 uppercase">Total Quantity</p>
                        <h3 class="text-2xl font-black text-blue-700 mt-1" id="totalQuantity">0</h3>
                    </div>

                    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
                        <p class="text-xs font-black text-orange-600 uppercase">Total MRP</p>
                        <h3 class="text-2xl font-black text-orange-700 mt-1">₹<span id="totalMrp">0.00</span></h3>
                    </div>

                    <div class="bg-green-50 border border-green-100 rounded-2xl p-4">
                        <p class="text-xs font-black text-green-600 uppercase">Total Sale</p>
                        <h3 class="text-2xl font-black text-green-700 mt-1">₹<span id="totalSale">0.00</span></h3>
                    </div>

                    <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
                        <p class="text-xs font-black text-red-500 uppercase">Saving</p>
                        <h3 class="text-2xl font-black text-red-600 mt-1">₹<span id="totalSaving">0.00</span></h3>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left min-w-[250px]">Flower *</th>
                                <th class="p-3 text-left min-w-[100px]">Unit *</th>
                                <th class="p-3 text-left min-w-[110px]">Quantity *</th>
                                <th class="p-3 text-left min-w-[120px]">Unit Price *</th>
                                <th class="p-3 text-left min-w-[120px]">MRP / Unit</th>
                                <th class="p-3 text-left min-w-[120px]">Sale / Unit</th>
                                <th class="p-3 text-left min-w-[130px]">Line Total</th>
                                <th class="p-3 text-center min-w-[90px]">Action</th>
                            </tr>
                        </thead>

                        <tbody id="flowerRows">
                            @foreach($items as $item)
                                <tr data-flower-row class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="p-3">
                                        <select name="flower_ids[]"
                                                class="flower-select w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                            <option value="">Select Flower</option>
                                            @foreach($flowers as $flower)
                                                <option value="{{ $flower->id }}"
                                                        data-unit="{{ $flower->unit }}"
                                                        data-price="{{ $flower->price }}"
                                                        {{ (string)($item['flower_id'] ?? '') == (string)$flower->id ? 'selected' : '' }}>
                                                    {{ $flower->flower_name }} - ₹{{ $flower->price }} / {{ $flower->unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="p-3">
                                        <input type="text"
                                               name="flower_units[]"
                                               value="{{ $item['unit'] ?? '' }}"
                                               placeholder="kg/gm/pcs"
                                               class="unit-input w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
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
                                               name="prices[]"
                                               value="{{ $item['price'] ?? '' }}"
                                               placeholder="Price"
                                               class="price-input w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="flower_mrp_prices[]"
                                               value="{{ $item['mrp_price'] ?? '' }}"
                                               placeholder="MRP"
                                               class="mrp-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="flower_sale_prices[]"
                                               value="{{ $item['sale_price'] ?? '' }}"
                                               placeholder="Sale"
                                               class="sale-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <div class="bg-green-50 border border-green-100 rounded-xl px-3 py-3 font-black text-green-700">
                                            ₹<span class="line-total">0.00</span>
                                        </div>
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
                    Tip: Select a flower first. Unit price, MRP and sale price will auto-fill. Change quantity to update total.
                </p>
            </div>
        </div>

        <div class="sticky bottom-0 bg-white border border-slate-200 rounded-2xl p-4 shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-slate-500">Final Package Sale Total</p>
                <h3 class="text-2xl font-black text-green-700">
                    ₹<span id="footerSaleTotal">0.00</span>
                </h3>
            </div>

            <div class="flex flex-col md:flex-row gap-3">
                <a href="{{ route('admin.pooja-packets.index') }}"
                   class="px-6 py-3 rounded-xl border border-slate-200 text-slate-700 font-black hover:bg-slate-50 text-center">
                    Cancel
                </a>

                <button type="submit"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-xl font-black shadow-lg shadow-orange-200 transition">
                    {{ $packet->exists ? 'Update Package' : 'Save Package' }}
                </button>
            </div>
        </div>
    </form>
</div>

<template id="flowerRowTemplate">
    <tr data-flower-row class="border-b border-slate-100 hover:bg-slate-50">
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
            <input type="text" name="flower_units[]" placeholder="kg/gm/pcs"
                   class="unit-input w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="quantities[]" placeholder="Qty"
                   class="qty-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="prices[]" placeholder="Price"
                   class="price-input w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="flower_mrp_prices[]" placeholder="MRP"
                   class="mrp-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="flower_sale_prices[]" placeholder="Sale"
                   class="sale-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <div class="bg-green-50 border border-green-100 rounded-xl px-3 py-3 font-black text-green-700">
                ₹<span class="line-total">0.00</span>
            </div>
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

    const packageMrpDisplay = document.getElementById('packageMrpDisplay');
    const packageSaleDisplay = document.getElementById('packageSaleDisplay');

    const totalRows = document.getElementById('totalRows');
    const totalQuantity = document.getElementById('totalQuantity');
    const totalMrp = document.getElementById('totalMrp');
    const totalSale = document.getElementById('totalSale');
    const totalSaving = document.getElementById('totalSaving');
    const footerSaleTotal = document.getElementById('footerSaleTotal');

    function amount(value) {
        const number = parseFloat(value);
        return isNaN(number) ? 0 : number;
    }

    function money(value) {
        return amount(value).toFixed(2);
    }

    function fillFlowerDetails(select) {
        const selectedOption = select.options[select.selectedIndex];
        const row = select.closest('[data-flower-row]');

        if (!selectedOption || !selectedOption.value || !row) {
            return;
        }

        const unit = selectedOption.dataset.unit || '';
        const price = selectedOption.dataset.price || '0';

        const unitInput = row.querySelector('.unit-input');
        const qtyInput = row.querySelector('.qty-input');
        const priceInput = row.querySelector('.price-input');
        const mrpInput = row.querySelector('.mrp-input');
        const saleInput = row.querySelector('.sale-input');

        unitInput.value = unit;
        priceInput.value = money(price);

        if (!qtyInput.value || amount(qtyInput.value) <= 0) {
            qtyInput.value = 1;
        }

        mrpInput.value = money(price);
        saleInput.value = money(price);

        calculateTotals();
    }

    function calculateTotals() {
        let rowsCount = 0;
        let quantityTotal = 0;
        let mrpTotal = 0;
        let saleTotal = 0;

        flowerRows.querySelectorAll('[data-flower-row]').forEach(function (row) {
            const flowerSelect = row.querySelector('.flower-select');
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const mrpInput = row.querySelector('.mrp-input');
            const saleInput = row.querySelector('.sale-input');
            const lineTotal = row.querySelector('.line-total');

            const hasFlower = flowerSelect && flowerSelect.value;
            const qty = amount(qtyInput ? qtyInput.value : 0);
            const unitPrice = amount(priceInput ? priceInput.value : 0);
            const mrp = amount(mrpInput ? mrpInput.value : unitPrice);
            const sale = amount(saleInput ? saleInput.value : unitPrice);

            if (hasFlower) {
                rowsCount++;
            }

            quantityTotal += qty;
            mrpTotal += qty * mrp;
            saleTotal += qty * sale;

            if (lineTotal) {
                lineTotal.textContent = money(qty * sale);
            }
        });

        const savingTotal = mrpTotal - saleTotal;

        totalRows.textContent = rowsCount;
        totalQuantity.textContent = money(quantityTotal);
        totalMrp.textContent = money(mrpTotal);
        totalSale.textContent = money(saleTotal);
        totalSaving.textContent = money(savingTotal > 0 ? savingTotal : 0);
        footerSaleTotal.textContent = money(saleTotal);

        packageMrpDisplay.value = money(mrpTotal);
        packageSaleDisplay.value = money(saleTotal);
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
            input.addEventListener('change', calculateTotals);
        });
    }

    flowerRows.querySelectorAll('[data-flower-row]').forEach(function (row) {
        bindRowEvents(row);
    });

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