<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index() { return view('admin.settings.index', ['settings' => AppSetting::orderBy('setting_key')->get()]); }
    public function update(Request $request) {
        foreach ($request->except(['_token']) as $key => $value) {
            AppSetting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
        return back()->with('success','Settings updated.');
    }
}
