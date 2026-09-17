@extends('layouts.app')

@section('content')

<div class="mb-8">
  <h1 class="text-3xl font-bold text-white tracking-tight">{{ isset($template) ? 'Edit Template' : 'New Template' }}</h1>
  <p class="text-zinc-500 text-sm mt-1.5">
    Available variables:
    <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{first_name}</code>
    <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{company}</code>
    <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{domain}</code>
    <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{price}</code>
    <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{your_name}</code>
  </p>
</div>

<div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-7 max-w-3xl">
  <form method="POST"
        action="{{ isset($template) ? route('templates.update', $template) : route('templates.store') }}">
    @csrf
    @if(isset($template)) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4 mb-5">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Template name</label>
        <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
               placeholder="e.g. Domain Outreach v1">
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Category</label>
        <select name="category"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
          <option value="initial"   {{ old('category', $template->category ?? '') === 'initial'   ? 'selected' : '' }}>Initial email</option>
          <option value="follow_up" {{ old('category', $template->category ?? '') === 'follow_up' ? 'selected' : '' }}>Follow up</option>
        </select>
      </div>
    </div>

    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Subject line</label>
      <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
             placeholder="e.g. {domain} — perfect for {company}">
    </div>

    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Email body</label>
      <textarea name="body" rows="12"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition font-mono"
                placeholder="Hi {first_name},&#10;&#10;I noticed {company} doesn't own {domain}...">{{ old('body', $template->body ?? '') }}</textarea>
    </div>

    <div class="mb-6 flex items-center gap-3">
      <input type="checkbox" name="has_price" id="has_price" value="1"
             {{ old('has_price', $template->has_price ?? false) ? 'checked' : '' }}
             class="w-4 h-4 rounded accent-[#7c6ef7]">
      <label for="has_price" class="text-sm text-zinc-300">
        This template uses <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-[#a78bfa] font-mono text-xs">{price}</code> — prompt for price when creating campaign
      </label>
    </div>

    <div class="flex gap-3">
      <button type="submit"
              class="px-5 py-2.5 bg-[#7c6ef7] text-white rounded-xl text-sm font-semibold hover:bg-[#8d80f9] transition">
        {{ isset($template) ? 'Update Template' : 'Save Template' }}
      </button>
      <a href="{{ route('templates.index') }}"
         class="px-5 py-2.5 bg-white/5 text-zinc-300 rounded-xl text-sm font-medium border border-white/10 hover:bg-white/10 transition">
        Cancel
      </a>
    </div>
  </form>
</div>

@endsection