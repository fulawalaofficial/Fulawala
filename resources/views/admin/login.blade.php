@extends('admin.layout')

@section('title','Admin Login')

@section('content')
<div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-orange-50 via-white to-amber-100 flex items-center justify-center px-4 py-12">

    <!-- Background Decoration -->
    <div class="absolute top-[-120px] left-[-120px] w-80 h-80 bg-orange-300 rounded-full blur-3xl opacity-30"></div>
    <div class="absolute bottom-[-140px] right-[-120px] w-96 h-96 bg-amber-400 rounded-full blur-3xl opacity-25"></div>
    <div class="absolute top-1/3 right-1/4 w-40 h-40 bg-pink-300 rounded-full blur-3xl opacity-20"></div>

    <div class="relative w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 bg-white/80 backdrop-blur-xl border border-white/70 rounded-3xl shadow-2xl overflow-hidden">

        <!-- Left Branding Section -->
        <div class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-orange-600 via-orange-500 to-amber-500 text-white relative overflow-hidden">

            <div class="absolute inset-0 opacity-20">
                <div class="absolute top-10 left-10 w-24 h-24 border border-white rounded-full"></div>
                <div class="absolute bottom-20 right-10 w-36 h-36 border border-white rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 w-44 h-44 border border-white rounded-full"></div>
            </div>

            <div class="relative z-10">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-4xl shadow-lg mb-6">
                    🌸
                </div>

                <h2 class="text-4xl font-black leading-tight mb-4">
                    Fulawala Admin Panel
                </h2>

                <p class="text-orange-50 text-lg leading-relaxed">
                    Manage pooja packets, flower orders, subscriptions, quotations and event bookings from one premium dashboard.
                </p>
            </div>

            <div class="relative z-10 grid grid-cols-2 gap-4 mt-10">
                <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
                    <p class="text-3xl font-black">24/7</p>
                    <p class="text-sm text-orange-50">Order Tracking</p>
                </div>

                <div class="bg-white/15 backdrop-blur rounded-2xl p-4">
                    <p class="text-3xl font-black">100%</p>
                    <p class="text-sm text-orange-50">Secure Access</p>
                </div>
            </div>
        </div>

        <!-- Login Form Section -->
        <div class="p-6 sm:p-10 lg:p-12">

            <div class="mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 font-semibold text-sm mb-5">
                    <span>🔐</span>
                    <span>Admin Secure Login</span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-black text-gray-900 mb-3">
                    Welcome Back
                </h1>

                <p class="text-gray-500">
                    Login to continue managing your flower delivery business.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl">
                    <span class="text-lg">⚠️</span>
                    <p class="text-sm font-medium">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Email Address
                    </label>

                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            ✉️
                        </span>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', 'admin@example.com') }}"
                            placeholder="Enter admin email"
                            autocomplete="email"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-12 py-4 text-gray-800 font-medium outline-none transition focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                            required
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Password
                    </label>

                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            🔑
                        </span>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter admin password"
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-12 py-4 text-gray-800 font-medium outline-none transition focus:bg-white focus:border-orange-500 focus:ring-4 focus:ring-orange-100"
                            required
                        >
                    </div>
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    class="group w-full rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-6 py-4 text-white font-black text-lg shadow-lg shadow-orange-200 transition hover:scale-[1.01] hover:shadow-xl active:scale-[0.99]"
                >
                    Login to Dashboard
                    <span class="inline-block transition group-hover:translate-x-1">→</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <p class="text-center text-sm text-gray-500">
                    © {{ date('Y') }} Fulawala Admin. Secure management panel.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection