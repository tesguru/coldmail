@extends('layouts.app')

@section('content')

<div class="mb-6">
  <h1 class="text-2xl font-bold">{{ isset($account) ? 'Edit Account' : 'Add Gmail Account' }}</h1>
</div>

<div class="bg-white border border-gray-200 rounded-xl p-6 max-w-2xl">
  <form method="POST"
        action="{{ isset($account) ? route('accounts.update', $account) : route('accounts.store') }}">
    @csrf
    @if(isset($account)) @method('PUT') @endif

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Account label</label>
      <input type="text" name="name" value="{{ old('name', $account->name ?? '') }}"
             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
             placeholder="e.g. emeka1@gmail.com">
    </div>

    @if(!isset($account))
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Gmail address</label>
      <input type="email" name="email" value="{{ old('email') }}"
             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
             placeholder="emeka1@gmail.com">
    </div>
    @endif

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Apps Script Web App URL</label>
      <input type="url" name="script_url" value="{{ old('script_url', $account->script_url ?? '') }}"
             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
             placeholder="https://script.google.com/macros/s/.../exec">
      <p class="text-xs text-gray-400 mt-1">Deploy sender.gs on this Gmail account and paste the web app URL here</p>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Daily sending limit</label>
      <input type="number" name="daily_limit" value="{{ old('daily_limit', $account->daily_limit ?? 100) }}"
             min="1" max="100"
             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
    </div>

    @if(isset($account))
    <div class="mb-4 flex items-center gap-2">
      <input type="checkbox" name="is_active" id="is_active" value="1"
             {{ $account->is_active ? 'checked' : '' }}>
      <label for="is_active" class="text-sm text-gray-700">Account is active</label>
    </div>
    @endif

    <div class="flex gap-3 mt-6">
      <button type="submit"
              class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:opacity-80">
        {{ isset($account) ? 'Update Account' : 'Add Account' }}
      </button>
      <a href="{{ route('accounts.index') }}"
         class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
        Cancel
      </a>
    </div>
  </form>
</div>

@endsection