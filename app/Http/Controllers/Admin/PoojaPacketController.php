<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;

class PoojaPacketController extends Controller
{
    public function index() { return view('admin.pooja-packets.index', ['packets' => PoojaPacket::latest()->paginate(20)]); }
    public function create() { return view('admin.pooja-packets.form', ['packet' => new PoojaPacket()]); }
    public function store(Request $request) { PoojaPacket::create($this->validated($request)); return redirect()->route('admin.pooja-packets.index')->with('success','Packet added.'); }
    public function edit(PoojaPacket $poojaPacket) { return view('admin.pooja-packets.form', ['packet' => $poojaPacket]); }
    public function update(Request $request, PoojaPacket $poojaPacket) { $poojaPacket->update($this->validated($request)); return redirect()->route('admin.pooja-packets.index')->with('success','Packet updated.'); }
    public function destroy(PoojaPacket $poojaPacket) { $poojaPacket->delete(); return back()->with('success','Packet deleted.'); }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'packet_name' => ['required','string','max:255'],
            'image' => ['nullable','string'],
            'description' => ['nullable','string'],
            'included_flowers' => ['required','string'],
            'monthly_price' => ['required','numeric'],
            'weekly_price' => ['nullable','numeric'],
            'daily_quantity' => ['nullable','string'],
            'package_type' => ['nullable','string'],
            'status' => ['required','in:Active,Inactive'],
        ]);
        $data['included_flowers'] = array_values(array_filter(array_map('trim', explode(',', $data['included_flowers']))));
        return $data;
    }
}
