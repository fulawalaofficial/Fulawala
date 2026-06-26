<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', ''));
        $role = $request->get('role', '');
        $status = $request->get('status', '');

        $query = Staff::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $staff = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Staff::count(),
            'active' => Staff::where('status', 'Active')->count(),
            'inactive' => Staff::where('status', 'Inactive')->count(),
            'delivery_boy' => Staff::where('role', 'Delivery Boy')->count(),
            'decorator' => Staff::where('role', 'Decorator')->count(),
            'manager' => Staff::where('role', 'Manager')->count(),
        ];

        $roles = collect([
            'Delivery Boy',
            'Decorator',
            'Manager',
        ])->merge(
            Staff::whereNotNull('role')
                ->where('role', '!=', '')
                ->distinct()
                ->pluck('role')
        )->unique()->values();

        $statuses = collect([
            'Active',
            'Inactive',
        ])->merge(
            Staff::whereNotNull('status')
                ->where('status', '!=', '')
                ->distinct()
                ->pluck('status')
        )->unique()->values();

        $filters = [
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ];

        return view('admin.staff.index', compact(
            'staff',
            'stats',
            'roles',
            'statuses',
            'filters'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:staff,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:Delivery Boy,Decorator,Manager'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        Staff::create($data);

        return back()->with('success', 'Staff member added successfully.');
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('staff', 'email')->ignore($staff->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:Delivery Boy,Decorator,Manager'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $staff->update($data);

        return back()->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return back()->with('success', 'Staff member deleted successfully.');
    }
}