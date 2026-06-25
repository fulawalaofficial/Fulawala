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
                Add package photo, package type, price and multiple flowers with quantity.
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
                <p class="text-sm text-slate-500 mt-1">Enter package name, type, photo, MRP and sale price.</p>
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
                    <label class="block text-sm font-black text-slate-700 mb-1">Package MRP Price *</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="mrp_price"
                           value="{{ old('mrp_price', $packet->mrp_price) }}"
                           placeholder="Example: 1500"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-700 mb-1">Package Sale Price *</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="sale_price"
                           value="{{ old('sale_price', $packet->sale_price) }}"
                           placeholder="Example: 999"
                           class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
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
                        Select multiple flowers and add unit, quantity, price, MRP and sale price.
                    </p>
                </div>

                <button type="button"
                        id="addFlowerRow"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-black shadow-lg shadow-green-100 transition">
                    + Add Flower
                </button>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="p-3 text-left min-w-[240px]">Flower *</th>
                                <th class="p-3 text-left min-w-[110px]">Unit *</th>
                                <th class="p-3 text-left min-w-[110px]">Quantity *</th>
                                <th class="p-3 text-left min-w-[120px]">Price *</th>
                                <th class="p-3 text-left min-w-[120px]">MRP</th>
                                <th class="p-3 text-left min-w-[120px]">Sale Price</th>
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
                                               name="prices[]"
                                               value="{{ $item['price'] ?? '' }}"
                                               placeholder="Price"
                                               class="price-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
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
                    When you select a flower, unit and price are auto-filled from the flower product.
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
                {{ $packet->exists ? 'Update Package' : 'Save Package' }}
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
            <input type="text" name="flower_units[]" placeholder="kg/gm/pcs"
                   class="unit-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="quantities[]" placeholder="Qty"
                   class="qty-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="prices[]" placeholder="Price"
                   class="price-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="flower_mrp_prices[]" placeholder="MRP"
                   class="mrp-input w-full border border-slate-200 rounded-xl px-3 py-3 focus:ring-2 focus:ring-orange-200 focus:border-orange-500 outline-none">
        </td>

        <td class="p-3">
            <input type="number" step="0.01" min="0" name="flower_sale_prices[]" placeholder="Sale"
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

    function fillFlowerDetails(select) {
        const selectedOption = select.options[select.selectedIndex];
        const row = select.closest('[data-flower-row]');

        if (!selectedOption || !selectedOption.value || !row) {
            return;
        }

        const unit = selectedOption.dataset.unit || '';
        const price = selectedOption.dataset.price || '';

        row.querySelector('.unit-input').value = unit;
        row.querySelector('.price-input').value = price;
        row.querySelector('.mrp-input').value = price;
        row.querySelector('.sale-input').value = price;
    }

    function bindRowEvents(row) {
        const select = row.querySelector('.flower-select');

        if (select) {
            select.addEventListener('change', function () {
                fillFlowerDetails(select);
            });
        }
    }

    flowerRows.querySelectorAll('[data-flower-row]').forEach(bindRowEvents);

    addFlowerRowButton.addEventListener('click', function () {
        const clone = flowerRowTemplate.content.cloneNode(true);
        const newRow = clone.querySelector('[data-flower-row]');

        flowerRows.appendChild(clone);
        bindRowEvents(newRow);
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
    });
});
</script>
@endsection