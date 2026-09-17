@extends('layouts.app')

@section('content')

<div class="mb-8">
  <h1 class="text-3xl font-bold text-white tracking-tight">New Campaign</h1>
  <p class="text-zinc-500 text-sm mt-1.5">Set up your outreach campaign</p>
</div>

<div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-7 max-w-3xl" x-data="campaignForm()">

  <form method="POST" action="{{ route('campaigns.store') }}">
    @csrf

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Campaign name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
               placeholder="e.g. Nigeria Geo Domains May">
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Your name</label>
        <input type="text" name="your_name" value="{{ old('your_name') }}"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
               placeholder="e.g. Emeka">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Gmail account</label>
        <select name="gmail_account_id"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
          <option value="">— select account —</option>
          @foreach($accounts as $account)
            <option value="{{ $account->id }}" {{ old('gmail_account_id') == $account->id ? 'selected' : '' }}>
              {{ $account->name }} ({{ $account->sent_today }}/{{ $account->daily_limit }} today)
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Template</label>
        <select name="template_id" x-model="templateId"
                @change="checkTemplate"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
          <option value="">— select template —</option>
          @foreach($templates as $template)
            <option value="{{ $template->id }}"
                    data-has-price="{{ $template->has_price ? '1' : '0' }}"
                    {{ old('template_id') == $template->id ? 'selected' : '' }}>
              {{ $template->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Domain you're selling</label>
        <input type="text" name="domain" value="{{ old('domain') }}"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
               placeholder="e.g. LagosBusiness.com">
      </div>

      <!-- Price field — shows only if template has_price -->
      <div x-show="hasPrice" x-cloak>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">
          Price
          <span class="text-amber-400 text-xs ml-1">← required by template</span>
        </label>
        <input type="text" name="price" value="{{ old('price') }}"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
               placeholder="e.g. $5,000 or negotiable">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Follow up after (days)</label>
        <input type="number" name="follow_up_days" value="{{ old('follow_up_days', 3) }}" min="1"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Max follow ups</label>
        <input type="number" name="max_follow_ups" value="{{ old('max_follow_ups', 2) }}" min="0" max="5"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
      </div>
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Prospect emails — one per line</label>
      <p class="text-xs text-zinc-600 mb-2">First name and company are auto-extracted from each email address</p>
      <textarea name="prospects" rows="10"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition font-mono"
                placeholder="john@lagosrealty.com&#10;sarah@abujabusiness.com&#10;mike@kanotraders.com">{{ old('prospects') }}</textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit"
              class="px-5 py-2.5 bg-[#7c6ef7] text-white rounded-xl text-sm font-semibold hover:bg-[#8d80f9] transition">
        Create Campaign
      </button>
      <a href="{{ route('campaigns.index') }}"
         class="px-5 py-2.5 bg-white/5 text-zinc-300 rounded-xl text-sm font-medium border border-white/10 hover:bg-white/10 transition">
        Cancel
      </a>
    </div>
  </form>
</div>

<script>
function campaignForm() {
  return {
    templateId: '{{ old('template_id', '') }}',
    hasPrice: false,
    checkTemplate(e) {
      const opt = e.target.selectedOptions[0];
      this.hasPrice = opt && opt.dataset.hasPrice === '1';
    }
  }
}
</script>

@endsection