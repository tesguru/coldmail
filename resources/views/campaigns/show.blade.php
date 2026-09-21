@extends('layouts.app')
@section('title', 'Campaign')
@section('content')

<div id="campaignDetail">
  <div class="flex items-center justify-center py-16 text-zinc-500">
    <div class="spinner mr-3"></div> Loading campaign...
  </div>
</div>

<!-- PRICE PROMPT MODAL -->
<div id="priceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
  <div class="bg-[#121218] rounded-3xl border border-white/[0.08] w-full max-w-md p-7 shadow-2xl">
    <h3 class="text-lg font-bold text-white mb-1.5">Confirm Price for Follow Up</h3>
    <p class="text-sm text-zinc-500 mb-5">
      Your follow up template contains <code class="bg-[#0e0e14] px-1.5 py-0.5 rounded-lg border border-white/10 text-xs text-[#a78bfa]">{price}</code>.
      Confirm or update the price for this follow up.
    </p>
    <div class="mb-5">
      <label class="block text-sm font-medium text-zinc-300 mb-1.5">Price</label>
      <input type="text" id="priceInput"
             class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition"
             placeholder="e.g. $2,499">
      <p class="text-xs text-zinc-600 mt-1.5">
        Campaign default: <span id="defaultPriceLabel" class="font-semibold text-zinc-400"></span>
      </p>
    </div>
    <div class="flex gap-3">
      <button onclick="confirmFollowUpPrice()"
              class="flex-1 bg-emerald-500 hover:bg-emerald-400 text-white rounded-xl py-2.5 text-sm font-bold transition">
        Send Follow Up
      </button>
      <button onclick="document.getElementById('priceModal').classList.add('hidden')"
              class="flex-1 bg-white/5 text-zinc-400 rounded-xl py-2.5 text-sm font-semibold hover:bg-white/10 transition">
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
      <div class="bg-[#121218] border border-white/[0.06] rounded-3xl p-14 text-center">
        <p class="text-zinc-400 mb-5">Campaign not found</p>
        <a href="{{ route('campaigns.index') }}"
           class="inline-flex items-center px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
          ← Back
        </a>
      </div>`;
    return;
  }

  const c = res.campaign;
  allEmails = c.emails;

  const sentPct    = c.total_emails > 0 ? Math.round((c.sent_count / c.total_emails) * 100) : 0;
  const eligibleFU = c.emails.filter(e => e.status === 'sent' && !e.has_reply && !e.is_bounced).length;
  const failed     = c.emails.filter(e => e.status === 'failed').length;
  const pending    = c.emails.filter(e => e.status === 'pending').length;

  const maxLevel     = c.emails.reduce((m, e) => Math.max(m, e.follow_up_count), 0);
  const levelsToShow = Math.min(Math.max(maxLevel + 1, 1), 20);

  // ── Build Facebook links HTML ──
  const facebookHtml = (c.facebook_links || []).length > 0
    ? (c.facebook_links).map((url, i) => `
        <a href="${url}" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#a78bfa] bg-[#7c6ef7]/10 border border-[#7c6ef7]/25 rounded-full px-3 py-1.5 hover:bg-[#7c6ef7]/20 transition">
          📘 ${c.facebook_links.length > 1 ? 'Facebook ' + (i + 1) : 'Facebook Page'}
        </a>`).join('')
    : '';

  // ── Build Website links HTML ──
  const websiteHtml = (c.website_links || []).length > 0
    ? (c.website_links).map((url, i) => `
        <a href="${url}" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 bg-white/5 border border-white/10 rounded-full px-3 py-1.5 hover:bg-white/10 transition">
          🌐 ${c.website_links.length > 1 ? 'Website ' + (i + 1) : 'Website'}
        </a>`).join('')
    : '';

  // ── Combined outreach links section ──
  const outreachLinksHtml = (facebookHtml || websiteHtml)
    ? `<div class="mt-5 p-4 bg-[#0e0e14] border border-white/[0.06] rounded-2xl">
         <p class="text-[11px] font-semibold text-zinc-500 uppercase tracking-widest mb-2.5">Manual Outreach Channels</p>
         <div class="flex flex-wrap gap-2">
           ${facebookHtml}
           ${websiteHtml}
         </div>
       </div>`
    : '';

  document.getElementById('campaignDetail').innerHTML = `

    <!-- BREADCRUMB -->
    <div class="mb-7">
      <a href="{{ route('campaigns.index') }}" class="text-sm text-zinc-600 hover:text-zinc-300 transition font-medium">← Campaigns</a>
      <div class="flex items-start justify-between mt-2">
        <div>
          <h1 class="text-3xl font-bold text-white tracking-tight">${c.name}</h1>
          <p class="text-zinc-500 text-sm mt-1.5">
            ${c.domain} · ${c.price} · sent by ${c.your_name}
          </p>
        </div>
        <span class="${statusBadgeClass(c.status)}">${c.status}</span>
      </div>

      ${outreachLinksHtml}
    </div>

    <!-- STAT CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-7">
      ${statBox('Total',      c.total_emails,    'text-sky-400')}
      ${statBox('Sent',       c.sent_count,      'text-emerald-400')}
      ${statBox('Replied',    c.replied_count,   'text-[#a78bfa]')}
      ${statBox('Follow-ups', c.follow_up_count, 'text-amber-400')}
      ${statBox('Bounced',    c.bounce_count,    'text-red-400')}
    </div>

    <!-- PROGRESS BARS -->
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-6 mb-6">
      <h2 class="font-bold text-white mb-6">Sending Progress</h2>

      <!-- Initial Emails Progress -->
      <div class="mb-7">
        <div class="flex items-center justify-between mb-2">
          <div>
            <span class="text-sm font-semibold text-zinc-300">Initial Emails</span>
            ${pending > 0 ? `<span class="ml-2 text-xs bg-sky-500/10 text-sky-400 border border-sky-500/25 rounded-full px-2 py-0.5">${pending} queued</span>` : ''}
          </div>
          <span class="text-sm font-bold text-white">
            ${c.sent_count} / ${c.total_emails}
            <span class="text-zinc-500 font-normal">(${sentPct}%)</span>
          </span>
        </div>
        <div class="bg-white/5 rounded-full h-3 overflow-hidden">
          <div class="h-3 rounded-full transition-all duration-500 ${sentPct === 100 ? 'bg-emerald-500' : 'bg-[#7c6ef7]'}"
               style="width: ${sentPct}%"></div>
        </div>
        <div class="flex items-center justify-between mt-1.5">
          <p class="text-xs text-zinc-600">${sentPct === 100 ? '✅ All initial emails sent' : sentPct + '% complete'}</p>
          <p class="text-xs text-zinc-600">${c.total_emails - c.sent_count} remaining</p>
        </div>
      </div>

      <!-- Follow Up Progress -->
      <div>
        <div class="flex items-center justify-between mb-5">
          <div>
            <span class="text-sm font-semibold text-zinc-300">Follow Ups</span>
            <span class="ml-2 text-xs bg-white/5 text-zinc-500 border border-white/10 rounded-full px-2 py-0.5">
              ${eligibleFU} eligible now
            </span>
          </div>
          <span class="text-sm font-bold text-white">${c.follow_up_count} total sent</span>
        </div>

        ${(() => {
          let bars = '';

          for (let level = 1; level <= levelsToShow; level++) {
            const sentAtLevel = c.emails.filter(e => e.follow_up_count >= level).length;
            const pct         = c.sent_count > 0 ? Math.round((sentAtLevel / c.sent_count) * 100) : 0;
            const isComplete  = pct === 100 && sentAtLevel > 0;
            const isNext      = level === maxLevel + 1;

            bars += `
              <div class="mb-5 ${isNext ? 'opacity-50' : ''}">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-zinc-300">Follow-up ${level}</span>
                    ${isNext
                      ? `<span class="text-xs bg-sky-500/10 text-sky-400 border border-sky-500/25 rounded-full px-2 py-0.5">next to send</span>`
                      : isComplete
                        ? `<span class="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full px-2 py-0.5">complete</span>`
                        : `<span class="text-xs bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full px-2 py-0.5">in progress</span>`
                    }
                  </div>
                  <span class="text-sm font-bold text-white">
                    ${sentAtLevel} / ${c.sent_count}
                    <span class="text-zinc-500 font-normal">(${pct}%)</span>
                  </span>
                </div>
                <div class="bg-white/5 rounded-full h-2.5 overflow-hidden">
                  <div class="h-2.5 rounded-full transition-all duration-500
                              ${isComplete ? 'bg-emerald-500' : isNext ? 'bg-zinc-600' : 'bg-amber-400'}"
                       style="width: ${pct}%"></div>
                </div>
                <div class="flex items-center justify-between mt-1.5">
                  <p class="text-xs text-zinc-600">
                    ${isComplete
                      ? '✅ All prospects received this follow-up'
                      : isNext
                        ? `${eligibleFU} prospects eligible to receive this`
                        : `${c.sent_count - sentAtLevel} prospects haven't received this yet`
                    }
                  </p>
                  ${!isComplete && !isNext
                    ? `<p class="text-xs text-zinc-600">${pct}% reached</p>`
                    : ''
                  }
                </div>
              </div>
            `;
          }

          return bars || `<p class="text-sm text-zinc-500 py-2">No follow-ups sent yet. Click "Send Follow Up" below to start.</p>`;
        })()}
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-6 mb-6">
      <h2 class="font-bold text-white mb-1.5">Actions</h2>
      <p class="text-xs text-zinc-600 mb-5">
        Follow up sends to prospects: sent · not replied · not bounced
        <span class="font-semibold text-zinc-400">(${eligibleFU} eligible)</span>
      </p>
      <div class="flex flex-wrap gap-3">

        <button onclick="handleFollowUp()" id="followUpBtn"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold rounded-xl transition
                       ${eligibleFU === 0
                         ? 'bg-white/5 text-zinc-600 cursor-not-allowed'
                         : 'bg-emerald-500 text-white hover:bg-emerald-400'}">
          🔄 Send Follow Up
          <span class="text-xs ${eligibleFU === 0 ? 'bg-white/10 text-zinc-500' : 'bg-emerald-700 text-white'} rounded-full px-2 py-0.5">
            ${eligibleFU}
          </span>
        </button>

        ${failed > 0 ? `
          <button onclick="retryFailed()" id="retryBtn"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-400 transition">
            🔁 Retry Failed
            <span class="bg-amber-700 text-white text-xs rounded-full px-2 py-0.5">${failed}</span>
          </button>
        ` : ''}

      </div>
    </div>

    <!-- PROSPECTS TABLE -->
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
        <h2 class="font-bold text-white">Prospects</h2>
        <span class="text-xs text-zinc-600 font-medium">${c.emails.length} total</span>
      </div>

      <div class="px-6 py-3.5 border-b border-white/[0.05] flex gap-2 flex-wrap">
        ${filterBtn('all',     'All',     c.emails.length)}
        ${filterBtn('sent',    'Sent',    c.emails.filter(e => e.status === 'sent').length)}
        ${filterBtn('replied', 'Replied', c.emails.filter(e => e.has_reply).length)}
        ${filterBtn('pending', 'Pending', c.emails.filter(e => e.status === 'pending').length)}
        ${filterBtn('failed',  'Failed',  c.emails.filter(e => e.status === 'failed').length)}
        ${filterBtn('bounced', 'Bounced', c.emails.filter(e => e.is_bounced).length)}
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-white/[0.03]">
            <tr>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Email</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Name</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Company</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Status</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Follow-ups</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Sent at</th>
              <th class="text-left py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Account</th>
              <th class="text-right py-3.5 px-4 text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">Action</th>
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

// ── Follow up handler ──
async function handleFollowUp() {
  const eligible = allEmails.filter(e => e.status === 'sent' && !e.has_reply && !e.is_bounced).length;
  if (eligible === 0) { toast('No eligible prospects', 'All have replied or bounced', 'info'); return; }

  const status = await apiGet(`/api/campaigns/${CAMPAIGN_ID}/follow-up-status`);

  if (status.needs_price) {
    document.getElementById('defaultPriceLabel').textContent = status.campaign_price || 'not set';
    document.getElementById('priceInput').value              = status.campaign_price || '';
    document.getElementById('priceModal').classList.remove('hidden');
    return;
  }

  await submitFollowUp(null);
}

async function confirmFollowUpPrice() {
  const price = document.getElementById('priceInput').value.trim();
  document.getElementById('priceModal').classList.add('hidden');
  await submitFollowUp(price);
}

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

const FILTER_ACTIVE   = 'text-xs px-3.5 py-1.5 rounded-xl font-semibold border transition-all bg-[#7c6ef7] text-white border-[#7c6ef7]';
const FILTER_INACTIVE = 'text-xs px-3.5 py-1.5 rounded-xl font-semibold border transition-all bg-white/[0.03] text-zinc-500 border-white/10 hover:border-[#7c6ef7]/40 hover:text-zinc-300';

function filterBtn(filter, label, count) {
  const isActive = filter === 'all';
  return `
    <button onclick="filterEmails('${filter}')" id="filter-${filter}"
            class="${isActive ? FILTER_ACTIVE : FILTER_INACTIVE}">
      ${label} (${count})
    </button>`;
}

function renderEmails(emails) {
  if (!emails.length) {
    return `<tr><td colspan="8" class="text-center py-10 text-zinc-500 text-sm">No prospects found</td></tr>`;
  }
  return emails.map(e => `
    <tr class="border-b border-white/[0.04] hover:bg-white/[0.03] transition">
      <td class="py-3.5 px-4 text-zinc-200 font-semibold text-sm">${e.to_email}</td>
      <td class="py-3.5 px-4 text-zinc-400 text-sm">${e.first_name || '—'}</td>
      <td class="py-3.5 px-4 text-zinc-400 text-sm">${e.company_name || '—'}</td>
      <td class="py-3.5 px-4"><span class="${emailStatusClass(e)}">${emailStatusLabel(e)}</span></td>
      <td class="py-3.5 px-4 text-zinc-500 text-sm">${e.follow_up_count}</td>
      <td class="py-3.5 px-4 text-zinc-600 text-xs">${e.sent_at ? new Date(e.sent_at).toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) : '—'}</td>
      <td class="py-3.5 px-4 text-zinc-600 text-xs truncate max-w-[140px]">${e.gmail_account || '—'}</td>
      <td class="py-3.5 px-4 text-right">
        <button onclick="event.stopPropagation(); deleteProspect(${e.id}, '${e.to_email.replace(/'/g, "\\'")}')"
                class="text-zinc-600 hover:text-red-400 transition text-lg leading-none font-bold p-1"
                title="Remove this prospect">×</button>
      </td>
    </tr>
  `).join('');
}

async function deleteProspect(id, email) {
  if (!confirm(`Remove ${email} from this campaign?\nAny queued send for them will be cancelled.`)) return;
  const res = await apiDelete(`/api/campaigns/${CAMPAIGN_ID}/emails/${id}`);
  if (res.success) {
    toast('Removed', 'Prospect removed', 'success');
    loadCampaign();
  } else {
    toast('Error', res.error, 'error');
  }
}

function filterEmails(filter) {
  const filtered = filter === 'all' ? allEmails : allEmails.filter(e => {
    if (filter === 'replied') return e.has_reply;
    if (filter === 'bounced') return e.is_bounced;
    return e.status === filter;
  });
  document.getElementById('emailsBody').innerHTML = renderEmails(filtered);

  document.querySelectorAll('[id^="filter-"]').forEach(btn => {
    btn.className = FILTER_INACTIVE;
  });
  const active = document.getElementById(`filter-${filter}`);
  if (active) active.className = FILTER_ACTIVE;
}

function statBox(label, value, color) {
  return `
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-4 text-center">
      <p class="text-3xl font-bold ${color} tracking-tight">${value}</p>
      <p class="text-[11px] text-zinc-500 mt-1 font-medium">${label}</p>
    </div>`;
}

function statusBadgeClass(status) {
  const map = {
    active:    'text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full px-3.5 py-1.5',
    paused:    'text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full px-3.5 py-1.5',
    completed: 'text-xs font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/25 rounded-full px-3.5 py-1.5',
  };
  return map[status] || 'text-xs font-semibold bg-white/5 text-zinc-500 border border-white/10 rounded-full px-3.5 py-1.5';
}

function emailStatusClass(e) {
  if (e.has_reply)            return 'text-[11px] font-semibold bg-violet-500/10 text-violet-300 border border-violet-500/25 rounded-full px-2.5 py-1';
  if (e.is_bounced)           return 'text-[11px] font-semibold bg-red-500/10 text-red-400 border border-red-500/25 rounded-full px-2.5 py-1';
  if (e.status === 'sent')    return 'text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-full px-2.5 py-1';
  if (e.status === 'pending') return 'text-[11px] font-semibold bg-sky-500/10 text-sky-400 border border-sky-500/25 rounded-full px-2.5 py-1';
  if (e.status === 'failed')  return 'text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full px-2.5 py-1';
  return 'text-[11px] font-semibold bg-white/5 text-zinc-400 border border-white/10 rounded-full px-2.5 py-1';
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