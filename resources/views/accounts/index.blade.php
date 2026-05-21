@extends('layouts.app')
@section('title', 'Accounts')
@section('content')

<div class="mb-6 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Gmail Accounts</h1>
    <p class="text-gray-500 text-sm mt-1">Manage your sending accounts and Apps Script URLs</p>
  </div>
  <a href="{{ route('google.add-account') }}"
     class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
    + Add Account
  </a>
</div>

<div id="accountsList">
  <div class="flex items-center justify-center py-16 text-gray-400">
    <div class="spinner mr-3"></div> Loading accounts...
  </div>
</div>

<!-- Script URL Modal -->
<div id="scriptModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Set Apps Script URL</h3>
    <p class="text-sm text-gray-500 mb-4">Deploy sender.gs on this Gmail account then paste the web app URL here</p>
    <input type="hidden" id="scriptAccountId">
    <input type="url" id="scriptUrlInput"
           placeholder="https://script.google.com/macros/s/.../exec"
           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 mb-4">
    <div class="flex gap-3">
      <button onclick="saveScriptUrl()"
              class="flex-1 bg-blue-600 text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-blue-700">
        Save URL
      </button>
      <button onclick="document.getElementById('scriptModal').classList.add('hidden')"
              class="flex-1 bg-gray-100 text-gray-700 rounded-lg py-2.5 text-sm font-semibold hover:bg-gray-200">
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
      <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <p class="text-gray-400 text-lg mb-1">No accounts connected</p>
        <p class="text-gray-400 text-sm mb-6">Connect your Gmail accounts to start sending</p>
        <a href="{{ route('google.add-account') }}"
           class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
          + Add Gmail Account
        </a>
      </div>`;
    return;
  }

  el.innerHTML = res.accounts.map(a => `
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
          ${a.avatar
            ? `<img src="${a.avatar}" class="w-10 h-10 rounded-full border border-gray-200">`
            : `<div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">${a.email[0].toUpperCase()}</div>`
          }
          <div>
            <p class="font-semibold text-gray-900">${a.name}</p>
            <p class="text-sm text-gray-500">${a.email}</p>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
          ${badge(a.is_active ? 'Active' : 'Inactive', a.is_active ? 'green' : 'red')}
          ${badge(a.has_script ? 'Script ✓' : 'No Script', a.has_script ? 'blue' : 'yellow')}
          ${badge(a.token_status === 'valid' ? 'OAuth ✓' : 'No OAuth', a.token_status === 'valid' ? 'green' : 'red')}
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-3 mt-4">
        <div class="text-center bg-gray-50 rounded-lg p-3 border border-gray-100">
          <p class="text-xl font-bold text-blue-600">${a.sent_today}</p>
          <p class="text-xs text-gray-400 mt-0.5">Sent today</p>
        </div>
        <div class="text-center bg-gray-50 rounded-lg p-3 border border-gray-100">
          <p class="text-xl font-bold text-gray-900">${a.remaining}</p>
          <p class="text-xs text-gray-400 mt-0.5">Remaining</p>
        </div>
        <div class="text-center bg-gray-50 rounded-lg p-3 border border-gray-100">
          <p class="text-xl font-bold text-gray-700">${a.total_sent}</p>
          <p class="text-xs text-gray-400 mt-0.5">Total sent</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
        <button onclick="openScriptModal(${a.id}, '${a.script_url || ''}')"
                class="text-xs px-3 py-2 rounded-lg font-semibold bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100">
          🔗 ${a.has_script ? 'Update Script' : 'Set Script URL'}
        </button>
        ${a.has_script ? `
          <button onclick="testAccount(${a.id}, this)"
                  class="text-xs px-3 py-2 rounded-lg font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100">
            🧪 Test Connection
          </button>
        ` : ''}
        <a href="{{ route('google.add-account') }}"
           class="text-xs px-3 py-2 rounded-lg font-semibold bg-green-50 text-green-700 border border-green-200 hover:bg-green-100">
          🔄 ${a.token_status === 'valid' ? 'Reconnect OAuth' : 'Connect Gmail'}
        </a>
        <button onclick="toggleAccount(${a.id})"
                class="text-xs px-3 py-2 rounded-lg font-semibold bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200">
          ${a.is_active ? '⏸ Deactivate' : '▶ Activate'}
        </button>
        <button onclick="deleteAccount(${a.id})"
                class="text-xs px-3 py-2 rounded-lg font-semibold bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
          🗑️ Remove
        </button>
      </div>
    </div>
  `).join('');
}

function badge(text, color) {
  const colors = {
    green:  'bg-green-50 text-green-700 border-green-200',
    red:    'bg-red-50 text-red-700 border-red-200',
    blue:   'bg-blue-50 text-blue-700 border-blue-200',
    yellow: 'bg-yellow-50 text-yellow-700 border-yellow-200',
  };
  return `<span class="text-xs border rounded-full px-2 py-0.5 font-medium ${colors[color]}">${text}</span>`;
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