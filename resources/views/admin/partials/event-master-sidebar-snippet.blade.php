{{-- Add this inside your admin sidebar navigation --}}
<a href="{{ route('admin.event-masters.index') }}"
   class="flex items-center gap-3 rounded-2xl px-4 py-3 font-bold transition {{ request()->routeIs('admin.event-masters.*') ? 'bg-orange-100 text-orange-700' : 'text-gray-700 hover:bg-orange-50' }}">
    <span class="text-xl">🎊</span>
    <span>Event Master</span>
</a>
