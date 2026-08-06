<a href="{{ route('admin.today-deliveries.index') }}"
   class="flex items-center gap-3 rounded-xl px-4 py-3 font-semibold transition
          {{ request()->routeIs('admin.today-deliveries.*')
              ? 'bg-orange-600 text-white shadow-lg shadow-orange-200'
              : 'text-slate-600 hover:bg-orange-50 hover:text-orange-700' }}">
    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-lg">🚚</span>
    <span>Today Delivery</span>
</a>
