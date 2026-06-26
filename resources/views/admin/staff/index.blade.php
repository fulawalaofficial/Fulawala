@extends('admin.layout')

@section('title', 'Staff')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-orange-600 via-amber-500 to-yellow-400 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -right-20 -top-20 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 text-sm font-black backdrop-blur">
                    👥 Fulawala Team Center
                </div>

                <h1 class="mt-4 text-3xl md:text-4xl font-black tracking-tight">
                    Staff Management
                </h1>

                <p class="mt-2 max-w-2xl text-white/90">
                    Add staff, manage delivery boys, decorators and managers, update roles, check contact details and control active/inactive status.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-center">
                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Total Staff</p>
                </div>

                <div class="rounded-2xl bg-white/20 px-5 py-4 backdrop-blur">
                    <p class="text-2xl font-black">{{ $stats['active'] ?? 0 }}</p>
                    <p class="text-xs font-bold uppercase text-white/80">Active Staff</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash / Validation --}}
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 font-semibold text-green-800 shadow-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-800 shadow-sm">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-800 shadow-sm">
            <p class="font-black">Please fix these errors:</p>
            <ul class="mt-2 list-disc pl-5 text-sm font-semibold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-6">
        <div class="rounded-3xl border border-orange-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Total</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['total'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-orange-100 text-2xl">👥</div>
            </div>
        </div>

        <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Active</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['active'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-green-100 text-2xl">✅</div>
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Inactive</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['inactive'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-red-100 text-2xl">⛔</div>
            </div>
        </div>

        <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Delivery Boys</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['delivery_boy'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-2xl">🚚</div>
            </div>
        </div>

        <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Decorators</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['decorator'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-2xl">🎨</div>
            </div>
        </div>

        <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-black text-gray-500">Managers</p>
            <div class="mt-3 flex items-center justify-between">
                <h3 class="text-3xl font-black text-gray-900">{{ $stats['manager'] ?? 0 }}</h3>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-yellow-100 text-2xl">👑</div>
            </div>
        </div>
    </div>

    {{-- Add Staff --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <div class="mb-5">
            <h2 class="text-2xl font-black text-gray-900">Add New Staff</h2>
            <p class="text-sm font-semibold text-gray-500">Create staff account for delivery, decoration and management work.</p>
        </div>

        <form method="POST" action="{{ route('admin.staff.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Staff Name</label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Enter full name"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Mobile Number</label>
                <input
                    type="text"
                    name="mobile"
                    value="{{ old('mobile') }}"
                    placeholder="Enter mobile number"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter email address"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Role</label>
                <select
                    name="role"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="Delivery Boy" @selected(old('role') == 'Delivery Boy')>Delivery Boy</option>
                    <option value="Decorator" @selected(old('role') == 'Decorator')>Decorator</option>
                    <option value="Manager" @selected(old('role') == 'Manager')>Manager</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                <select
                    name="status"
                    required
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="Active" @selected(old('status') == 'Active')>Active</option>
                    <option value="Inactive" @selected(old('status') == 'Inactive')>Inactive</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-gradient-to-r from-orange-600 to-amber-500 px-8 py-4 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:scale-[1.01]"
                >
                    Add Staff Member
                </button>
            </div>
        </form>
    </div>

    {{-- Filters --}}
    <div class="rounded-[2rem] border border-orange-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.staff.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-black text-gray-700">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search name, mobile, email..."
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Role</label>
                <select
                    name="role"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(($filters['role'] ?? '') == $role)>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                <select
                    name="status"
                    class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                >
                    <option value="">All Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') == $status)>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-3">
                <button
                    type="submit"
                    class="w-full rounded-2xl bg-gray-900 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                >
                    Filter
                </button>

                <a
                    href="{{ route('admin.staff.index') }}"
                    class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-center text-sm font-black text-gray-700 transition hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Staff Cards --}}
    <div class="space-y-4">
        @forelse($staff as $s)
            @php
                $statusText = $s->status ?: 'Inactive';
                $roleText = $s->role ?: '-';

                $statusClass = $statusText === 'Active'
                    ? 'bg-green-100 text-green-700 border-green-200'
                    : 'bg-red-100 text-red-700 border-red-200';

                $roleClass = match($roleText) {
                    'Delivery Boy' => 'bg-blue-100 text-blue-700 border-blue-200',
                    'Decorator' => 'bg-purple-100 text-purple-700 border-purple-200',
                    'Manager' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    default => 'bg-gray-100 text-gray-700 border-gray-200',
                };

                $avatar = strtoupper(substr($s->name ?? 'S', 0, 1));
            @endphp

            <details class="group overflow-hidden rounded-[2rem] border border-orange-100 bg-white shadow-sm transition hover:shadow-xl hover:shadow-orange-100">
                <summary class="cursor-pointer list-none p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-12 md:items-center">

                        <div class="md:col-span-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Staff</p>
                            <div class="mt-1 flex items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-400 text-lg font-black text-white">
                                    {{ $avatar }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900">{{ $s->name }}</p>
                                    <p class="text-xs font-semibold text-gray-500">Staff ID #{{ $s->id }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Mobile</p>
                            <p class="mt-1 font-black text-gray-900">{{ $s->mobile ?: '-' }}</p>
                        </div>

                        <div class="md:col-span-3">
                            <p class="text-xs font-black uppercase tracking-wide text-gray-400">Email</p>
                            <p class="mt-1 font-black text-gray-900 break-all">{{ $s->email }}</p>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $roleClass }}">
                                {{ $roleText }}
                            </span>
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>

                        <div class="md:col-span-2 text-right">
                            <span class="inline-flex rounded-2xl bg-gray-100 px-4 py-2 text-xs font-black text-gray-700 transition group-open:bg-orange-100 group-open:text-orange-700">
                                Manage
                            </span>
                        </div>
                    </div>
                </summary>

                <div class="border-t border-orange-100 bg-gradient-to-br from-orange-50/80 to-white p-5 md:p-6">
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                        {{-- Staff Details --}}
                        <div class="rounded-3xl border border-orange-100 bg-white p-5">
                            <h3 class="mb-4 text-lg font-black text-gray-900">Staff Details</h3>

                            <div class="space-y-4 text-sm">
                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase text-orange-600">Name</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $s->name }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Mobile</p>
                                    <p class="mt-1 font-black text-gray-900">{{ $s->mobile ?: '-' }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Email</p>
                                    <p class="mt-1 break-all font-black text-gray-900">{{ $s->email }}</p>
                                </div>

                                <div class="rounded-2xl bg-gray-50 p-4">
                                    <p class="text-xs font-black uppercase text-gray-500">Created</p>
                                    <p class="mt-1 font-black text-gray-900">
                                        {{ $s->created_at ? $s->created_at->format('d M Y, h:i A') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Edit Staff --}}
                        <div class="lg:col-span-2 rounded-3xl border border-orange-100 bg-white p-5">
                            <h3 class="mb-4 text-lg font-black text-gray-900">Edit Staff Member</h3>

                            <form method="POST" action="{{ route('admin.staff.update', $s) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name', $s->name) }}"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Mobile</label>
                                    <input
                                        type="text"
                                        name="mobile"
                                        value="{{ old('mobile', $s->mobile) }}"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $s->email) }}"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">New Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="Leave empty to keep old password"
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Role</label>
                                    <select
                                        name="role"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="Delivery Boy" @selected($s->role == 'Delivery Boy')>Delivery Boy</option>
                                        <option value="Decorator" @selected($s->role == 'Decorator')>Decorator</option>
                                        <option value="Manager" @selected($s->role == 'Manager')>Manager</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-gray-700">Status</label>
                                    <select
                                        name="status"
                                        required
                                        class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                                    >
                                        <option value="Active" @selected($s->status == 'Active')>Active</option>
                                        <option value="Inactive" @selected($s->status == 'Inactive')>Inactive</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    <button
                                        type="submit"
                                        class="rounded-2xl bg-gray-900 px-6 py-3 text-sm font-black text-white shadow-lg transition hover:bg-orange-600"
                                    >
                                        Update Staff
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('admin.staff.destroy', $s) }}" class="mt-4" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="rounded-2xl border border-red-200 bg-red-50 px-6 py-3 text-sm font-black text-red-700 transition hover:bg-red-100"
                                >
                                    Delete Staff
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </details>
        @empty
            <div class="rounded-[2rem] border border-dashed border-orange-200 bg-white p-12 text-center">
                <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-orange-100 text-4xl">
                    👥
                </div>
                <h3 class="mt-5 text-2xl font-black text-gray-900">No staff found</h3>
                <p class="mt-2 text-gray-500">Add your first staff member from the form above.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="rounded-3xl border border-orange-100 bg-white p-4 shadow-sm">
        {{ $staff->links() }}
    </div>
</div>
@endsection