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
            ->get()
            ->map(function (PoojaPacket $packet): array {
                return $this->formatPacket($packet);
            })
            ->values();

        /*
         * Keep the response as a direct array so the existing
         * React Native mobile application does not break.
         */
        return response()->json($packets);
    }

    /**
     * Return one active pooja packet.
     */
    public function show(PoojaPacket $poojaPacket): JsonResponse
    {
        if (
            strcasecmp(
                trim((string) $poojaPacket->status),
                'Active'
            ) !== 0
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Pooja packet not found.',
            ], 404);
        }

        return response()->json(
            $this->formatPacket($poojaPacket)
        );
    }

    /**
     * Format packet API response.
     *
     * Database image:
     * uploads/pooja-packets/example.jpg
     *
     * API image:
     * https://fulawala.com/uploads/pooja-packets/example.jpg
     *
     * Or for Laravel public storage:
     * https://fulawala.com/storage/pooja-packets/example.jpg
     */
    private function formatPacket(PoojaPacket $packet): array
    {
        $packetData = $packet->toArray();

        /*
         * Get the original image path stored in the database.
         */
        $originalImagePath = $packet->getRawOriginal('image');

        /*
         * The PoojaPacket model image_url accessor generates
         * the complete public URL.
         */
        $fullImageUrl = $packet->image_url;

        /*
         * Keep original path separately for debugging or admin use.
         */
        $packetData['image_path'] = $originalImagePath;

        /*
         * Return the complete URL in both fields.
         */
        $packetData['image'] = $fullImageUrl;
        $packetData['image_url'] = $fullImageUrl;

        return $packetData;
    }
}