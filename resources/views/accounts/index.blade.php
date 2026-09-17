@extends('layouts.app')
@section('title', 'Accounts')
@section('content')

<div class="mb-8 flex items-center justify-between">
  <div>
    <div class="flex items-center gap-2 text-[11px] font-semibold text-[#7c6ef7] uppercase tracking-widest mb-2">
      <span class="w-1.5 h-1.5 rounded-full bg-[#7c6ef7] inline-block"></span>
      Sending
    </div>
    <h1 class="text-3xl font-bold text-white tracking-tight">Gmail Accounts</h1>
    <p class="text-zinc-500 text-sm mt-1.5">Manage your sending accounts and Apps Script URLs</p>
  </div>
  <a href="{{ route('google.add-account') }}"
     class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
    Add Account
  </a>
</div>

<div id="accountsList">
  <div class="flex items-center justify-center py-16 text-zinc-500">
    <div class="spinner mr-3"></div> Loading accounts...
  </div>
</div>

<!-- Script URL Modal -->
<div id="scriptModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
  <div class="bg-[#121218] rounded-3xl border border-white/[0.08] w-full max-w-lg p-7 shadow-2xl">
    <h3 class="text-lg font-bold text-white mb-1.5">Set Apps Script URL</h3>
    <p class="text-sm text-zinc-500 mb-5">Deploy sender.gs on this Gmail account then paste the web app URL here</p>
    <input type="hidden" id="scriptAccountId">
    <input type="url" id="scriptUrlInput"
           placeholder="https://script.google.com/macros/s/.../exec"
           class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition mb-5">
    <div class="flex gap-3">
      <button onclick="saveScriptUrl()"
              class="flex-1 bg-[#7c6ef7] hover:bg-[#8d80f9] text-white rounded-xl py-2.5 text-sm font-semibold transition">
        Save URL
      </button>
      <button onclick="document.getElementById('scriptModal').classList.add('hidden')"
              class="flex-1 bg-white/5 text-zinc-400 rounded-xl py-2.5 text-sm font-semibold hover:bg-white/10 transition">
        Cancel
      </button>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', loadAccounts);

async function loadAccounts() {
  const res = await apiGet('/api/gmail-accounts');
  const el  = document.getElementById('accountsList');

  if (!res.accounts?.length) {
    el.innerHTML = `
      <div class="bg-[#121218] border border-white/[0.06] rounded-3xl p-14 text-center">
        <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-[#7c6ef7]/10 border border-[#7c6ef7]/25 flex items-center justify-center">
          <svg class="w-7 h-7 text-[#7c6ef7]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <p class="text-white text-lg font-bold mb-1">No accounts connected</p>
        <p class="text-zinc-500 text-sm mb-7">Connect your Gmail accounts to start sending</p>
        <a href="{{ route('google.add-account') }}"
           class="inline-flex items-center px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
          + Add Gmail Account
        </a>
      </div>`;
    return;
  }

  el.innerHTML = res.accounts.map(a => `
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-6 mb-4">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-center gap-3.5 min-w-0">
          ${a.avatar
            ? `<img src="${a.avatar}" class="w-11 h-11 rounded-xl border border-white/10">`
            : `<div class="w-11 h-11 rounded-xl bg-[#7c6ef7]/15 border border-[#7c6ef7]/25 flex items-center justify-center text-[#a78bfa] font-bold text-sm">${a.email[0].toUpperCase()}</div>`
          }
          <div class="min-w-0">
            <p class="font-bold text-white truncate">${a.name}</p>
            <p class="text-sm text-zinc-500 truncate">${a.email}</p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end flex-shrink-0">
          ${badge(a.is_active ? 'Active' : 'Inactive', a.is_active ? 'green' : 'red')}
          ${badge(a.has_script ? 'Script ✓' : 'No Script', a.has_script ? 'violet' : 'yellow')}
          ${badge(a.token_status === 'valid' ? 'OAuth ✓' : 'No OAuth', a.token_status === 'valid' ? 'green' : 'red')}
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-3 mt-5">
        <div class="text-center bg-white/[0.03] rounded-xl p-3.5 border border-white/[0.05]">
          <p class="text-2xl font-bold text-sky-400">${a.sent_today}</p>
          <p class="text-[11px] text-zinc-500 mt-0.5 font-medium">Sent today</p>
        </div>
        <div class="text-center bg-white/[0.03] rounded-xl p-3.5 border border-white/[0.05]">
          <p class="text-2xl font-bold text-white">${a.remaining}</p>
          <p class="text-[11px] text-zinc-500 mt-0.5 font-medium">Remaining</p>
        </div>
        <div class="text-center bg-white/[0.03] rounded-xl p-3.5 border border-white/[0.05]">
          <p class="text-2xl font-bold text-zinc-300">${a.total_sent}</p>
          <p class="text-[11px] text-zinc-500 mt-0.5 font-medium">Total sent</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-white/[0.05]">
        <button onclick="openScriptModal(${a.id}, '${a.script_url || ''}')"
                class="text-xs px-3.5 py-2 rounded-xl font-semibold bg-[#7c6ef7]/10 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/20 transition">
          🔗 ${a.has_script ? 'Update Script' : 'Set Script URL'}
        </button>
        ${a.has_script ? `
          <button onclick="testAccount(${a.id}, this)"
                  class="text-xs px-3.5 py-2 rounded-xl font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/25 hover:bg-sky-500/20 transition">
            🧪 Test Connection
          </button>
        ` : ''}
        <a href="{{ route('google.add-account') }}"
           class="text-xs px-3.5 py-2 rounded-xl font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition">
          🔄 ${a.token_status === 'valid' ? 'Reconnect OAuth' : 'Connect Gmail'}
        </a>
        <button onclick="toggleAccount(${a.id})"
                class="text-xs px-3.5 py-2 rounded-xl font-semibold bg-white/5 text-zinc-400 border border-white/10 hover:bg-white/10 transition">
          ${a.is_active ? '⏸ Deactivate' : '▶ Activate'}
        </button>
        <button onclick="deleteAccount(${a.id})"
                class="text-xs px-3.5 py-2 rounded-xl font-semibold bg-red-500/10 text-red-400 border border-red-500/25 hover:bg-red-500/20 transition">
          🗑️ Remove
        </button>
      </div>
    </div>
  `).join('');
}

