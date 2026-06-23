<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoojaPacket;

class PoojaPacketController extends Controller
{
    public function index()
    {
        return response()->json(PoojaPacket::where('status', 'Active')->latest()->get());
    }

    public function show(PoojaPacket $poojaPacket)
    {
        abort_if($poojaPacket->status !== 'Active', 404);
        return response()->json($poojaPacket);
    }
}
