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
  const res = await apiGet('/api/gmail-accounts').catch(() => null);
  const el  = document.getElementById('accountsList');

  if (!res || !res.accounts) {
    el.innerHTML = `
      <div class="bg-[#121218] border border-red-500/30 rounded-3xl p-8 text-center">
        <p class="text-red-400 font-bold mb-1">Could not load accounts</p>
        <p class="text-zinc-500 text-sm">${res?.message || res?.error || 'Server error loading accounts. Please refresh.'}</p>
      </div>`;
    return;
  }

  if (!res.accounts.length) {
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

  const rows = res.accounts.map(a => {
    const ready     = a.can_send !== false;
    const rowClass  = ready
      ? 'bg-emerald-500/[0.04] border-l-2 border-l-emerald-500/60'
      : 'bg-white/[0.02] border-l-2 border-l-amber-500/40';
    const sendState = ready
      ? `<span class="text-[10px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/40 rounded-full px-2 py-0.5">✅ Ready</span>`
      : `<span class="text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full px-2 py-0.5">🔒 Cooldown till ${fmtDate(a.next_available)}</span>`;
    const lastSent = a.last_sent_at ? fmtDate(a.last_sent_at) : '<span class="text-zinc-600">never</span>';
    const nameCls  = ready ? 'font-extrabold text-white' : 'font-semibold text-zinc-400';

    return `
    <tr class="border-b border-white/[0.04] hover:bg-white/[0.03] transition ${rowClass}">
      <td class="py-3 px-4">
        <div class="flex items-center gap-3 min-w-0">
          ${a.avatar
            ? `<img src="${a.avatar}" class="w-8 h-8 rounded-lg border border-white/10 flex-shrink-0">`
            : `<div class="w-8 h-8 rounded-lg bg-[#7c6ef7]/15 border border-[#7c6ef7]/25 flex items-center justify-center text-[#a78bfa] font-bold text-xs flex-shrink-0">${a.email[0].toUpperCase()}</div>`
          }
          <div class="min-w-0">
            <p class="text-sm ${nameCls} truncate">${a.name}</p>
            <p class="text-xs text-zinc-500 truncate">${a.email}</p>
          </div>
        </div>
      </td>
      <td class="py-3 px-4">
        <div class="flex items-center gap-1.5 flex-wrap">
          ${sendState}
          <span class="${miniBadge(a.is_active)}">${a.is_active ? 'Active' : 'Off'}</span>
          <span class="${miniBadge(a.has_script)}">${a.has_script ? 'Script' : 'No script'}</span>
          <span class="${miniBadge(a.token_status === 'valid')}">${a.token_status === 'valid' ? 'OAuth' : 'No OAuth'}</span>
        </div>
      </td>
      <td class="py-3 px-4 text-sm font-bold text-sky-400">${a.sent_today}</td>
      <td class="py-3 px-4 text-sm font-semibold ${ready ? 'text-emerald-400' : 'text-zinc-400'}">${a.remaining}</td>
      <td class="py-3 px-4 text-sm text-zinc-500">${lastSent}</td>
      <td class="py-3 px-4 text-sm text-zinc-500">${a.total_sent}</td>
      <td class="py-3 px-4">
        <div class="flex items-center justify-end gap-1">
          <button onclick="openScriptModal(${a.id}, '${a.script_url || ''}')" title="${a.has_script ? 'Update Script' : 'Set Script URL'}"
                  class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#7c6ef7]/10 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/20 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
          </button>
          ${a.has_script ? `
            <button onclick="testAccount(${a.id}, this)" title="Test Connection"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/25 hover:bg-sky-500/20 transition">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M9 3v6l-4.5 6v3h15v-3L15 9V3"/></svg>
            </button>
          ` : ''}
          <a href="{{ route('google.add-account') }}" title="${a.token_status === 'valid' ? 'Reconnect OAuth' : 'Connect Gmail'}"
             class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </a>
          <button onclick="toggleAccount(${a.id})" title="${a.is_active ? 'Deactivate' : 'Activate'}"
                  class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/5 text-zinc-400 border border-white/10 hover:bg-white/10 transition">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="${a.is_active ? 'M6 4h4v16H6zM14 4h4v16h-4z' : 'M8 5v14l11-7z'}"/></svg>
          </button>
          <button onclick="deleteAccount(${a.id})" title="Remove"
                  class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-500/10 text-red-400 border border-red-500/25 hover:bg-red-500/20 transition">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </td>
    </tr>
  `;
  }).join('');

  el.innerHTML = `
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06]">
        <h2 class="font-bold text-white">Accounts</h2>
        <span class="text-xs text-zinc-600 font-medium">${res.accounts.length} total</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-white/[0.03]">
            <tr>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Account</th>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Status</th>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Sent today</th>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Remaining</th>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Last sent</th>
              <th class="text-left py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Total sent</th>
              <th class="text-right py-3 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    </div>`;
}

function miniBadge(on) {
  return on
    ? 'text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full px-2 py-0.5'
    : 'text-[10px] font-semibold bg-white/5 text-zinc-500 border border-white/10 rounded-full px-2 py-0.5';
}

function fmtDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleString(undefined, {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit',
  });
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
  const orig = btn.innerHTML;
  btn.innerHTML = `<div class="spinner"></div>`;
  btn.disabled = true;
  const res = await apiPost(`/api/gmail-accounts/${id}/test`, {});
  btn.innerHTML = orig;
  btn.disabled = false;
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