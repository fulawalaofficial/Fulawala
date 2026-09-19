<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventMaster;
use Illuminate\Http\JsonResponse;

class EventMasterController extends Controller
{
    public function index(): JsonResponse
    {
        $events = EventMaster::query()
            ->active()
            ->with([
                'photos:id,event_master_id,media_type,path,external_url,title,sort_order',
                'videos:id,event_master_id,media_type,path,external_url,title,sort_order',
                'activePlans:id,event_master_id,name,price,description,features,status,sort_order',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $events,
        ]);
    }

    public function show(EventMaster $eventMaster): JsonResponse
    {
        abort_unless($eventMaster->status === 'active', 404);

        $eventMaster->load([
            'photos',
            'videos',
            'activePlans',
        ]);

        return response()->json([
            'data' => $eventMaster,
        ]);
    }
}
