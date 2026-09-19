<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventMaster;
use App\Models\EventMedia;
use App\Models\EventPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventMasterController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $status = trim((string) $request->get('status', ''));

        $events = EventMaster::query()
            ->withCount(['photos', 'videos', 'plans'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn ($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => EventMaster::count(),
            'active' => EventMaster::where('status', 'active')->count(),
            'inactive' => EventMaster::where('status', 'inactive')->count(),
            'plans' => EventPlan::count(),
        ];

        return view('admin.event-masters.index', compact('events', 'stats', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.event-masters.create', [
            'eventMaster' => new EventMaster(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateEvent($request);

        DB::transaction(function () use ($request, $data): void {
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
            }

            $event = EventMaster::create($data);

            $this->saveMedia($request, $event);
            $this->syncPlans($request, $event);
        });

        return redirect()
            ->route('admin.event-masters.index')
            ->with('success', 'Event master created successfully.');
    }

    public function edit(EventMaster $eventMaster): View
    {
        $eventMaster->load(['media', 'plans']);

        return view('admin.event-masters.edit', compact('eventMaster'));
    }

    public function update(Request $request, EventMaster $eventMaster): RedirectResponse
    {
        $data = $this->validateEvent($request, $eventMaster->id);

        DB::transaction(function () use ($request, $data, $eventMaster): void {
            if ($request->boolean('remove_cover') && $eventMaster->cover_image) {
                Storage::disk('public')->delete($eventMaster->cover_image);
                $data['cover_image'] = null;
            }

            if ($request->hasFile('cover_image')) {
                if ($eventMaster->cover_image) {
                    Storage::disk('public')->delete($eventMaster->cover_image);
                }

                $data['cover_image'] = $request->file('cover_image')->store('events/covers', 'public');
            }

            $eventMaster->update($data);

            foreach ((array) $request->input('remove_media', []) as $mediaId) {
                $media = $eventMaster->media()->whereKey((int) $mediaId)->first();

                if (!$media) {
                    continue;
                }

                if ($media->path) {
                    Storage::disk('public')->delete($media->path);
                }

                $media->delete();
            }

            $this->saveMedia($request, $eventMaster);
            $this->syncPlans($request, $eventMaster);
        });

        return redirect()
            ->route('admin.event-masters.edit', $eventMaster)
            ->with('success', 'Event master updated successfully.');
    }

    public function destroy(EventMaster $eventMaster): RedirectResponse
    {
        $eventMaster->load('media');

        DB::transaction(function () use ($eventMaster): void {
            if ($eventMaster->cover_image) {
                Storage::disk('public')->delete($eventMaster->cover_image);
            }

            foreach ($eventMaster->media as $media) {
                if ($media->path) {
                    Storage::disk('public')->delete($media->path);
                }
            }

            $eventMaster->delete();
        });

        return redirect()
            ->route('admin.event-masters.index')
            ->with('success', 'Event master deleted successfully.');
    }

    public function destroyMedia(EventMaster $eventMaster, EventMedia $media): RedirectResponse
    {
        abort_unless($media->event_master_id === $eventMaster->id, 404);

        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return back()->with('success', 'Event media removed successfully.');
    }

    private function validateEvent(Request $request, ?int $eventId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('event_masters', 'slug')->ignore($eventId),
            ],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'starting_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'videos' => ['nullable', 'array', 'max:5'],
            'videos.*' => ['file', 'mimes:mp4,mov,webm,m4v', 'max:51200'],
            'video_urls' => ['nullable', 'array', 'max:10'],
            'video_urls.*' => ['nullable', 'url', 'max:1000'],
            'remove_media' => ['nullable', 'array'],
            'remove_media.*' => ['integer'],
            'plans' => ['nullable', 'array', 'max:20'],
            'plans.*.id' => ['nullable', 'integer'],
            'plans.*.name' => ['nullable', 'string', 'max:120'],
            'plans.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'plans.*.description' => ['nullable', 'string', 'max:3000'],
            'plans.*.features_text' => ['nullable', 'string', 'max:5000'],
            'plans.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
            'plans.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    private function saveMedia(Request $request, EventMaster $event): void
    {
        $nextSort = ((int) $event->media()->max('sort_order')) + 1;

        foreach ($request->file('photos', []) as $photo) {
            $event->media()->create([
                'media_type' => 'photo',
                'path' => $photo->store('events/photos', 'public'),
                'sort_order' => $nextSort++,
            ]);
        }

        foreach ($request->file('videos', []) as $video) {
            $event->media()->create([
                'media_type' => 'video',
                'path' => $video->store('events/videos', 'public'),
                'sort_order' => $nextSort++,
            ]);
        }

        foreach ((array) $request->input('video_urls', []) as $url) {
            $url = trim((string) $url);

            if ($url === '') {
                continue;
            }

            $event->media()->create([
                'media_type' => 'video',
                'external_url' => $url,
                'sort_order' => $nextSort++,
            ]);
        }
    }

    private function syncPlans(Request $request, EventMaster $event): void
    {
        $submittedIds = [];

        foreach ((array) $request->input('plans', []) as $index => $planData) {
            $name = trim((string) ($planData['name'] ?? ''));

            // Empty plan rows are ignored.
            if ($name === '') {
                continue;
            }

            $features = collect(preg_split('/\r\n|\r|\n/', (string) ($planData['features_text'] ?? '')))
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();

            $payload = [
                'name' => $name,
                'price' => ($planData['price'] ?? '') !== '' ? $planData['price'] : null,
                'description' => $planData['description'] ?? null,
                'features' => $features,
                'status' => $planData['status'] ?? 'active',
                'sort_order' => $planData['sort_order'] ?? $index,
            ];

            $planId = isset($planData['id']) ? (int) $planData['id'] : 0;

            if ($planId > 0) {
                $plan = $event->plans()->whereKey($planId)->first();

                if ($plan) {
                    $plan->update($payload);
                    $submittedIds[] = $plan->id;
                    continue;
                }
            }

            $plan = $event->plans()->create($payload);
            $submittedIds[] = $plan->id;
        }

        if ($event->exists) {
            $query = $event->plans();
            if ($submittedIds) {
                $query->whereNotIn('id', $submittedIds);
            }
            $query->delete();
        }
    }
}
