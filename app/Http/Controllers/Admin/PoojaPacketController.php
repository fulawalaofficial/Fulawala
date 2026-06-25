<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PoojaPacket;
use Illuminate\Http\Request;

class PoojaPacketController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status', '');

        $query = PoojaPacket::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('packet_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('included_flowers', 'like', "%{$search}%")
                    ->orWhere('package_type', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['Active', 'Inactive'])) {
            $query->where('status', $status);
        }

        $packets = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => PoojaPacket::count(),
            'active' => PoojaPacket::where('status', 'Active')->count(),
            'inactive' => PoojaPacket::where('status', 'Inactive')->count(),
            'average_price' => PoojaPacket::avg('monthly_price') ?? 0,
        ];

        return view('admin.pooja-packets.index', compact('packets', 'stats', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.pooja-packets.form', [
            'packet' => new PoojaPacket()
        ]);
    }

    public function store(Request $request)
    {
        PoojaPacket::create($this->validated($request));

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet added successfully.');
    }

    public function edit(PoojaPacket $poojaPacket)
    {
        return view('admin.pooja-packets.form', [
            'packet' => $pojaPacket ?? $poojaPacket
        ]);
    }

    public function update(Request $request, PoojaPacket $poojaPacket)
    {
        $poojaPacket->update($this->validated($request));

        return redirect()
            ->route('admin.pooja-packets.index')
            ->with('success', 'Pooja packet updated successfully.');
    }

    public function destroy(PoojaPacket $poojaPacket)
    {
        $poojaPacket->delete();

        return back()->with('success', 'Pooja packet deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'packet_name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'included_flowers' => ['required', 'string'],
            'monthly_price' => ['required', 'numeric'],
            'weekly_price' => ['nullable', 'numeric'],
            'daily_quantity' => ['nullable', 'string'],
            'package_type' => ['nullable', 'string'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $data['included_flowers'] = array_values(
            array_filter(
                array_map('trim', explode(',', $data['included_flowers']))
            )
        );

        return $data;
    }
}