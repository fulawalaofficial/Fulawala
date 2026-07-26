<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoojaPacket;
use Illuminate\Http\JsonResponse;

class PoojaPacketController extends Controller
{
    /**
     * Return all active pooja packets.
     */
    public function index(): JsonResponse
    {
        $packets = PoojaPacket::query()
            ->where('status', 'Active')
            ->latest()
            ->get();

        /*
         * Because image_url is included in the model $appends,
         * every packet will contain:
         *
         * image: "pooja-packets/photo.jpg"
         * image_url:
         * "https://fulawala.com/storage/pooja-packets/photo.jpg"
         */
        return response()->json($packets);
    }

    /**
     * Return one active pooja packet.
     */
    public function show(PoojaPacket $poojaPacket): JsonResponse
    {
        if (strcasecmp((string) $poojaPacket->status, 'Active') !== 0) {
            return response()->json([
                'status' => false,
                'message' => 'Pooja packet not found.',
            ], 404);
        }

        return response()->json($poojaPacket);
    }
}