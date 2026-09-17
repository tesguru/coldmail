@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')

<div class="mb-8 flex items-center justify-between">
  <div>
    <div class="flex items-center gap-2 text-[11px] font-semibold text-[#7c6ef7] uppercase tracking-widest mb-2">
      <span class="w-1.5 h-1.5 rounded-full bg-[#7c6ef7] inline-block"></span>
      Outbound
    </div>
    <h1 class="text-3xl font-bold text-white tracking-tight">Campaigns</h1>
    <p class="text-zinc-500 text-sm mt-1.5">Create and manage your outbound campaigns</p>
  </div>
  <button onclick="showModal()"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
    New Campaign
  </button>
</div>

<div id="campaignsList">
  <div class="flex items-center justify-center py-16 text-zinc-500">
    <div class="spinner mr-3"></div> Loading campaigns...
  </div>
</div>

<!-- CREATE MODAL -->
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
  <div class="bg-[#121218] rounded-3xl border border-white/[0.08] w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">

    <div class="flex items-center justify-between p-6 border-b border-white/[0.06] sticky top-0 bg-[#121218] rounded-t-3xl z-10">
      <div>
        <h2 class="text-lg font-bold text-white">New Campaign</h2>
        <p class="text-xs text-zinc-500 mt-0.5">Split recipients smartly across your sending accounts</p>
      </div>
      <button onclick="hideModal()"
              class="w-8 h-8 flex items-center justify-center text-zinc-500 hover:text-white hover:bg-white/5 rounded-xl transition text-lg leading-none">×</button>
    </div>

    <div class="p-6 space-y-6">

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Campaign Name <span class="text-zinc-600 font-normal">(must be unique)</span></label>
        <input type="text" id="campName" placeholder="e.g. LagosBusiness.com Outreach"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-zinc-300 mb-1.5">Domain</label>
          <input type="text" id="campDomain" placeholder="LagosBusiness.com"
                 class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
        </div>

        <div id="campPriceWrapper" class="hidden">
          <label class="block text-sm font-medium text-zinc-300 mb-1.5">
            Price
            <span class="ml-1.5 text-[11px] font-medium text-amber-400 bg-amber-500/10 border border-amber-500/25 rounded-full px-2 py-0.5">
              required by template
            </span>
          </label>
          <input type="text" id="campPrice" placeholder="$2,499"
                 class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Your Name</label>
        <input type="text" id="campYourName" placeholder="e.g. Emeka"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
      </div>

      <!-- ── FACEBOOK PAGES ── -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="block text-sm font-medium text-zinc-300">Facebook Pages</label>
            <p class="text-xs text-zinc-600">Optional — manual outreach when no email</p>
          </div>
          <button type="button" onclick="addLink('facebook')"
                  class="text-xs font-semibold text-[#a78bfa] bg-[#7c6ef7]/15 border border-[#7c6ef7]/25 rounded-full px-3.5 py-1.5 hover:bg-[#7c6ef7]/25 transition">
            + Add Page
          </button>
        </div>
        <div id="facebookLinks" class="space-y-2">
        </div>
        <p id="facebookEmpty" class="text-xs text-zinc-600 italic py-1.5">No Facebook pages added yet</p>
      </div>

      <!-- ── WEBSITES ── -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="block text-sm font-medium text-zinc-300">Websites / Contact Forms</label>
            <p class="text-xs text-zinc-600">Optional — visit contact form if no email reply</p>
          </div>
          <button type="button" onclick="addLink('website')"
                  class="text-xs font-semibold text-zinc-400 bg-white/5 border border-white/10 rounded-full px-3.5 py-1.5 hover:bg-white/10 transition">
            + Add Website
          </button>
        </div>
        <div id="websiteLinks" class="space-y-2">
        </div>
        <p id="websiteEmpty" class="text-xs text-zinc-600 italic py-1.5">No websites added yet</p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-sm font-medium text-zinc-300">Recipients <span class="text-zinc-600 font-normal">(one per line)</span></label>
          <span id="recipientCount" class="text-xs font-semibold bg-white/5 text-zinc-500 border border-white/10 rounded-full px-2.5 py-1">0 emails</span>
        </div>
        <textarea id="campRecipients" rows="6"
                  class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition font-mono"
                  placeholder="john@lagosrealty.com&#10;sarah@abujafirm.com&#10;mike@kanotraders.com"
                  oninput="countRecipients()"
                  onpaste="handlePaste(event)"></textarea>
        <div id="cleanFeedback" class="hidden mt-1.5 text-xs px-3 py-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/25"></div>
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-2">Gmail Accounts <span class="text-zinc-600 font-normal">(select sending accounts)</span></label>
        <div id="accountCheckboxes" class="space-y-2 bg-white/[0.03] rounded-2xl p-3.5 border border-white/[0.06]">
          <p class="text-sm text-zinc-500">Loading accounts...</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Split Mode</label>
        <p class="text-xs text-zinc-600 mb-2.5">Each recipient is sent by exactly one selected account — no double sends.</p>
        <div class="flex gap-3">
          <button onclick="setSplitMode('equal')" id="splitEqual"
                  class="flex-1 py-3 rounded-xl text-sm font-semibold border bg-[#7c6ef7] text-white border-[#7c6ef7] shadow-lg shadow-[#7c6ef7]/20 transition">
            Equal Split
          </button>
          <button onclick="setSplitMode('custom')" id="splitCustom"
                  class="flex-1 py-3 rounded-xl text-sm font-semibold border bg-[#17171f] text-zinc-500 border-white/10 hover:border-white/20 transition">
            Custom Split
          </button>
        </div>
      </div>

      <button onclick="previewSplit()"
              class="w-full py-3 rounded-xl text-sm font-semibold bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/25 transition">
        Preview Split Distribution
      </button>

      <div id="splitPreview" class="hidden"></div>

      <button onclick="createCampaign()" id="createBtn"
              class="w-full py-3.5 rounded-xl text-base font-bold bg-emerald-500 hover:bg-emerald-400 text-white transition">
        Create Campaign & Start Sending
      </button>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
