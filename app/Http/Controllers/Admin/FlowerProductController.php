<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowerProduct;
use Illuminate\Http\Request;

class FlowerProductController extends Controller
{
    public function index() { return view('admin.flowers.index', ['flowers' => FlowerProduct::latest()->paginate(20)]); }
    public function create() { return view('admin.flowers.form', ['flower' => new FlowerProduct()]); }
    public function store(Request $request) { FlowerProduct::create($this->validated($request)); return redirect()->route('admin.flowers.index')->with('success','Flower added.'); }
    public function edit(FlowerProduct $flower) { return view('admin.flowers.form', compact('flower')); }
    public function update(Request $request, FlowerProduct $flower) { $flower->update($this->validated($request)); return redirect()->route('admin.flowers.index')->with('success','Flower updated.'); }
    public function destroy(FlowerProduct $flower) { $flower->delete(); return back()->with('success','Flower deleted.'); }
    private function validated(Request $request): array {
        return $request->validate([
            'flower_name' => ['required','string','max:255'],
            'image' => ['nullable','string'],
            'category' => ['nullable','string'],
            'price' => ['required','numeric'],
            'unit' => ['required','string'],
            'stock_status' => ['required','string'],
            'description' => ['nullable','string'],
            'status' => ['required','in:Active,Inactive'],
        ]);
    }
}
