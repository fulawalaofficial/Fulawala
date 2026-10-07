<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use Illuminate\Http\JsonResponse;

class FlowerProductController extends Controller
{
    public function index(): JsonResponse
    {
        $flowers = FlowerProduct::query()
            ->active()
            ->latest('id')
            ->get();

        // FlowerProduct::$appends adds image_url automatically while
        // keeping the existing image field for backward compatibility.
        return response()->json($flowers);
    }
}