let splitMode   = 'equal';
let allAccounts = [];

const SPLIT_ACTIVE    = 'flex-1 py-3 rounded-xl text-sm font-semibold border bg-[#7c6ef7] text-white border-[#7c6ef7] shadow-lg shadow-[#7c6ef7]/20 transition';
const SPLIT_INACTIVE  = 'flex-1 py-3 rounded-xl text-sm font-semibold border bg-[#17171f] text-zinc-500 border-white/10 hover:border-white/20 transition';

document.addEventListener('DOMContentLoaded', () => {
  loadCampaigns();
  loadAccountCheckboxes();
});

// ── Show modal ──
async function showModal() {
  document.getElementById('createModal').classList.remove('hidden');

  const res          = await apiGet('/api/templates/check-price-var?type=bulk_template');
  const priceWrapper = document.getElementById('campPriceWrapper');

  if (res.has_price_var) {
    priceWrapper.classList.remove('hidden');
  } else {
    priceWrapper.classList.add('hidden');
    document.getElementById('campPrice').value = '';
  }
}

// ── Hide modal and reset everything ──
function hideModal() {
  document.getElementById('createModal').classList.add('hidden');
  document.getElementById('facebookLinks').innerHTML = '';
  document.getElementById('websiteLinks').innerHTML  = '';
  document.getElementById('facebookEmpty').style.display = '';
  document.getElementById('websiteEmpty').style.display  = '';
}

