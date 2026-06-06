@extends('layouts.app')
@section('title', 'Campaigns')
@section('content')

<div class="mb-6 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>
    <p class="text-gray-500 text-sm mt-1">Create and manage your outbound campaigns</p>
  </div>
  <button onclick="showModal()"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
    + New Campaign
  </button>
</div>

<div id="campaignsList">
  <div class="flex items-center justify-center py-16 text-gray-400">
    <div class="spinner mr-3"></div> Loading campaigns...
  </div>
</div>

<!-- CREATE MODAL -->
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
  <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl max-h-[90vh] overflow-y-auto">

    <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white rounded-t-2xl">
      <h2 class="text-lg font-semibold text-gray-900">New Campaign</h2>
      <button onclick="hideModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
    </div>

    <div class="p-6 space-y-5">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Name * <span class="text-gray-400 font-normal">(must be unique)</span></label>
        <input type="text" id="campName" placeholder="e.g. LagosBusiness.com Outreach"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Domain *</label>
          <input type="text" id="campDomain" placeholder="LagosBusiness.com"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
        </div>

        <div id="campPriceWrapper" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Price *
            <span class="ml-1 text-xs font-normal text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-2 py-0.5">
              required by template
            </span>
          </label>
          <input type="text" id="campPrice" placeholder="$2,499"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
        <input type="text" id="campYourName" placeholder="e.g. Emeka"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
      </div>

      <!-- ── FACEBOOK PAGES — add as many as you want ── -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Facebook Pages</label>
            <p class="text-xs text-gray-400">Optional — for manual outreach when no email</p>
          </div>
          <button type="button" onclick="addLink('facebook')"
                  class="text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-full px-3 py-1 hover:bg-blue-100 transition">
            + Add Page
          </button>
        </div>
        <div id="facebookLinks" class="space-y-2">
          <!-- rows injected here by addLink() -->
        </div>
        <p id="facebookEmpty" class="text-xs text-gray-400 italic py-1">No Facebook pages added yet</p>
      </div>

      <!-- ── WEBSITES — add as many as you want ── -->
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Websites / Contact Forms</label>
            <p class="text-xs text-gray-400">Optional — visit contact form if no email reply</p>
          </div>
          <button type="button" onclick="addLink('website')"
                  class="text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 rounded-full px-3 py-1 hover:bg-gray-200 transition">
            + Add Website
          </button>
        </div>
        <div id="websiteLinks" class="space-y-2">
          <!-- rows injected here by addLink() -->
        </div>
        <p id="websiteEmpty" class="text-xs text-gray-400 italic py-1">No websites added yet</p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-medium text-gray-700">Recipients * <span class="text-gray-400 font-normal">(one per line)</span></label>
          <span id="recipientCount" class="text-xs font-semibold bg-gray-100 text-gray-600 rounded-full px-2.5 py-0.5">0 emails</span>
        </div>
        <textarea id="campRecipients" rows="6"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 font-mono"
                  placeholder="john@lagosrealty.com&#10;sarah@abujafirm.com&#10;mike@kanotraders.com"
                  oninput="countRecipients()"
                  onpaste="handlePaste(event)"></textarea>
        <div id="cleanFeedback" class="hidden mt-1.5 text-xs px-3 py-2 rounded-lg bg-green-50 text-green-700 border border-green-200"></div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Gmail Accounts * <span class="text-gray-400 font-normal">(select sending accounts)</span></label>
        <div id="accountCheckboxes" class="space-y-2 bg-gray-50 rounded-xl p-3 border border-gray-200">
          <p class="text-sm text-gray-400">Loading accounts...</p>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Split Mode *</label>
        <div class="flex gap-3">
          <button onclick="setSplitMode('equal')" id="splitEqual"
                  class="flex-1 py-2.5 rounded-lg text-sm font-semibold border-2 border-blue-600 bg-blue-50 text-blue-700">
            Equal Split
          </button>
          <button onclick="setSplitMode('custom')" id="splitCustom"
                  class="flex-1 py-2.5 rounded-lg text-sm font-semibold border-2 border-gray-200 bg-white text-gray-500">
            Custom Split
          </button>
        </div>
      </div>

      <button onclick="previewSplit()"
              class="w-full py-2.5 rounded-lg text-sm font-semibold bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition">
        Preview Split Distribution
      </button>

      <div id="splitPreview" class="hidden"></div>

      <button onclick="createCampaign()" id="createBtn"
              class="w-full py-3 rounded-lg text-base font-semibold bg-green-600 text-white hover:bg-green-700 transition">
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
  // Clear link rows
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

  // Hide the "none added yet" label
  emptyLabel.style.display = 'none';

  const row       = document.createElement('div');
  row.id          = id;
  row.className   = 'flex items-center gap-2';
  row.innerHTML   = `
    <span class="text-lg flex-shrink-0">${icon}</span>
    <input type="url"
           placeholder="${placeholder}"
           data-link-type="${type}"
           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
    <button type="button" onclick="removeLink('${id}', '${type}')"
            class="text-gray-400 hover:text-red-500 transition text-xl leading-none font-bold flex-shrink-0">
      ×
    </button>
  `;
  container.appendChild(row);
}

