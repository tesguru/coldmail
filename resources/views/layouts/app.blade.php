<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>ColdMail — @yield('title', 'Dashboard')</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Syne', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          },
        },
      },
    };
  </script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    body {
      font-family: 'Syne', ui-sans-serif, system-ui, sans-serif;
    }
    [x-cloak] { display: none !important; }

    .spinner {
      width: 18px; height: 18px;
      border: 2px solid rgba(255,255,255,0.08);
      border-top-color: #7c6ef7;
      border-radius: 50%;
      animation: spin 0.7s linear infinite;
      display: inline-block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    #toast-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .toast {
      padding: 12px 16px;
      border-radius: 14px;
      font-size: 14px;
      font-weight: 500;
      background: #131318;
      color: #e4e4e7;
      border: 1px solid rgba(255,255,255,0.08);
      box-shadow: 0 8px 32px rgba(0,0,0,0.5);
      animation: toastIn 0.25s ease;
      max-width: 340px;
    }
    .toast strong { display: block; margin-bottom: 2px; }
    .toast-success { border-color: rgba(52,211,153,0.3); }
    .toast-success strong { color: #34d399; }
    .toast-error { border-color: rgba(248,113,113,0.3); }
    .toast-error strong { color: #f87171; }
    .toast-info { border-color: rgba(124,110,247,0.3); }
    .toast-info strong { color: #a78bfa; }
    @keyframes toastIn {
      from { transform: translateX(100%); opacity: 0; }
      to   { transform: translateX(0);   opacity: 1; }
    }

    /* Dark scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
  </style>
</head>
<body class="bg-[#0a0a0f] min-h-screen text-zinc-200 antialiased">

  <!-- NAVBAR -->
  <nav class="bg-[#0d0d12]/90 backdrop-blur-md border-b border-white/[0.06] sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">

      <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
        <div class="w-8 h-8 rounded-xl bg-[#7c6ef7]/20 border border-[#7c6ef7]/30 flex items-center justify-center transition group-hover:bg-[#7c6ef7]/30">
          <svg class="w-4 h-4 text-[#7c6ef7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-linecap="round"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </div>
        <span class="font-bold text-lg text-white tracking-tight">ColdMail</span>
      </a>

      <div class="flex items-center gap-1">
        @php
          $navItems = [
            ['route' => 'dashboard',     'label' => 'Dashboard',     'match' => 'dashboard'],
            ['route' => 'accounts.index', 'label' => 'Accounts',     'match' => 'accounts.*'],
            ['route' => 'templates.index','label' => 'Templates',    'match' => 'templates.*'],
            ['route' => 'campaigns.index','label' => 'Campaigns',    'match' => 'campaigns.*'],
          ];
        @endphp
        @foreach($navItems as $item)
          <a href="{{ route($item['route']) }}"
             class="px-3.5 py-2 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs($item['match'])
                        ? 'bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25'
                        : 'text-zinc-500 hover:text-zinc-200 hover:bg-white/5 border border-transparent' }}">
            {{ $item['label'] }}
          </a>
        @endforeach
      </div>

      <!-- USER -->
      <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center gap-2.5 hover:bg-white/5 rounded-xl px-2.5 py-2 transition">
          @if(auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-xl border border-white/10">
          @else
            <div class="w-8 h-8 rounded-xl bg-[#7c6ef7]/20 border border-[#7c6ef7]/30 flex items-center justify-center text-[#a78bfa] font-bold text-xs">
              {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
          @endif
          <span class="text-sm font-medium text-zinc-300 hidden sm:block">{{ auth()->user()->name }}</span>
          <svg class="w-3.5 h-3.5 text-zinc-500 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <div x-show="open" @click.away="open = false" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-60 bg-[#131318] rounded-2xl shadow-2xl border border-white/[0.08] py-1.5 z-20 origin-top-right">
          <div class="px-4 py-3 border-b border-white/[0.06]">
            <p class="text-[11px] font-semibold text-zinc-600 uppercase tracking-wider">Signed in as</p>
            <p class="text-sm font-medium text-zinc-200 truncate mt-0.5">{{ auth()->user()->email }}</p>
          </div>
          <a href="{{ route('google.add-account') }}"
             class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-400 hover:bg-white/5 hover:text-zinc-200 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Add Gmail Account
          </a>
          <form method="POST" action="{{ route('google.logout') }}">
            @csrf
            <button class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition mt-0.5">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
              Sign out
            </button>
          </form>
        </div>
      </div>
    </div>
  </nav>

  <!-- PAGE -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    @yield('content')
  </main>

  <!-- TOASTS -->
  <div id="toast-container"></div>

  <!-- GLOBAL JS -->
  <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    function toast(title, message, type = 'info') {
      const el = document.createElement('div');
      el.className = `toast toast-${type}`;
      el.innerHTML = `<strong>${title}</strong>${message ? '<span class="text-zinc-400 text-[13px]">' + message + '</span>' : ''}`;
      document.getElementById('toast-container').appendChild(el);
      setTimeout(() => el.remove(), 4000);
    }

    async function apiGet(url) {
      const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
      return r.json();
    }

    async function apiPost(url, data) {
      const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data),
      });
      return r.json();
    }

    async function apiPut(url, data) {
      const r = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data),
      });
      return r.json();
    }

    async function apiDelete(url) {
      const r = await fetch(url, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      });
      return r.json();
    }
  </script>

  @yield('scripts')
</body>
</html>
