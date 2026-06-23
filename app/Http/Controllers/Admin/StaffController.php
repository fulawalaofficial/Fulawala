<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index() { return view('admin.staff.index', ['staff' => Staff::latest()->paginate(30)]); }
    public function store(Request $request) {
        Staff::create($request->validate([
            'name' => ['required','string'], 'mobile' => ['nullable','string'], 'email' => ['required','email','unique:staff,email'],
            'password' => ['required','min:6'], 'role' => ['required','in:Delivery Boy,Decorator,Manager'], 'status' => ['required','in:Active,Inactive'],
        ]));
        return back()->with('success','Staff added.');
    }
}
