@extends('admin.layout')

@section('title', $flower->exists ? 'Edit Flower Product' : 'Add Flower Product')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-orange-600 uppercase tracking-wide">
                {{ $flower->exists ? 'Update Product' : 'Create Product' }}
            </p>

            <h1 class="text-3xl font-black text-gray-900">
                {{ $flower->exists ? 'Edit Flower Product' : 'Add Flower Product' }}
            </h1>

            <p class="text-gray-500 mt-1">
                Add flower photo, price, stock and product details.
            </p>
        </div>

        <a href="{{ route('admin.flowers.index') }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-xl font-bold">
            Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl">
            <h3 class="font-black mb-2">Please fix these errors:</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $flower->exists ? route('admin.flowers.update', $flower) : route('admin.flowers.store') }}"
          enctype="multipart/form-data"
          class="bg-white border rounded-3xl shadow-sm overflow-hidden">

        @csrf

        @if($flower->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-0">

            <div class="bg-orange-50 border-r p-6">
                <label class="block text-sm font-black text-gray-800 mb-3">
                    Flower Photo
                </label>

                <div class="w-full aspect-square rounded-3xl bg-white border-2 border-dashed border-orange-300 overflow-hidden flex items-center justify-center">
                    @if($flower->image)
                        <img id="imagePreview"
                             src="{{ asset('storage/' . $flower->image) }}"
                             alt="{{ $flower->flower_name }}"
                             class="w-full h-full object-cover">
                    @else
                        <img id="imagePreview"
                             src=""
                             alt=""
                             class="hidden w-full h-full object-cover">
                        <div id="imagePlaceholder" class="text-center px-5">
                            <div class="text-6xl mb-3">🌼</div>
                            <p class="font-bold text-gray-700">Upload flower photo</p>
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP max 2MB</p>
                        </div>
                    @endif
                </div>

                <input type="file"
                       name="image"
                       id="imageInput"
                       accept="image/png,image/jpeg,image/jpg,image/webp"
                       class="mt-4 w-full border rounded-xl px-4 py-3 bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">

                @error('image')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror

                @if($flower->exists)
                    <p class="text-xs text-gray-500 mt-3">
                        Leave photo empty if you do not want to change the existing image.
                    </p>
                @endif
            </div>

            <div class="lg:col-span-2 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Flower Name <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="flower_name"
                           value="{{ old('flower_name', $flower->flower_name) }}"
                           placeholder="Example: Marigold, Rose, Jasmine"
                           class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">

                    @error('flower_name')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Category
                    </label>

                    <input type="text"
                           name="category"
                           value="{{ old('category', $flower->category) }}"
                           placeholder="Example: Pooja, Garland, Decoration"
                           class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">

                    @error('category')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Price <span class="text-red-500">*</span>
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="price"
                           value="{{ old('price', $flower->price) }}"
                           placeholder="Example: 50"
                           class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">

                    @error('price')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Unit <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           name="unit"
                           value="{{ old('unit', $flower->unit) }}"
                           placeholder="Example: 500g, 20 pieces, 1 kg"
                           class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">

                    @error('unit')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Stock Status <span class="text-red-500">*</span>
                    </label>

                    <select name="stock_status"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="In Stock" @selected(old('stock_status', $flower->stock_status) === 'In Stock')>
                            In Stock
                        </option>
                        <option value="Out of Stock" @selected(old('stock_status', $flower->stock_status) === 'Out of Stock')>
                            Out of Stock
                        </option>
                    </select>

                    @error('stock_status')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>

                    <select name="status"
                            class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="Active" @selected(old('status', $flower->status) === 'Active')>
                            Active
                        </option>
                        <option value="Inactive" @selected(old('status', $flower->status) === 'Inactive')>
                            Inactive
                        </option>
                    </select>

                    @error('status')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-black text-gray-800 mb-2">
                        Description
                    </label>

                    <textarea name="description"
                              rows="5"
                              placeholder="Write short details about this flower..."
                              class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:outline-none">{{ old('description', $flower->description) }}</textarea>

                    @error('description')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 flex flex-col md:flex-row gap-3 pt-4">
                    <button class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-xl font-black shadow">
                        {{ $flower->exists ? 'Update Flower' : 'Save Flower' }}
                    </button>

                    <a href="{{ route('admin.flowers.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-black text-center">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');

    if (imageInput) {
        imageInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (file) {
                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove('hidden');

                if (imagePlaceholder) {
                    imagePlaceholder.classList.add('hidden');
                }
            }
        });
    }
</script>

@endsection