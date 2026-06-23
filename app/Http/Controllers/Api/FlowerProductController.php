<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;

class FlowerProductController extends Controller
{
    public function index()
    {
        return response()->json(FlowerProduct::where('status', 'Active')->latest()->get());
    }
}
