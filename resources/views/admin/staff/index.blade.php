@extends('admin.layout')
@section('title','Staff')
@section('content')
<h1 class="text-3xl font-black mb-6">Staff Management</h1>
<form method="POST" action="{{ route('admin.staff.store') }}" class="bg-white border rounded-2xl p-5 grid md:grid-cols-3 gap-4 mb-6">@csrf
    <input name="name" placeholder="Name" class="border rounded-lg px-4 py-3"><input name="mobile" placeholder="Mobile" class="border rounded-lg px-4 py-3"><input name="email" placeholder="Email" class="border rounded-lg px-4 py-3"><input name="password" placeholder="Password" class="border rounded-lg px-4 py-3"><select name="role" class="border rounded-lg px-4 py-3"><option>Delivery Boy</option><option>Decorator</option><option>Manager</option></select><select name="status" class="border rounded-lg px-4 py-3"><option>Active</option><option>Inactive</option></select><button class="bg-orange-600 text-white rounded-lg py-3 font-bold md:col-span-3">Add Staff</button>
</form>
<div class="bg-white border rounded-2xl overflow-hidden"><table class="w-full text-sm"><thead class="bg-orange-100"><tr><th class="p-3 text-left">Name</th><th class="p-3">Mobile</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Status</th></tr></thead><tbody>@foreach($staff as $s)<tr class="border-t"><td class="p-3 font-bold">{{ $s->name }}</td><td class="p-3 text-center">{{ $s->mobile }}</td><td class="p-3 text-center">{{ $s->email }}</td><td class="p-3 text-center">{{ $s->role }}</td><td class="p-3 text-center">{{ $s->status }}</td></tr>@endforeach</tbody></table></div><div class="mt-4">{{ $staff->links() }}</div>
@endsection
