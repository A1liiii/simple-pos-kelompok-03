<nav class="bg-slate-900 text-white px-6 py-3 flex items-center gap-6">
    <span class="font-semibold text-lg">
        Simple POS
    </span>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('pos.create') }}"
            class="px-3 py-1.5 rounded
                {{ request()->routeIs('pos.*')
                    ? 'bg-white text-slate-900 font-semibold'
                    : 'hover:bg-slate-800' }}"
        >
            Kasir
        </a>

        <a
            href="{{ route('transactions.index') }}"
            class="px-3 py-1.5 rounded
                {{ request()->routeIs('transactions.*')
                    ? 'bg-white text-slate-900 font-semibold'
                    : 'hover:bg-slate-800' }}"
        >
            Transaksi
        </a>
    </div>
</nav>