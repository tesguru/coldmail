@extends('layouts.app')
@section('title', 'Campaign')
@section('content')

<div id="campaignDetail">
  <div class="flex items-center justify-center py-16 text-gray-400">
    <div class="spinner mr-3"></div> Loading campaign...
  </div>
</div>

<!-- PRICE PROMPT MODAL -->
<div id="priceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-md p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-1">Confirm Price for Follow Up</h3>
    <p class="text-sm text-gray-500 mb-4">
      Your follow up template contains <code class="bg-gray-100 px-1 rounded text-xs">{price}</code>.
      Confirm or update the price for this follow up.
    </p>
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
      <input type="text" id="priceInput"
             class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200"
             placeholder="e.g. $2,499">
      <p class="text-xs text-gray-400 mt-1">
        Campaign default: <span id="defaultPriceLabel" class="font-semibold text-gray-600"></span>
      </p>
    </div>
    <div class="flex gap-3">
      <button onclick="confirmFollowUpPrice()"
              class="flex-1 bg-green-600 text-white rounded-lg py-2.5 text-sm font-semibold hover:bg-green-700">
        Send Follow Up
      </button>
      <button onclick="document.getElementById('priceModal').classList.add('hidden')"
              class="flex-1 bg-gray-100 text-gray-700 rounded-lg py-2.5 text-sm font-semibold hover:bg-gray-200">
        Cancel
      </button>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
const CAMPAIGN_ID = {{ $id }};
let allEmails      = [];
let followUpStatus = {};

document.addEventListener('DOMContentLoaded', () => {
  loadCampaign();
  loadFollowUpStatus();
});

async function loadFollowUpStatus() {
  const res = await apiGet(`/api/campaigns/${CAMPAIGN_ID}/follow-up-status`);
  if (res.success) followUpStatus = res;
}

