<div class="max-w-5xl mx-auto space-y-6">
    <a href="{{ route('admin.home-banners.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-orange-700 hover:underline">← Back to Banner Master</a>
    <div class="bg-white rounded-3xl p-5 md:p-8 border border-orange-100 shadow-soft">
        <div class="border-b border-orange-100 pb-6 mb-6">
            <div class="text-xs text-orange-600 uppercase tracking-widest font-black">Fulawala / Banner Master</div>
            <h1 class="text-2xl font-black text-slate-900 mt-1">{{ $formTitle }}</h1>
            <p class="text-sm text-slate-500 mt-2">{{ $formHelp }}</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 text-sm text-red-700">
                <p class="font-black">Please check the highlighted fields:</p>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($isEditing) @method('PUT') @endif

            <div class="grid lg:grid-cols-2 gap-7">
                <div class="space-y-5">
                    <div>
                        <label for="title" class="block text-sm font-black text-slate-800 mb-2">Banner title <span class="text-red-600">*</span></label>
                        <input id="title" name="title" required maxlength="100" type="text" value="{{ old('title', $banner->title) }}"
                               placeholder="Example: Fresh Flowers" class="w-full rounded-2xl border border-orange-200 focus:border-orange-500 focus:ring-orange-200 px-4 py-3 outline-none">
                        <p class="text-xs text-slate-500 mt-1">For the admin list and screen-reader description; no extra text is added over your artwork.</p>
                    </div>

                    <div>
                        <label for="target_screen" class="block text-sm font-black text-slate-800 mb-2">Open page on banner tap</label>
                        <select id="target_screen" name="target_screen" class="w-full rounded-2xl border border-orange-200 focus:border-orange-500 px-4 py-3 bg-white outline-none">
                            <option value="">No navigation — image only</option>
                            @foreach($targets as $screen => $label)
                                <option value="{{ $screen }}" @selected(old('target_screen', $banner->target_screen) === $screen)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Choose a supported screen in the existing mobile application.</p>
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-black text-slate-800 mb-2">Display order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" required
                               value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
                               class="w-full rounded-2xl border border-orange-200 focus:border-orange-500 px-4 py-3 outline-none">
                        <p class="text-xs text-slate-500 mt-1">Lower number appears first. Equal numbers are sorted by banner ID.</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="starts_at" class="block text-sm font-black text-slate-800 mb-2">Start date (optional)</label>
                            <input id="starts_at" name="starts_at" type="datetime-local"
                                   value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\\TH:i')) }}"
                                   class="w-full rounded-2xl border border-orange-200 px-3 py-3 outline-none">
                        </div>
                        <div>
                            <label for="ends_at" class="block text-sm font-black text-slate-800 mb-2">End date (optional)</label>
                            <input id="ends_at" name="ends_at" type="datetime-local"
                                   value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\\TH:i')) }}"
                                   class="w-full rounded-2xl border border-orange-200 px-3 py-3 outline-none">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Dates use your Laravel application's configured timezone. Leave blank to start immediately or never expire.</p>

                    <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-black text-slate-900">Publish banner</p>
                            <p class="text-xs text-slate-500 mt-1">Only published banners within their date window appear in the app.</p>
                        </div>
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               @checked((bool) old('is_active', $banner->is_active ?? true))
                               class="h-6 w-6 rounded border-orange-300 text-orange-600 focus:ring-orange-300">
                    </div>
                </div>

                <div class="space-y-4">
                    <label for="image" class="block text-sm font-black text-slate-800">Banner image {!! $isEditing ? '(replace optional)' : '<span class="text-red-600">*</span>' !!}</label>
                    <div class="rounded-3xl bg-orange-50 border-2 border-dashed border-orange-200 p-3">
                        <div class="w-full aspect-[2/1] bg-white rounded-2xl overflow-hidden flex items-center justify-center border border-orange-100">
                            <img id="bannerPreview" src="{{ $banner->image_url ?: '' }}" alt="Banner preview"
                                 class="w-full h-full object-fill {{ $banner->image_url ? '' : 'hidden' }}">
                            <p id="bannerEmpty" class="text-slate-400 text-sm font-semibold {{ $banner->image_url ? 'hidden' : '' }}">Image preview will appear here</p>
                        </div>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" {{ $isEditing ? '' : 'required' }}
                               class="block w-full mt-4 text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-orange-600 file:px-4 file:py-2 file:font-black file:text-white">
                        <p class="text-xs text-slate-500 mt-3">Use approximately 1200 × 600 px so the entire message remains readable in the mobile slider. Maximum file size: 5 MB.</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-orange-100">
                <a href="{{ route('admin.home-banners.index') }}" class="px-6 py-3 rounded-xl border border-orange-200 text-slate-600 text-center font-black">Cancel</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-orange-500 to-red-500 text-white text-center font-black shadow-lg shadow-orange-200">{{ $isEditing ? 'Save Changes' : 'Publish Banner' }}</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    (function () {
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('bannerPreview');
        const empty = document.getElementById('bannerEmpty');
        let blobUrl = null;
        if (!fileInput || !preview) return;
        fileInput.addEventListener('change', function () {
            if (blobUrl) URL.revokeObjectURL(blobUrl);
            const file = fileInput.files && fileInput.files[0];
            if (!file) return;
            blobUrl = URL.createObjectURL(file);
            preview.src = blobUrl;
            preview.classList.remove('hidden');
            if (empty) empty.classList.add('hidden');
        });
    })();
</script>
@endpush
