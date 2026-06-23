@extends('admin.layout')
@section('title','Admin Login')
@section('content')
<div class="max-w-md mx-auto mt-24 bg-white border border-orange-200 rounded-2xl p-8 shadow-sm">
    <h1 class="text-3xl font-black text-orange-700 mb-2">🌸 Admin Login</h1>
    <p class="text-gray-500 mb-6">Manage pooja packets, orders, subscriptions and events.</p>
    @if($errors->any())<div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">@csrf
        <input name="email" value="admin@example.com" placeholder="Email" class="w-full border rounded-lg px-4 py-3">
        <input name="password" value="admin123" type="password" placeholder="Password" class="w-full border rounded-lg px-4 py-3">
        <button class="w-full bg-orange-600 text-white rounded-lg py-3 font-bold">Login</button>
    </form>
</div>
@endsection
