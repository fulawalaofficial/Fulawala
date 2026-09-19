@if($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800">
        <p class="font-black">Please fix these fields:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-black text-gray-900">Basic Event Information</h2>
            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-black text-gray-700">Event Name *</label>
                    <input required name="name" value="{{ old('name', $eventMaster->name) }}" placeholder="Example: Premium Wedding Decoration" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Slug</label>
                    <input name="slug" value="{{ old('slug', $eventMaster->slug) }}" placeholder="premium-wedding-decoration" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                    <p class="mt-1 text-xs text-gray-500">Leave blank to generate automatically.</p>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Starting Price</label>
                    <input type="number" step="0.01" min="0" name="starting_price" value="{{ old('starting_price', $eventMaster->starting_price) }}" placeholder="25000" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-black text-gray-700">Short Description</label>
                    <textarea name="short_description" rows="3" maxlength="500" placeholder="Short summary shown on event cards..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('short_description', $eventMaster->short_description) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-black text-gray-700">Full Description</label>
                    <textarea name="description" rows="8" placeholder="Describe decoration style, flowers, stage, entry setup, service coverage and important notes..." class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100">{{ old('description', $eventMaster->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-gray-900">Event Plans</h2>
                    <p class="mt-1 text-sm text-gray-500">Add Basic, Premium, Luxury or any custom plan. Put one feature per line.</p>
                </div>
                <button type="button" id="add-plan" class="rounded-2xl bg-orange-100 px-4 py-2 text-sm font-black text-orange-700">+ Add Plan</button>
            </div>

            @php
                $oldPlans = old('plans');
                if ($oldPlans === null) {
                    $oldPlans = $eventMaster->exists
                        ? $eventMaster->plans->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                            'price' => $p->price,
                            'description' => $p->description,
                            'features_text' => implode("\n", $p->features ?? []),
                            'status' => $p->status,
                            'sort_order' => $p->sort_order,
                        ])->toArray()
                        : [['name' => '', 'price' => '', 'description' => '', 'features_text' => '', 'status' => 'active', 'sort_order' => 0]];
                }
            @endphp

            <div id="plans-wrap" class="mt-5 space-y-4">
                @foreach($oldPlans as $i => $plan)
                    <div class="plan-row rounded-3xl border border-orange-100 bg-orange-50/50 p-4">
                        <input type="hidden" data-field="id" name="plans[{{ $i }}][id]" value="{{ $plan['id'] ?? '' }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-black text-gray-800">Plan <span class="plan-number">{{ $i + 1 }}</span></p>
                            <button type="button" class="remove-plan rounded-xl bg-red-50 px-3 py-1.5 text-xs font-black text-red-700">Remove</button>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Name</label>
                                <input data-field="name" name="plans[{{ $i }}][name]" value="{{ $plan['name'] ?? '' }}" placeholder="Premium Plan" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Price</label>
                                <input data-field="price" type="number" step="0.01" min="0" name="plans[{{ $i }}][price]" value="{{ $plan['price'] ?? '' }}" placeholder="50000" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Description</label>
                                <textarea data-field="description" name="plans[{{ $i }}][description]" rows="3" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">{{ $plan['description'] ?? '' }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Features (one per line)</label>
                                <textarea data-field="features_text" name="plans[{{ $i }}][features_text]" rows="5" placeholder="Fresh flower stage decoration&#10;Welcome gate&#10;Car decoration" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">{{ $plan['features_text'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Status</label>
                                <select data-field="status" name="plans[{{ $i }}][status]" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">
                                    <option value="active" @selected(($plan['status'] ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(($plan['status'] ?? '') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-black uppercase text-gray-500">Sort Order</label>
                                <input data-field="sort_order" type="number" min="0" name="plans[{{ $i }}][sort_order]" value="{{ $plan['sort_order'] ?? $i }}" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-black text-gray-900">Photos & Videos</h2>
            <p class="mt-1 text-sm text-gray-500">Photos up to 5 MB each. Video files up to 50 MB each. You can also save external video URLs.</p>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-3xl border border-dashed border-orange-200 bg-orange-50 p-5">
                    <label class="block font-black text-gray-800">Gallery Photos</label>
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-3 block w-full text-sm">
                </div>
                <div class="rounded-3xl border border-dashed border-blue-200 bg-blue-50 p-5">
                    <label class="block font-black text-gray-800">Video Files</label>
                    <input type="file" name="videos[]" accept="video/mp4,video/webm,video/quicktime" multiple class="mt-3 block w-full text-sm">
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-2 block text-sm font-black text-gray-700">External Video URLs</label>
                <div id="video-url-wrap" class="space-y-2">
                    @foreach((array) old('video_urls', ['']) as $videoUrl)
                        <div class="video-url-row flex gap-2">
                            <input type="url" name="video_urls[]" value="{{ $videoUrl }}" placeholder="https://youtube.com/... or direct video URL" class="flex-1 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <button type="button" class="remove-video-url rounded-2xl border border-red-200 bg-red-50 px-4 font-black text-red-700">×</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-video-url" class="mt-3 rounded-xl bg-blue-50 px-4 py-2 text-sm font-black text-blue-700">+ Add Video URL</button>
            </div>

            @if($eventMaster->exists && $eventMaster->media->count())
                <div class="mt-6">
                    <h3 class="mb-3 font-black text-gray-900">Existing Media</h3>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach($eventMaster->media as $media)
                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50">
                                @if($media->media_type === 'photo')
                                    <img src="{{ $media->media_url }}" class="h-32 w-full object-cover" alt="Event photo">
                                @else
                                    @if($media->path)
                                        <video src="{{ $media->media_url }}" class="h-32 w-full object-cover" controls preload="metadata"></video>
                                    @else
                                        <div class="grid h-32 place-items-center px-3 text-center text-xs font-bold text-blue-700">External video URL saved</div>
                                    @endif
                                @endif
                                <div class="p-2 text-xs font-bold text-gray-600">{{ ucfirst($media->media_type) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-black text-gray-900">Cover Image</h2>
            @if($eventMaster->cover_image_url)
                <img src="{{ $eventMaster->cover_image_url }}" class="mt-4 h-56 w-full rounded-3xl object-cover" alt="Cover">
                <label class="mt-3 flex items-center gap-2 text-sm font-bold text-red-700">
                    <input type="checkbox" name="remove_cover" value="1"> Remove current cover
                </label>
            @endif
            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="mt-4 block w-full text-sm">
            <p class="mt-2 text-xs text-gray-500">Recommended: 1600 × 900 landscape.</p>
        </div>

        <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-black text-gray-900">Publishing</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Status *</label>
                    <select name="status" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <option value="active" @selected(old('status', $eventMaster->status ?: 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $eventMaster->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-black text-gray-700">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $eventMaster->sort_order ?? 0) }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                </div>
            </div>
        </div>

        @if($eventMaster->exists && $eventMaster->media->count())
            <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black text-gray-900">Delete Existing Media</h2>
                <p class="mt-1 text-sm text-gray-500">Use these buttons for individual files or links.</p>
                <div class="mt-4 space-y-2">
                    @foreach($eventMaster->media as $media)
                        <label class="flex cursor-pointer items-center justify-between rounded-2xl bg-gray-50 p-3">
                            <span class="truncate pr-2 text-xs font-bold text-gray-600">{{ ucfirst($media->media_type) }} #{{ $media->id }}</span>
                            <span class="flex items-center gap-2 text-xs font-black text-red-700">
                                <input type="checkbox" name="remove_media[]" value="{{ $media->id }}">
                                Remove on save
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    const plansWrap = document.getElementById('plans-wrap');
    const addPlan = document.getElementById('add-plan');

    function reindexPlans() {
        [...plansWrap.querySelectorAll('.plan-row')].forEach((row, index) => {
            row.querySelector('.plan-number').textContent = index + 1;
            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `plans[${index}][${input.dataset.field}]`;
            });
        });
    }

    addPlan?.addEventListener('click', function () {
        const index = plansWrap.querySelectorAll('.plan-row').length;
        const row = document.createElement('div');
        row.className = 'plan-row rounded-3xl border border-orange-100 bg-orange-50/50 p-4';
        row.innerHTML = `
            <input type="hidden" data-field="id" name="plans[${index}][id]" value="">
            <div class="flex items-center justify-between gap-3">
                <p class="font-black text-gray-800">Plan <span class="plan-number">${index + 1}</span></p>
                <button type="button" class="remove-plan rounded-xl bg-red-50 px-3 py-1.5 text-xs font-black text-red-700">Remove</button>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Name</label><input data-field="name" name="plans[${index}][name]" placeholder="Premium Plan" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Price</label><input data-field="price" type="number" step="0.01" min="0" name="plans[${index}][price]" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-black uppercase text-gray-500">Plan Description</label><textarea data-field="description" name="plans[${index}][description]" rows="3" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"></textarea></div>
                <div class="md:col-span-2"><label class="mb-1 block text-xs font-black uppercase text-gray-500">Features (one per line)</label><textarea data-field="features_text" name="plans[${index}][features_text]" rows="5" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"></textarea></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Status</label><select data-field="status" name="plans[${index}][status]" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div><label class="mb-1 block text-xs font-black uppercase text-gray-500">Sort Order</label><input data-field="sort_order" type="number" min="0" name="plans[${index}][sort_order]" value="${index}" class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3"></div>
            </div>`;
        plansWrap.appendChild(row);
    });

    plansWrap?.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-plan')) {
            event.target.closest('.plan-row')?.remove();
            reindexPlans();
        }
    });

    const videoWrap = document.getElementById('video-url-wrap');
    document.getElementById('add-video-url')?.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'video-url-row flex gap-2';
        row.innerHTML = '<input type="url" name="video_urls[]" placeholder="https://youtube.com/... or direct video URL" class="flex-1 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3"><button type="button" class="remove-video-url rounded-2xl border border-red-200 bg-red-50 px-4 font-black text-red-700">×</button>';
        videoWrap.appendChild(row);
    });

    videoWrap?.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-video-url')) {
            if (videoWrap.querySelectorAll('.video-url-row').length > 1) {
                event.target.closest('.video-url-row')?.remove();
            } else {
                event.target.closest('.video-url-row').querySelector('input').value = '';
            }
        }
    });
})();
</script>
