@extends('layouts.app')

@section('content')

<div class="mb-8">
  <h1 class="text-3xl font-bold text-white tracking-tight">{{ isset($account) ? 'Edit Account' : 'Add Gmail Account' }}</h1>
</div>

<div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-7 max-w-2xl">
  <form method="POST"
        action="{{ isset($account) ? route('accounts.update', $account) : route('accounts.store') }}">
    @csrf
    @if(isset($account)) @method('PUT') @endif

    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Account label</label>
      <input type="text" name="name" value="{{ old('name', $account->name ?? '') }}"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
             placeholder="e.g. emeka1@gmail.com">
    </div>

    @if(!isset($account))
    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Gmail address</label>
      <input type="email" name="email" value="{{ old('email') }}"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
             placeholder="emeka1@gmail.com">
    </div>
    @endif

    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Apps Script Web App URL</label>
      <input type="url" name="script_url" value="{{ old('script_url', $account->script_url ?? '') }}"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
             placeholder="https://script.google.com/macros/s/.../exec">
      <p class="text-xs text-zinc-600 mt-1.5">Deploy sender.gs on this Gmail account and paste the web app URL here</p>
    </div>

    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Daily sending limit</label>
      <input type="number" name="daily_limit" value="{{ old('daily_limit', $account->daily_limit ?? 100) }}"
             min="1" max="100"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
    </div>

    @if(isset($account))
    <div class="mb-5 flex items-center gap-3">
      <input type="checkbox" name="is_active" id="is_active" value="1"
             {{ $account->is_active ? 'checked' : '' }}
             class="w-4 h-4 rounded accent-[#7c6ef7]">
      <label for="is_active" class="text-sm text-zinc-300">Account is active</label>
    </div>
    @endif

    <div class="flex gap-3 mt-7">
      <button type="submit"
              class="px-5 py-2.5 bg-[#7c6ef7] text-white rounded-xl text-sm font-semibold hover:bg-[#8d80f9] transition">
        {{ isset($account) ? 'Update Account' : 'Add Account' }}
      </button>
      <a href="{{ route('accounts.index') }}"
         class="px-5 py-2.5 bg-white/5 text-zinc-300 rounded-xl text-sm font-medium border border-white/10 hover:bg-white/10 transition">
        Cancel
      </a>
    </div>
  </form>
</div>

@endsection