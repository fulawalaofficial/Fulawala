<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventMasterController extends Controller
{
    /**
     * Active Event Master list for the mobile app.
     * Includes photos, videos and active plans so the user can review
     * everything before selecting an event.
     */
    public function index(Request $request): JsonResponse
    {
        $events = EventMaster::query()
            ->active()
            ->with([
                'photos',
                'videos',
                'activePlans',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (EventMaster $event) => $this->transformEvent($event))
            ->values();

        return response()->json([
            'data' => $events,
        ]);
    }

    /**
     * Single Event Master detail.
     */
    public function show(Request $request, EventMaster $eventMaster): JsonResponse
    {
        abort_unless($eventMaster->status === 'active', 404);

        $eventMaster->load([
            'photos',
            'videos',
            'activePlans',
        ]);

        return response()->json([
            'data' => $this->transformEvent($eventMaster),
        ]);
    }

    private function transformEvent(EventMaster $event): array
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'slug' => $event->slug,
            'short_description' => $event->short_description,
            'description' => $event->description,
            'cover_image_url' => $event->cover_image_url,
            'starting_price' => $event->starting_price !== null
                ? (float) $event->starting_price
                : null,
            'photos' => $event->photos->map(fn ($media) => [
                'id' => $media->id,
                'title' => $media->title,
                'url' => $media->media_url,
            ])->values(),
            'videos' => $event->videos->map(fn ($media) => [
                'id' => $media->id,
                'title' => $media->title,
                'url' => $media->media_url,
                'external' => filled($media->external_url),
            ])->values(),
            'plans' => $event->activePlans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price !== null
                    ? (float) $plan->price
                    : null,
                'description' => $plan->description,
                'features' => $plan->features ?? [],
                'sort_order' => $plan->sort_order,
            ])->values(),
        ];
    }
}