function badge(text, color) {
  const colors = {
    green:  'bg-emerald-500/10 text-emerald-400 border-emerald-500/25',
    red:    'bg-red-500/10 text-red-400 border-red-500/25',
    violet: 'bg-[#7c6ef7]/10 text-[#a78bfa] border-[#7c6ef7]/25',
    yellow: 'bg-amber-500/10 text-amber-400 border-amber-500/25',
  };
  return `<span class="text-[11px] font-semibold border rounded-full px-2.5 py-1 ${colors[color]}">${text}</span>`;
}

function openScriptModal(id, currentUrl) {
  document.getElementById('scriptAccountId').value = id;
  document.getElementById('scriptUrlInput').value  = currentUrl;
  document.getElementById('scriptModal').classList.remove('hidden');
}

async function saveScriptUrl() {
  const id  = document.getElementById('scriptAccountId').value;
  const url = document.getElementById('scriptUrlInput').value.trim();
  if (!url) { toast('Error', 'Please enter a URL', 'error'); return; }
  const res = await apiPost(`/api/gmail-accounts/${id}/script`, { script_url: url });
  if (res.success) {
    toast('Saved!', 'Apps Script URL saved', 'success');
    document.getElementById('scriptModal').classList.add('hidden');
    loadAccounts();
  } else {
    toast('Error', res.error || res.message, 'error');
  }
}

async function testAccount(id, btn) {
  const orig = btn.textContent;
  btn.textContent = '⏳ Testing...';
  btn.disabled = true;
  const res = await apiPost(`/api/gmail-accounts/${id}/test`, {});
  btn.textContent = res.message;
  btn.disabled = false;
  setTimeout(() => btn.textContent = orig, 3000);
  toast(res.success ? 'Connected!' : 'Failed', res.message, res.success ? 'success' : 'error');
}

async function toggleAccount(id) {
  const res = await apiPost(`/api/gmail-accounts/${id}/toggle`, {});
  if (res.success) { toast('Updated', res.message, 'success'); loadAccounts(); }
}

async function deleteAccount(id) {
  if (!confirm('Remove this account?')) return;
  const res = await apiDelete(`/api/gmail-accounts/${id}`);
  if (res.success) { toast('Removed', 'Account removed', 'success'); loadAccounts(); }
}
</script>
@endsection