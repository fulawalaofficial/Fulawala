@extends('admin.layout')
@section('title','Reports')
@section('content')
<h1 class="text-3xl font-black mb-6">Reports</h1>
<div class="grid md:grid-cols-3 gap-4">@foreach($report as $label => $value)<div class="bg-white border rounded-2xl p-5"><p class="text-gray-500">{{ ucwords(str_replace('_',' ',$label)) }}</p><p class="text-2xl font-black text-orange-700 mt-2">{{ is_numeric($value) && str_contains($label,'revenue') ? '₹'.number_format($value,2) : $value }}</p></div>@endforeach</div>
@endsection
