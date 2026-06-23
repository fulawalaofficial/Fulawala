@extends('admin.layout')
@section('title','Settings')
@section('content')
<h1 class="text-3xl font-black mb-6">Settings</h1>
<form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white border rounded-2xl p-6 space-y-4 max-w-2xl">@csrf
@php $map = $settings->pluck('setting_value','setting_key'); @endphp
@foreach(['default_morning_delivery_time','delivery_charge','minimum_order_amount','company_name','support_number'] as $key)
    <label class="block"><span class="font-bold">{{ ucwords(str_replace('_',' ',$key)) }}</span><input name="{{ $key }}" value="{{ $map[$key] ?? '' }}" class="w-full border rounded-lg px-4 py-3 mt-1"></label>
@endforeach
<button class="bg-orange-600 text-white px-5 py-3 rounded-lg font-bold">Save Settings</button>
</form>
@endsection
