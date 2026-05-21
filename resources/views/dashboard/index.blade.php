@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
  <p class="text-gray-500 text-sm mt-1">Live overview across all your Gmail accounts</p>
</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-sm text-gray-500 mb-1">Sent today</p>
    <p class="text-3xl font-bold text-blue-600">{{ $stats['total_sent_today'] }}</p>
  </div>
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-sm text-gray-500 mb-1">Total sent</p>
    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_sent_all'] }}</p>
  </div>
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-sm text-gray-500 mb-1">Replies</p>
    <p class="text-3xl font-bold text-green-600">{{ $stats['total_replies'] }}</p>
  </div>
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-sm text-gray-500 mb-1">Bounces</p>
    <p class="text-3xl font-bold text-red-500">{{ $stats['total_bounces'] }}</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- ACCOUNTS -->
  <div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-semibold text-gray-900">Gmail Accounts</h2>
      <a href="{{ route('accounts.index') }}" class="text-sm text-blue-600 hover:underline">Manage</a>
    </div>

    @forelse($accountStats as $item)
      <div class="mb-5 last:mb-0">
        <div class="flex items-center justify-between mb-1.5">
          <div>
            <span class="text-sm font-medium text-gray-900">{{ $item['account']->email }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">{{ $item['sent_today'] }}/{{ $item['limit'] }}</span>
            @if($item['account']->is_active)
              <span class="text-xs bg-green-50 text-green-700 border border-green-200 rounded-full px-2 py-0.5 font-medium">Active</span>
            @else
              <span class="text-xs bg-gray-100 text-gray-500 border border-gray-200 rounded-full px-2 py-0.5 font-medium">Inactive</span>
            @endif
          </div>
        </div>
        <div class="bg-gray-100 rounded-full h-2">
          <div class="bg-blue-500 h-2 rounded-full transition-all"
               style="width: {{ min(100, ($item['sent_today'] / max(1, $item['limit'])) * 100) }}%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $item['remaining'] }} remaining · {{ $item['total_sent'] }} total sent</p>
      </div>
    @empty
      <div class="text-center py-6">
        <p class="text-sm text-gray-400 mb-3">No accounts connected yet</p>
        <a href="{{ route('google.add-account') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
          + Add Gmail Account
        </a>
      </div>
    @endforelse
  </div>

  <!-- RECENT CAMPAIGNS -->
  <div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-semibold text-gray-900">Recent Campaigns</h2>
      <a href="{{ route('campaigns.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
    </div>

    @forelse($campaigns as $campaign)
      <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-medium text-gray-900 truncate">{{ $campaign->name }}</p>
          <p class="text-xs text-gray-400 mt-0.5">
            {{ $campaign->domain }} · {{ $campaign->sent_count }}/{{ $campaign->total_emails }} sent
          </p>
        </div>
        <div class="flex items-center gap-2 ml-3">
          @php
            $statusColors = [
              'active'    => 'bg-green-50 text-green-700 border-green-200',
              'paused'    => 'bg-yellow-50 text-yellow-700 border-yellow-200',
              'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
            ];
          @endphp
          <span class="text-xs border rounded-full px-2 py-0.5 font-medium {{ $statusColors[$campaign->status] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
            {{ ucfirst($campaign->status) }}
          </span>
          <a href="{{ route('campaigns.show', $campaign->id) }}"
             class="text-xs text-blue-600 hover:underline">View</a>
        </div>
      </div>
    @empty
      <div class="text-center py-6">
        <p class="text-sm text-gray-400 mb-3">No campaigns yet</p>
        <a href="{{ route('campaigns.index') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
          Create Campaign
        </a>
      </div>
    @endforelse
  </div>

</div>

@endsection