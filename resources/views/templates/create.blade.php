@extends('layouts.app')

@section('content')

<div class="mb-6">
  <h1 class="text-2xl font-bold">{{ isset($template) ? 'Edit Template' : 'New Template' }}</h1>
  <p class="text-gray-500 text-sm mt-1">
    Available variables:
    <code class="bg-gray-100 px-1 rounded">{first_name}</code>
    <code class="bg-gray-100 px-1 rounded">{company}</code>
    <code class="bg-gray-100 px-1 rounded">{domain}</code>
    <code class="bg-gray-100 px-1 rounded">{price}</code>
    <code class="bg-gray-100 px-1 rounded">{your_name}</code>
  </p>
</div>

<div class="bg-white border border-gray-200 rounded-xl p-6 max-w-3xl">
  <form method="POST"
        action="{{ isset($template) ? route('templates.update', $template) : route('templates.store') }}">
    @csrf
    @if(isset($template)) @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Template name</label>
        <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
               placeholder="e.g. Domain Outreach v1">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="category"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400">
          <option value="initial"   {{ old('category', $template->category ?? '') === 'initial'   ? 'selected' : '' }}>Initial email</option>
          <option value="follow_up" {{ old('category', $template->category ?? '') === 'follow_up' ? 'selected' : '' }}>Follow up</option>
        </select>
      </div>
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Subject line</label>
      <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}"
             class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400"
             placeholder="e.g. {domain} — perfect for {company}">
    </div>

    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Email body</label>
      <textarea name="body" rows="12"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-gray-400 font-mono"
                placeholder="Hi {first_name},&#10;&#10;I noticed {company} doesn't own {domain}...">{{ old('body', $template->body ?? '') }}</textarea>
    </div>

    <div class="mb-6 flex items-center gap-2">
      <input type="checkbox" name="has_price" id="has_price" value="1"
             {{ old('has_price', $template->has_price ?? false) ? 'checked' : '' }}>
      <label for="has_price" class="text-sm text-gray-700">
        This template uses <code class="bg-gray-100 px-1 rounded">{price}</code> — prompt for price when creating campaign
      </label>
    </div>

    <div class="flex gap-3">
      <button type="submit"
              class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:opacity-80">
        {{ isset($template) ? 'Update Template' : 'Save Template' }}
      </button>
      <a href="{{ route('templates.index') }}"
         class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
        Cancel
      </a>
    </div>
  </form>
</div>

@endsection