// ── Add a link row dynamically ──
function addLink(type) {
  const container  = document.getElementById(type === 'facebook' ? 'facebookLinks' : 'websiteLinks');
  const emptyLabel = document.getElementById(type === 'facebook' ? 'facebookEmpty' : 'websiteEmpty');
  const id         = 'link-' + Date.now() + '-' + Math.random().toString(36).slice(2);
  const icon       = type === 'facebook' ? '📘' : '🌐';
  const placeholder = type === 'facebook'
    ? 'https://facebook.com/businesspage'
    : 'https://theirbusiness.com/contact';

  emptyLabel.style.display = 'none';

  const row       = document.createElement('div');
  row.id          = id;
  row.className   = 'flex items-center gap-2';
  row.innerHTML   = `
    <span class="text-lg flex-shrink-0">${icon}</span>
    <input type="url"
           placeholder="${placeholder}"
           data-link-type="${type}"
           class="flex-1 bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
    <button type="button" onclick="removeLink('${id}', '${type}')"
            class="text-zinc-600 hover:text-red-400 transition text-xl leading-none font-bold flex-shrink-0">
      ×
    </button>
  `;
  container.appendChild(row);
}

// ── Remove a link row ──
function removeLink(rowId, type) {
  const row = document.getElementById(rowId);
  if (row) row.remove();

  const container  = document.getElementById(type === 'facebook' ? 'facebookLinks' : 'websiteLinks');
  const emptyLabel = document.getElementById(type === 'facebook' ? 'facebookEmpty' : 'websiteEmpty');
  if (container.children.length === 0) {
    emptyLabel.style.display = '';
  }
}

// ── Collect all link values for a type ──
function getLinks(type) {
  return [...document.querySelectorAll(`input[data-link-type="${type}"]`)]
    .map(i => i.value.trim())
    .filter(v => v !== '');
}

// ── Paste handler ──
function handlePaste(event) {
  event.preventDefault();
  const pasted = (event.clipboardData || window.clipboardData).getData('text');
  const el     = document.getElementById('campRecipients');
  const start  = el.selectionStart;
  el.value     = el.value.substring(0, start) + pasted + el.value.substring(el.selectionEnd);
  cleanRecipients();
}

function cleanRecipients() {
  const raw    = document.getElementById('campRecipients').value;
  const tokens = raw.split(/[\n\r,;|\s\t]+/);
  const seen   = new Set();
  const valid  = [];
  let dupes    = 0;
  let invalid  = 0;

  tokens.forEach(token => {
    const email = token.replace(/^[^a-zA-Z0-9]+|[^a-zA-Z0-9]+$/g, '').toLowerCase();
    if (!email) return;
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      if (!seen.has(email)) { seen.add(email); valid.push(email); }
      else dupes++;
    } else {
      invalid++;
    }
  });

  document.getElementById('campRecipients').value = valid.join('\n');
  const countEl = document.getElementById('recipientCount');
  countEl.textContent = `${valid.length} emails`;
  countEl.className   = `text-xs font-semibold rounded-full px-2.5 py-1 border ${valid.length > 0 ? 'bg-[#7c6ef7]/15 text-[#a78bfa] border-[#7c6ef7]/25' : 'bg-red-500/10 text-red-400 border-red-500/25'}`;

  const parts = [`✅ ${valid.length} valid`];
  if (dupes > 0)   parts.push(`${dupes} duplicates removed`);
  if (invalid > 0) parts.push(`${invalid} invalid skipped`);

  const fb = document.getElementById('cleanFeedback');
  fb.textContent = parts.join(' · ');
  fb.classList.remove('hidden');
}

function countRecipients() {
  const val  = document.getElementById('campRecipients').value;
  const list = val.split(/\n/).map(e => e.trim()).filter(e => e.includes('@'));
  document.getElementById('recipientCount').textContent = `${list.length} emails`;
}