async function loadCampaign() {
  const res = await apiGet(`/api/campaigns/${CAMPAIGN_ID}`);

  if (!res.success) {
    document.getElementById('campaignDetail').innerHTML = `
      <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <p class="text-gray-500 mb-4">Campaign not found</p>
        <a href="{{ route('campaigns.index') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
          ← Back
        </a>
      </div>`;
    return;
  }

  const c = res.campaign;
  allEmails = c.emails;

  // ── Progress calculations ──
  const sentPct      = c.total_emails > 0 ? Math.round((c.sent_count / c.total_emails) * 100) : 0;
  const eligibleFU   = c.emails.filter(e => e.status === 'sent' && !e.has_reply && !e.is_bounced).length;
  const fuPct        = c.sent_count > 0 ? Math.round((c.follow_up_count / c.sent_count) * 100) : 0;
  const failed       = c.emails.filter(e => e.status === 'failed').length;
  const pending      = c.emails.filter(e => e.status === 'pending').length;

  document.getElementById('campaignDetail').innerHTML = `

    <!-- BREADCRUMB -->
    <div class="mb-6">
      <a href="{{ route('campaigns.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Campaigns</a>
      <div class="flex items-start justify-between mt-2">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">${c.name}</h1>
          <p class="text-gray-500 text-sm mt-1">
            ${c.domain} · ${c.price} · sent by ${c.your_name}
          </p>
        </div>
        <span class="${statusBadgeClass(c.status)}">${c.status}</span>
      </div>
    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      ${statBox('Total', c.total_emails, 'text-blue-600')}
      ${statBox('Sent', c.sent_count, 'text-green-600')}
      ${statBox('Replied', c.replied_count, 'text-purple-600')}
      ${statBox('Follow-ups', c.follow_up_count, 'text-amber-600')}
      ${statBox('Bounced', c.bounce_count, 'text-red-500')}
    </div>

    <!-- PROGRESS BARS -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
      <h2 class="font-semibold text-gray-900 mb-5">Sending Progress</h2>

      <!-- Initial Emails Progress -->
      <div class="mb-5">
        <div class="flex items-center justify-between mb-1.5">
          <div>
            <span class="text-sm font-medium text-gray-700">Initial Emails</span>
            ${pending > 0 ? `<span class="ml-2 text-xs bg-blue-50 text-blue-600 border border-blue-200 rounded-full px-2 py-0.5">${pending} queued</span>` : ''}
          </div>
          <span class="text-sm font-semibold text-gray-900">${c.sent_count} / ${c.total_emails} <span class="text-gray-400 font-normal">(${sentPct}%)</span></span>
        </div>
        <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
          <div class="h-3 rounded-full transition-all duration-500 ${sentPct === 100 ? 'bg-green-500' : 'bg-blue-500'}"
               style="width: ${sentPct}%"></div>
        </div>
        <div class="flex items-center justify-between mt-1">
          <p class="text-xs text-gray-400">${sentPct === 100 ? '✅ All initial emails sent' : sentPct + '% complete'}</p>
          <p class="text-xs text-gray-400">${c.total_emails - c.sent_count} remaining</p>
        </div>
      </div>

      <!-- Follow Up Progress -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <div>
            <span class="text-sm font-medium text-gray-700">Follow Ups</span>
            <span class="ml-2 text-xs bg-gray-100 text-gray-500 border border-gray-200 rounded-full px-2 py-0.5">${eligibleFU} eligible</span>
          </div>
          <span class="text-sm font-semibold text-gray-900">${c.follow_up_count} / ${c.sent_count} <span class="text-gray-400 font-normal">(${fuPct}%)</span></span>
        </div>
        <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
          <div class="h-3 rounded-full transition-all duration-500 ${fuPct === 100 ? 'bg-green-500' : 'bg-amber-500'}"
               style="width: ${fuPct}%"></div>
        </div>
        <div class="flex items-center justify-between mt-1">
          <p class="text-xs text-gray-400">${fuPct === 100 ? '✅ All follow ups sent' : fuPct + '% of sent prospects followed up'}</p>
          <p class="text-xs text-gray-400">${eligibleFU} still to follow up</p>
        </div>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
      <h2 class="font-semibold text-gray-900 mb-1">Actions</h2>
      <p class="text-xs text-gray-400 mb-4">
        Follow up sends to prospects: sent · not replied · not bounced
        <span class="font-semibold text-gray-600">(${eligibleFU} eligible)</span>
      </p>
      <div class="flex flex-wrap gap-3">

        <!-- FOLLOW UP BUTTON -->
        <button onclick="handleFollowUp()" id="followUpBtn"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg transition
                       ${eligibleFU === 0
                         ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                         : 'bg-green-600 text-white hover:bg-green-700'}">
          🔄 Send Follow Up
          <span class="text-xs ${eligibleFU === 0 ? 'bg-gray-200 text-gray-400' : 'bg-green-500 text-white'} rounded-full px-1.5 py-0.5">
            ${eligibleFU}
          </span>
        </button>

        <!-- RETRY FAILED -->
        ${failed > 0 ? `
          <button onclick="retryFailed()" id="retryBtn"
                  class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition">
            🔁 Retry Failed
            <span class="bg-amber-400 text-white text-xs rounded-full px-1.5 py-0.5">${failed}</span>
          </button>
        ` : ''}

      </div>
    </div>

    <!-- PROSPECTS TABLE -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">Prospects</h2>
        <span class="text-xs text-gray-400">${c.emails.length} total</span>
      </div>

      <!-- FILTERS -->
      <div class="px-5 py-3 border-b border-gray-100 flex gap-2 flex-wrap">
        ${filterBtn('all',     'All',      c.emails.length)}
        ${filterBtn('sent',    'Sent',     c.emails.filter(e => e.status === 'sent').length)}
        ${filterBtn('replied', 'Replied',  c.emails.filter(e => e.has_reply).length)}
        ${filterBtn('pending', 'Pending',  c.emails.filter(e => e.status === 'pending').length)}
        ${filterBtn('failed',  'Failed',   c.emails.filter(e => e.status === 'failed').length)}
        ${filterBtn('bounced', 'Bounced',  c.emails.filter(e => e.is_bounced).length)}
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Email</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Name</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Company</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Follow-ups</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Sent at</th>
              <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Account</th>
            </tr>
          </thead>
          <tbody id="emailsBody">
            ${renderEmails(c.emails)}
          </tbody>
        </table>
      </div>
    </div>
  `;
}

// ── Follow up handler — checks if price needed ────────────────
async function handleFollowUp() {
  const eligible = allEmails.filter(e => e.status === 'sent' && !e.has_reply && !e.is_bounced).length;
  if (eligible === 0) { toast('No eligible prospects', 'All have replied or bounced', 'info'); return; }

  // First check follow up status to see if price needed
  const status = await apiGet(`/api/campaigns/${CAMPAIGN_ID}/follow-up-status`);

  if (status.needs_price) {
    // Show price modal
    document.getElementById('defaultPriceLabel').textContent = status.campaign_price || 'not set';
    document.getElementById('priceInput').value              = status.campaign_price || '';
    document.getElementById('priceModal').classList.remove('hidden');
    return;
  }

  // No price needed — send directly
  await submitFollowUp(null);
}

// ── Called when user confirms price in modal ──────────────────
async function confirmFollowUpPrice() {
  const price = document.getElementById('priceInput').value.trim();
  document.getElementById('priceModal').classList.add('hidden');
  await submitFollowUp(price);
}

