@extends('admin.layout')

@section('title', 'Flower Products')

@section('content')

<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">Inventory Management</p>
            <h1 class="text-3xl font-black text-gray-900">Flower Products</h1>
            <p class="text-gray-500 mt-1">Manage flower photos, stock, price and product status.</p>
        </div>

        <a href="{{ route('admin.flowers.create') }}"
           class="inline-flex items-center justify-center bg-orange-600 hover:bg-orange-700 text-white px-5 py-3 rounded-xl font-bold shadow">
            + Add Flower
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Flowers</p>
            <h2 class="text-3xl font-black text-gray-900 mt-1">{{ $totalFlowers }}</h2>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Active</p>
            <h2 class="text-3xl font-black text-green-600 mt-1">{{ $activeFlowers }}</h2>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Inactive</p>
            <h2 class="text-3xl font-black text-gray-500 mt-1">{{ $inactiveFlowers }}</h2>
        </div>

        <div class="bg-white border rounded-2xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Out of Stock</p>
            <h2 class="text-3xl font-black text-red-600 mt-1">{{ $outOfStockFlowers }}</h2>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.flowers.index') }}"
          class="bg-white border rounded-2xl p-5 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search flower, category, unit..."
               class="border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none md:col-span-2">

        <select name="status"
                class="border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">All Status</option>
            <option value="Active" @selected(request('status') === 'Active')>Active</option>
            <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
        </select>

        <select name="stock_status"
                class="border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">
            <option value="">All Stock</option>
            <option value="In Stock" @selected(request('stock_status') === 'In Stock')>In Stock</option>
            <option value="Out of Stock" @selected(request('stock_status') === 'Out of Stock')>Out of Stock</option>
        </select>

        <div class="md:col-span-4 flex gap-3">
            <button class="bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-xl font-bold">
                Filter
            </button>

            <a href="{{ route('admin.flowers.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-xl font-bold">
                Reset
            </a>
        </div>
    </form>

    <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-orange-50 border-b">
                    <tr>
                        <th class="p-4 text-left">Flower</th>
                        <th class="p-4 text-left">Category</th>
                        <th class="p-4 text-center">Price</th>
                        <th class="p-4 text-center">Stock</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($flowers as $flower)
                        <tr class="border-b hover:bg-orange-50/40 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-orange-100 flex items-center justify-center border">
                                        @if($flower->image)
                                            <img src="{{ asset('storage/' . $flower->image) }}"
                                                 alt="{{ $flower->flower_name }}"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <span class="text-2xl">🌼</span>
                                        @endif
                                    </div>

                                    <div>
                                        <h3 class="font-black text-gray-900">{{ $flower->flower_name }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $flower->description ? \Illuminate\Support\Str::limit($flower->description, 55) : 'No description added' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 text-gray-700">
                                {{ $flower->category ?? 'N/A' }}
                            </td>

                            <td class="p-4 text-center">
                                <span class="font-black text-gray-900">
                                    ₹{{ number_format($flower->price, 2) }}
                                </span>
                                <span class="text-gray-500">/ {{ $flower->unit }}</span>
                            </td>

                            <td class="p-4 text-center">
                                @if($flower->stock_status === 'In Stock')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                        In Stock
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">
                                        Out of Stock
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                @if($flower->status === 'Active')
                                    <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold">
                                        Active
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-3">
                                    <a href="{{ route('admin.flowers.edit', $flower) }}"
                                       class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-bold">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.flowers.destroy', $flower) }}"
                                          onsubmit="return confirm('Delete this flower?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-bold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center">
                                <div class="text-5xl mb-3">🌸</div>
                                <h3 class="font-black text-xl text-gray-900">No flowers found</h3>
                                <p class="text-gray-500 mt-1">Add your first flower product now.</p>
                                <a href="{{ route('admin.flowers.create') }}"
                                   class="inline-block mt-4 bg-orange-600 text-white px-5 py-3 rounded-xl font-bold">
                                    Add Flower
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $flowers->links() }}
    </div>

</div>

@endsection