// ── Load campaigns list ──
async function loadCampaigns() {
  const res = await apiGet('/api/campaigns');
  const el  = document.getElementById('campaignsList');

  if (!res.campaigns?.length) {
    el.innerHTML = `
      <div class="bg-[#121218] border border-white/[0.06] rounded-3xl p-14 text-center">
        <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-[#7c6ef7]/10 border border-[#7c6ef7]/25 flex items-center justify-center">
          <svg class="w-7 h-7 text-[#7c6ef7]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <p class="text-white text-lg font-bold mb-1">No campaigns yet</p>
        <p class="text-zinc-500 text-sm mb-7">Create your first outbound campaign to start sending</p>
        <button onclick="showModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#7c6ef7] text-white text-sm font-semibold rounded-xl hover:bg-[#8d80f9] transition">
          + New Campaign
        </button>
      </div>`;
    return;
  }

  el.innerHTML = res.campaigns.map(c => `
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-6 mb-4 hover:border-[#7c6ef7]/40 transition cursor-pointer"
         onclick="window.location='/campaigns/${c.id}'">
      <div class="flex items-start justify-between mb-5">
        <div>
          <h3 class="font-bold text-white text-base">${c.name}</h3>
          <p class="text-sm text-zinc-500 mt-0.5">${c.domain} · ${c.price}</p>
        </div>
        <div class="flex items-center gap-2.5">
          ${statusBadge(c.status)}
          <button onclick="event.stopPropagation(); deleteCampaign(${c.id})"
                  class="text-xs px-3 py-1.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/25 hover:bg-red-500/20 font-medium transition">
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        ${statBox('Total',      c.total_emails,    'text-sky-400')}
        ${statBox('Sent',       c.sent_count,      'text-emerald-400')}
        ${statBox('Replied',    c.replied_count,   'text-[#a78bfa]')}
        ${statBox('Follow-ups', c.follow_up_count, 'text-amber-400')}
        ${statBox('Bounced',    c.bounce_count,    'text-red-400')}
      </div>

      ${c.pending_count > 0 ? `
        <div class="mt-4 flex items-center gap-2.5 text-sm text-[#a78bfa] bg-[#7c6ef7]/10 rounded-xl px-4 py-2.5 border border-[#7c6ef7]/25">
          <div class="spinner"></div>
          ${c.pending_count} emails queued and sending
        </div>` : ''}
    </div>
  `).join('');
}

function statBox(label, value, color) {
  return `
    <div class="text-center bg-white/[0.03] rounded-xl p-3.5 border border-white/[0.05]">
      <p class="text-2xl font-bold ${color}">${value}</p>
      <p class="text-[11px] text-zinc-500 mt-1 font-medium">${label}</p>
    </div>`;
}

function statusBadge(status) {
  const map = {
    active:    'bg-emerald-500/10 text-emerald-400 border-emerald-500/25',
    paused:    'bg-amber-500/10 text-amber-400 border-amber-500/25',
    completed: 'bg-sky-500/10 text-sky-400 border-sky-500/25',
  };
  return `<span class="text-[11px] font-semibold border rounded-full px-3 py-1 ${map[status] || 'bg-white/5 text-zinc-500 border-white/10'}">${status}</span>`;
}

// ── Load Gmail account checkboxes ──
async function loadAccountCheckboxes() {
  const res   = await apiGet('/api/gmail-accounts');
  allAccounts = res.accounts || [];
  const el    = document.getElementById('accountCheckboxes');

  if (!allAccounts.length) {
    el.innerHTML = `<p class="text-sm text-red-400">No Gmail accounts connected. <a href="{{ route('accounts.index') }}" class="underline">Add one first</a></p>`;
    return;
  }

  el.innerHTML = allAccounts.map(a => `
    <label class="flex items-center gap-3 bg-[#0e0e14] p-3.5 rounded-xl border border-white/[0.06] cursor-pointer hover:border-[#7c6ef7]/40 transition">
      <input type="checkbox" name="gmail_accounts" value="${a.id}"
             class="w-4 h-4 rounded accent-[#7c6ef7] focus:ring-[#7c6ef7]/30">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-zinc-200">${a.email}</p>
        <p class="text-xs text-zinc-500 mt-0.5">
          ${a.remaining} remaining today
          · ${a.token_status === 'valid' ? '✅ OAuth ready' : '❌ OAuth needed'}
          · ${a.has_script ? '✅ Script ready' : '⚠️ No script'}
        </p>
      </div>
    </label>
  `).join('');
}