// ── Remove a link row ──
function removeLink(rowId, type) {
  const row = document.getElementById(rowId);
  if (row) row.remove();

  // If no more rows, show the empty label again
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
  countEl.className   = `text-xs font-semibold rounded-full px-2.5 py-0.5 ${valid.length > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'}`;

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
      <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <p class="text-gray-900 text-lg font-semibold mb-1">No campaigns yet</p>
        <p class="text-gray-400 text-sm mb-6">Create your first outbound campaign to start sending</p>
        <button onclick="showModal()"
                class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
          + New Campaign
        </button>
      </div>`;
    return;
  }

  el.innerHTML = res.campaigns.map(c => `
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4 hover:border-blue-300 transition cursor-pointer"
         onclick="window.location='/campaigns/${c.id}'">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="font-semibold text-gray-900 text-base">${c.name}</h3>
          <p class="text-sm text-gray-500 mt-0.5">${c.domain} · ${c.price}</p>
        </div>
        <div class="flex items-center gap-2">
          ${statusBadge(c.status)}
          <button onclick="event.stopPropagation(); deleteCampaign(${c.id})"
                  class="text-xs px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-medium">
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        ${statBox('Total',      c.total_emails,    'text-blue-600')}
        ${statBox('Sent',       c.sent_count,      'text-green-600')}
        ${statBox('Replied',    c.replied_count,   'text-purple-600')}
        ${statBox('Follow-ups', c.follow_up_count, 'text-amber-600')}
        ${statBox('Bounced',    c.bounce_count,    'text-red-500')}
      </div>

      ${c.pending_count > 0 ? `
        <div class="mt-3 flex items-center gap-2 text-sm text-blue-700 bg-blue-50 rounded-lg px-3 py-2 border border-blue-200">
          <div class="spinner"></div>
          ${c.pending_count} emails queued and sending
        </div>` : ''}
    </div>
  `).join('');
}

function statBox(label, value, color) {
  return `
    <div class="text-center bg-gray-50 rounded-lg p-3 border border-gray-100">
      <p class="text-xl font-bold ${color}">${value}</p>
      <p class="text-xs text-gray-400 mt-0.5">${label}</p>
    </div>`;
}

function statusBadge(status) {
  const map = {
    active:    'bg-green-50 text-green-700 border-green-200',
    paused:    'bg-yellow-50 text-yellow-700 border-yellow-200',
    completed: 'bg-blue-50 text-blue-700 border-blue-200',
  };
  return `<span class="text-xs border rounded-full px-2.5 py-0.5 font-semibold ${map[status] || 'bg-gray-100 text-gray-600 border-gray-200'}">${status}</span>`;
}

// ── Load Gmail account checkboxes ──
async function loadAccountCheckboxes() {
  const res   = await apiGet('/api/gmail-accounts');
  allAccounts = res.accounts || [];
  const el    = document.getElementById('accountCheckboxes');

  if (!allAccounts.length) {
    el.innerHTML = `<p class="text-sm text-red-600">No Gmail accounts connected. <a href="{{ route('accounts.index') }}" class="underline">Add one first</a></p>`;
    return;
  }

  el.innerHTML = allAccounts.map(a => `
    <label class="flex items-center gap-3 bg-white p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-blue-300 transition">
      <input type="checkbox" name="gmail_accounts" value="${a.id}"
             class="w-4 h-4 rounded accent-blue-600">
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900">${a.email}</p>
        <p class="text-xs text-gray-400">
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
    eq.className = eq.className.replace('border-gray-200 bg-white text-gray-500', 'border-blue-600 bg-blue-50 text-blue-700');
    cu.className = cu.className.replace('border-blue-600 bg-blue-50 text-blue-700', 'border-gray-200 bg-white text-gray-500');
  } else {
    cu.className = cu.className.replace('border-gray-200 bg-white text-gray-500', 'border-blue-600 bg-blue-50 text-blue-700');
    eq.className = eq.className.replace('border-blue-600 bg-blue-50 text-blue-700', 'border-gray-200 bg-white text-gray-500');
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
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <p class="font-semibold text-gray-900 text-sm mb-3">
          Split preview — ${res.total} emails · ~${res.total_time}
        </p>
        ${res.preview.map(p => `
          <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0">
            <span class="text-sm text-gray-700">${p.account}</span>
            <span class="text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5">${p.count} emails</span>
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
  const facebookLinks = getLinks('facebook');  // ← collect all Facebook URLs
  const websiteLinks  = getLinks('website');   // ← collect all Website URLs

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
    facebook_links:  facebookLinks,  // ← sent to backend
    website_links:   websiteLinks,   // ← sent to backend
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