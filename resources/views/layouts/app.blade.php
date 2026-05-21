<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>ColdMail — @yield('title', 'Dashboard')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    [x-cloak] { display: none !important; }

    .spinner {
      width: 18px; height: 18px;
      border: 2px solid #e5e7eb;
      border-top-color: #3b82f6;
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
      border-radius: 10px;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      animation: toastIn 0.25s ease;
      max-width: 300px;
    }

    .toast-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .toast-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
    .toast-info    { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

    @keyframes toastIn {
      from { transform: translateX(100%); opacity: 0; }
      to   { transform: translateX(0);   opacity: 1; }
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900">

  <!-- NAVBAR -->
  <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">

      <a href="{{ route('dashboard') }}" class="font-bold text-lg text-blue-600">ColdMail</a>

      <div class="flex items-center gap-1">
        <a href="{{ route('dashboard') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
          Dashboard
        </a>
        <a href="{{ route('accounts.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('accounts.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
          Accounts
        </a>
        <a href="{{ route('templates.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('templates.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
          Templates
        </a>
        <a href="{{ route('campaigns.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('campaigns.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
          Campaigns
        </a>
      </div>

      <!-- USER -->
      <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="flex items-center gap-2 hover:bg-gray-100 rounded-lg px-2 py-1.5">
          @if(auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar }}" class="w-7 h-7 rounded-full">
          @else
            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-xs">
              {{ substr(auth()->user()->name, 0, 1) }}
            </div>
          @endif
          <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <div x-show="open" @click.away="open = false" x-cloak
             class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-20">
          <div class="px-4 py-2.5 border-b border-gray-100">
            <p class="text-xs text-gray-400">Signed in as</p>
            <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->email }}</p>
          </div>
          <a href="{{ route('google.add-account') }}"
             class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            + Add Gmail Account
          </a>
          <form method="POST" action="{{ route('google.logout') }}">
            @csrf
            <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
      el.innerHTML = `<strong>${title}</strong>${message ? ' — ' + message : ''}`;
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