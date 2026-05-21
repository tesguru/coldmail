@extends('layouts.app')

@section('content')

<div class="mb-6">
  <h1 class="text-2xl font-bold">New Campaign</h1>
  <p class="text-gray-500 text-sm mt-1">Set up your outreach campaign</p>
</div>

<div class="bg-white border border-gray-200 rounded-xl p-6 max-w-3xl" x-data="campaignForm()">

  <form method="POST" action="{{ route('campaigns.store') }}">
    @csrf

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Campaign name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
               placeholder="e.g. Nigeria Geo Domains May">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Your name</label>
        <input type="text" name="your_name" value="{{ old('your_name') }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
               placeholder="e.g. Emeka">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gmail account</label>
        <select name="gmail_account_id"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
          <option value="">— select account —</option>
          @foreach($accounts as $account)
            <option value="{{ $account->id }}" {{ old('gmail_account_id') == $account->id ? 'selected' : '' }}>
              {{ $account->name }} ({{ $account->sent_today }}/{{ $account->daily_limit }} today)
            </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
        <select name="template_id" x-model="templateId"
                @change="checkTemplate"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
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
        <label class="block text-sm font-medium text-gray-700 mb-1">Domain you're selling</label>
        <input type="text" name="domain" value="{{ old('domain') }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
               placeholder="e.g. LagosBusiness.com">
      </div>

      <!-- Price field — shows only if template has_price -->
      <div x-show="hasPrice" x-cloak>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Price
          <span class="text-yellow-600 text-xs ml-1">← required by template</span>
        </label>
        <input type="text" name="price" value="{{ old('price') }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
               placeholder="e.g. $5,000 or negotiable">
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Follow up after (days)</label>
        <input type="number" name="follow_up_days" value="{{ old('follow_up_days', 3) }}" min="1"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Max follow ups</label>
        <input type="number" name="max_follow_ups" value="{{ old('max_follow_ups', 2) }}" min="0" max="5"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
      </div>
    </div>

    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-1">Prospect emails — one per line</label>
      <p class="text-xs text-gray-400 mb-2">First name and company are auto-extracted from each email address</p>
      <textarea name="prospects" rows="10"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400 font-mono"
                placeholder="john@lagosrealty.com&#10;sarah@abujabusiness.com&#10;mike@kanotraders.com">{{ old('prospects') }}</textarea>
    </div>

    <div class="flex gap-3">
      <button type="submit"
              class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:opacity-80">
        Create Campaign
      </button>
      <a href="{{ route('campaigns.index') }}"
         class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
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