function setSplitMode(mode) {
  splitMode = mode;
  const eq = document.getElementById('splitEqual');
  const cu = document.getElementById('splitCustom');

  if (mode === 'equal') {
    eq.className = SPLIT_ACTIVE;
    cu.className = SPLIT_INACTIVE;
  } else {
    cu.className = SPLIT_ACTIVE;
    eq.className = SPLIT_INACTIVE;
  }
}

function getSelectedAccounts() {
  return [...document.querySelectorAll('input[name="gmail_accounts"]:checked')].map(i => parseInt(i.value));
}

async function previewSplit() {
  const recipients = document.getElementById('campRecipients').value;
  const accounts   = getSelectedAccounts();

  if (!recipients || !accounts.length) {
    toast('Error', 'Add recipients and select accounts first', 'error');
    return;
  }

  const res = await apiPost('/api/campaigns/preview-split', {
    recipients, gmail_accounts: accounts, split_mode: splitMode, custom_splits: {},
  });

  const el = document.getElementById('splitPreview');
  if (res.success) {
    el.classList.remove('hidden');
    el.innerHTML = `
      <div class="bg-[#0e0e14] border border-white/[0.08] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <p class="font-bold text-white text-sm">📊 Split preview</p>
          <span class="text-xs font-semibold text-zinc-500">${res.total} emails · ~${res.total_time}</span>
        </div>
        ${res.preview.map(p => `
          <div class="flex items-center justify-between py-2.5 border-b border-white/[0.05] last:border-0">
            <span class="text-sm text-zinc-300 font-medium truncate mr-2">${p.account}</span>
            <span class="text-xs font-bold bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25 rounded-full px-3 py-1">${p.count} emails</span>
          </div>
        `).join('')}
      </div>`;
  } else {
    toast('Error', res.error, 'error');
  }
}

// ── Create campaign ──
async function createCampaign() {
  const name         = document.getElementById('campName').value.trim();
  const domain       = document.getElementById('campDomain').value.trim();
  const price        = document.getElementById('campPrice').value.trim();
  const yourName     = document.getElementById('campYourName').value.trim();
  const recipients   = document.getElementById('campRecipients').value;
  const accounts     = getSelectedAccounts();
  const facebookLinks = getLinks('facebook');
  const websiteLinks  = getLinks('website');

  const priceRequired = !document.getElementById('campPriceWrapper').classList.contains('hidden');

  if (!name || !domain || !yourName || !recipients || (priceRequired && !price)) {
    toast('Error', 'Please fill all required fields', 'error'); return;
  }
  if (!accounts.length) {
    toast('Error', 'Select at least one Gmail account', 'error'); return;
  }

  const btn = document.getElementById('createBtn');
  btn.textContent = 'Creating...';
  btn.disabled    = true;

  const res = await apiPost('/api/campaigns', {
    name,
    domain,
    price,
    your_name:       yourName,
    facebook_links:  facebookLinks,
    website_links:   websiteLinks,
    recipients,
    gmail_accounts:  accounts,
    split_mode:      splitMode,
    custom_splits:   {},
  });

  btn.textContent = 'Create Campaign & Start Sending';
  btn.disabled    = false;

  if (res.success) {
    toast('Created! 🎉', res.message, 'success');
    hideModal();
    loadCampaigns();
  } else {
    toast('Error', res.error, 'error');
  }
}

async function deleteCampaign(id) {
  if (!confirm('Delete this campaign?')) return;
  const res = await apiDelete(`/api/campaigns/${id}`);
  if (res.success) { toast('Deleted', 'Campaign deleted', 'success'); loadCampaigns(); }
}
</script>
@endsection