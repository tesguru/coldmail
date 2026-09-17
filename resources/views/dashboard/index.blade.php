@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="mb-8">
  <div class="flex items-center gap-2 text-[11px] font-semibold text-[#7c6ef7] uppercase tracking-widest mb-2">
    <span class="w-1.5 h-1.5 rounded-full bg-[#7c6ef7] inline-block"></span>
    Live overview
  </div>
  <h1 class="text-3xl font-bold text-white tracking-tight">Dashboard</h1>
  <p class="text-zinc-500 text-sm mt-1.5">Live overview across all your Gmail accounts</p>
</div>

<!-- STAT CARDS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6 hover:border-white/[0.12] transition">
    <div class="flex items-center gap-2 text-sm text-zinc-500 mb-3">
      <span class="w-2 h-2 rounded-full bg-sky-400"></span> Sent today
    </div>
    <p class="text-4xl font-bold text-sky-400 tracking-tight">{{ $stats['total_sent_today'] }}</p>
  </div>
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6 hover:border-white/[0.12] transition">
    <div class="flex items-center gap-2 text-sm text-zinc-500 mb-3">
      <span class="w-2 h-2 rounded-full bg-white/30"></span> Total sent
    </div>
    <p class="text-4xl font-bold text-white tracking-tight">{{ $stats['total_sent_all'] }}</p>
  </div>
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6 hover:border-white/[0.12] transition">
    <div class="flex items-center gap-2 text-sm text-zinc-500 mb-3">
      <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Replies
    </div>
    <p class="text-4xl font-bold text-emerald-400 tracking-tight">{{ $stats['total_replies'] }}</p>
  </div>
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6 hover:border-white/[0.12] transition">
    <div class="flex items-center gap-2 text-sm text-zinc-500 mb-3">
      <span class="w-2 h-2 rounded-full bg-red-400"></span> Bounces
    </div>
    <p class="text-4xl font-bold text-red-400 tracking-tight">{{ $stats['total_bounces'] }}</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- ACCOUNTS -->
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="font-bold text-white">Gmail Accounts</h2>
      <a href="{{ route('accounts.index') }}" class="text-sm font-medium text-[#a78bfa] hover:text-[#c4b5fd] transition">Manage →</a>
    </div>

    @forelse($accountStats as $item)
      <div class="mb-6 last:mb-0">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-2 h-2 rounded-full {{ $item['account']->is_active ? 'bg-emerald-400' : 'bg-zinc-600' }}"></div>
            <span class="text-sm font-medium text-zinc-200 truncate">{{ $item['account']->email }}</span>
          </div>
          <div class="flex items-center gap-2.5 flex-shrink-0">
            <span class="text-xs text-zinc-500 font-semibold">{{ $item['sent_today'] }}/{{ $item['limit'] }}</span>
            @if($item['account']->is_active)
              <span class="text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full px-2.5 py-0.5">Active</span>
            @else
              <span class="text-[11px] font-semibold bg-white/5 text-zinc-500 border border-white/10 rounded-full px-2.5 py-0.5">Inactive</span>
            @endif
          </div>
        </div>
        <div class="bg-white/5 rounded-full h-2 overflow-hidden">
          <div class="bg-[#7c6ef7] h-2 rounded-full transition-all duration-500"
               style="width: {{ min(100, ($item['sent_today'] / max(1, $item['limit'])) * 100) }}%"></div>
        </div>
        <p class="text-xs text-zinc-600 mt-1.5 font-medium">{{ $item['remaining'] }} remaining · {{ $item['total_sent'] }} total sent</p>
      </div>
    @empty
      <div class="text-center py-10">
        <p class="text-sm text-zinc-500 mb-4">No accounts connected yet</p>
        <a href="{{ route('google.add-account') }}"
           class="inline-flex items-center px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
          + Add Gmail Account
        </a>
      </div>
    @endforelse
  </div>

  <!-- RECENT CAMPAIGNS -->
  <div class="bg-[#121218] rounded-2xl border border-white/[0.06] p-6">
    <div class="flex items-center justify-between mb-6">
      <h2 class="font-bold text-white">Recent Campaigns</h2>
      <a href="{{ route('campaigns.index') }}" class="text-sm font-medium text-[#a78bfa] hover:text-[#c4b5fd] transition">View all →</a>
    </div>

    @forelse($campaigns as $campaign)
      <div class="flex items-center justify-between py-3.5 border-b border-white/[0.05] last:border-0 hover:bg-white/[0.02] -mx-2 px-2 rounded-xl transition">
        <div class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-zinc-200 truncate">{{ $campaign->name }}</p>
          <p class="text-xs text-zinc-500 mt-1">
            {{ $campaign->domain }} · {{ $campaign->sent_count }}/{{ $campaign->total_emails }} sent
          </p>
        </div>
        <div class="flex items-center gap-2.5 ml-3 flex-shrink-0">
          @php
            $statusColors = [
              'active'    => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25',
              'paused'    => 'bg-amber-500/10 text-amber-400 border-amber-500/25',
              'completed' => 'bg-sky-500/10 text-sky-400 border-sky-500/25',
            ];
          @endphp
          <span class="text-[11px] font-semibold border rounded-full px-2.5 py-1 {{ $statusColors[$campaign->status] ?? 'bg-white/5 text-zinc-500 border-white/10' }}">
            {{ ucfirst($campaign->status) }}
          </span>
          <a href="{{ route('campaigns.show', $campaign->id) }}"
             class="text-xs font-medium text-[#a78bfa] hover:text-[#c4b5fd] transition">View →</a>
        </div>
      </div>
    @empty
      <div class="text-center py-10">
        <p class="text-sm text-zinc-500 mb-4">No campaigns yet</p>
        <a href="{{ route('campaigns.index') }}"
           class="inline-flex items-center px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
          Create Campaign
        </a>
      </div>
    @endforelse
  </div>

</div>

@endsection