// ── Actually submit the follow up ────────────────────────────
async function submitFollowUp(price) {
  if (!confirm(`Send follow up to eligible prospects?`)) return;

  const btn = document.getElementById('followUpBtn');
  if (btn) { btn.textContent = '⏳ Queuing...'; btn.disabled = true; }

  const payload = {};
  if (price) payload.price = price;

  const res = await apiPost(`/api/campaigns/${CAMPAIGN_ID}/follow-up`, payload);

  if (btn) { btn.innerHTML = '🔄 Send Follow Up'; btn.disabled = false; }

  if (res.success) {
    toast('Follow up queued! 🎉', res.message, 'success');
    loadCampaign();
  } else if (res.needs_price) {
    // Shouldn't happen but handle it
    document.getElementById('defaultPriceLabel').textContent = res.campaign_price || 'not set';
    document.getElementById('priceInput').value              = res.campaign_price || '';
    document.getElementById('priceModal').classList.remove('hidden');
  } else {
    toast('Error', res.error, 'error');
  }
}

async function retryFailed() {
  if (!confirm('Retry all failed emails?')) return;
  const btn = document.getElementById('retryBtn');
  btn.textContent = '⏳ Retrying...';
  btn.disabled    = true;
  const res = await apiPost(`/api/campaigns/${CAMPAIGN_ID}/retry-failed`, {});
  btn.textContent = '🔁 Retry Failed';
  btn.disabled    = false;
  if (res.success) { toast('Retried! 🎉', res.message, 'success'); loadCampaign(); }
  else toast('Error', res.error, 'error');
}

function filterBtn(filter, label, count) {
  const isActive = filter === 'all';
  return `
    <button onclick="filterEmails('${filter}')" id="filter-${filter}"
            class="text-xs px-3 py-1.5 rounded-lg font-semibold border transition-all
                   ${isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300'}">
      ${label} (${count})
    </button>`;
}

function renderEmails(emails) {
  if (!emails.length) {
    return `<tr><td colspan="7" class="text-center py-10 text-gray-400 text-sm">No prospects found</td></tr>`;
  }
  return emails.map(e => `
    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
      <td class="py-3 px-4 text-gray-900 font-medium text-sm">${e.to_email}</td>
      <td class="py-3 px-4 text-gray-600 text-sm">${e.first_name || '—'}</td>
      <td class="py-3 px-4 text-gray-600 text-sm">${e.company_name || '—'}</td>
      <td class="py-3 px-4"><span class="${emailStatusClass(e)}">${emailStatusLabel(e)}</span></td>
      <td class="py-3 px-4 text-gray-500 text-sm">${e.follow_up_count}</td>
      <td class="py-3 px-4 text-gray-400 text-xs">${e.sent_at ? new Date(e.sent_at).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : '—'}</td>
      <td class="py-3 px-4 text-gray-400 text-xs truncate max-w-[140px]">${e.gmail_account || '—'}</td>
    </tr>
  `).join('');
}

function filterEmails(filter) {
  const filtered = filter === 'all' ? allEmails : allEmails.filter(e => {
    if (filter === 'replied') return e.has_reply;
    if (filter === 'bounced') return e.is_bounced;
    return e.status === filter;
  });
  document.getElementById('emailsBody').innerHTML = renderEmails(filtered);

  document.querySelectorAll('[id^="filter-"]').forEach(btn => {
    btn.className = btn.className
      .replace('bg-blue-600 text-white border-blue-600', 'bg-white text-gray-600 border-gray-200 hover:border-blue-300');
  });
  const active = document.getElementById(`filter-${filter}`);
  if (active) active.className = active.className
    .replace('bg-white text-gray-600 border-gray-200 hover:border-blue-300', 'bg-blue-600 text-white border-blue-600');
}

function statBox(label, value, color) {
  return `
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold ${color}">${value}</p>
      <p class="text-xs text-gray-400 mt-1">${label}</p>
    </div>`;
}

function statusBadgeClass(status) {
  const map = {
    active:    'text-xs font-semibold bg-green-50 text-green-700 border border-green-200 rounded-full px-3 py-0.5',
    paused:    'text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full px-3 py-0.5',
    completed: 'text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-0.5',
  };
  return map[status] || 'text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 rounded-full px-3 py-0.5';
}

function emailStatusClass(e) {
  if (e.has_reply)            return 'text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 rounded-full px-2 py-0.5';
  if (e.is_bounced)           return 'text-xs font-semibold bg-red-50 text-red-600 border border-red-200 rounded-full px-2 py-0.5';
  if (e.status === 'sent')    return 'text-xs font-semibold bg-green-50 text-green-700 border border-green-200 rounded-full px-2 py-0.5';
  if (e.status === 'pending') return 'text-xs font-semibold bg-blue-50 text-blue-600 border border-blue-200 rounded-full px-2 py-0.5';
  if (e.status === 'failed')  return 'text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full px-2 py-0.5';
  return 'text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200 rounded-full px-2 py-0.5';
}

function emailStatusLabel(e) {
  if (e.has_reply)            return '💬 Replied';
  if (e.is_bounced)           return '❌ Bounced';
  if (e.status === 'sent')    return '✅ Sent';
  if (e.status === 'pending') return '⏳ Pending';
  if (e.status === 'failed')  return '⚠️ Failed';
  return e.status;
}
</script>
@endsection