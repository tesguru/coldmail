@extends('layouts.app')
@section('title', 'Templates')
@section('content')

<div class="mb-8">
  <div class="flex items-center gap-2 text-[11px] font-semibold text-[#7c6ef7] uppercase tracking-widest mb-2">
    <span class="w-1.5 h-1.5 rounded-full bg-[#7c6ef7] inline-block"></span>
    Messaging
  </div>
  <h1 class="text-3xl font-bold text-white tracking-tight">Email Templates</h1>
  <p class="text-zinc-500 text-sm mt-1.5">Max 6 per type · supports initial + follow-up 1 to 20</p>
</div>

<!-- TYPE TABS -->
<div class="mb-6 p-2 bg-[#121218] border border-white/[0.06] rounded-2xl w-full inline-block">
  <div class="flex gap-2 mb-2 flex-wrap">
    <button onclick="switchType('bulk_template')" id="tab-bulk_template"
            class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all bg-[#7c6ef7] text-white border-[#7c6ef7]">
      📧 Initial
    </button>
  </div>
  <div class="flex gap-1.5 flex-wrap">
    @for($i = 1; $i <= 20; $i++)
      <button onclick="switchType('followup_{{ $i }}')" id="tab-followup_{{ $i }}"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all bg-white/[0.03] text-zinc-500 border-white/10 hover:text-zinc-200 hover:border-white/20">
        FU {{ $i }}
      </button>
    @endfor
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- LIST -->
  <div>
    <div class="flex items-center justify-between mb-3.5">
      <h2 class="font-bold text-white" id="typeLabel">Templates</h2>
      <span id="templateCount" class="text-xs font-semibold bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25 rounded-full px-2.5 py-1">0/6</span>
    </div>
    <div id="templatesList">
      <div class="flex items-center justify-center py-12 text-zinc-500">
        <div class="spinner mr-2"></div> Loading...
      </div>
    </div>
  </div>

  <!-- FORM -->
  <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-7">
    <div class="flex items-center justify-between mb-6">
      <h2 class="font-bold text-white" id="formTitle">Create Template</h2>
      <button id="cancelEditBtn" onclick="cancelEdit()"
              class="hidden text-xs px-3.5 py-1.5 rounded-xl bg-white/5 text-zinc-400 hover:bg-white/10 font-medium">
        Cancel Edit
      </button>
    </div>

    <div class="space-y-5">
      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Name *</label>
        <input type="text" id="tplName" placeholder="e.g. Domain Outreach v1"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition">
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Type *</label>
        <select id="tplType" onchange="onTypeChange()"
                class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 outline-none focus:border-[#7c6ef7]/60 transition">
          <option value="bulk_template">📧 Initial Outbound</option>
          @for($i = 1; $i <= 20; $i++)
            <option value="followup_{{ $i }}">🔄 Follow-up {{ $i }}</option>
          @endfor
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Subject *</label>
        <input type="text" id="tplSubject" placeholder="{company} — {domain} opportunity"
               class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition mb-2.5">
        <div class="flex flex-wrap gap-1.5">
          @foreach(['{company}', '{domain}', '{price}', '{firstName}', '{yourName}'] as $var)
            <button type="button" onclick="insertSubjectVar('{{ $var }}')"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-mono font-semibold bg-[#7c6ef7]/10 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/20 transition">
              {{ $var }}
            </button>
          @endforeach
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Body *</label>
        <textarea id="tplBody" rows="10"
                  class="w-full bg-[#0e0e14] border border-white/10 rounded-xl px-3.5 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 outline-none focus:border-[#7c6ef7]/60 focus:ring-1 focus:ring-[#7c6ef7]/30 transition font-mono mb-2.5"
                  placeholder="Hi {firstName},&#10;&#10;I noticed {company} doesn't own {domain}..."></textarea>
        <div class="flex flex-wrap gap-1.5">
          @foreach(['{company}', '{domain}', '{price}', '{firstName}', '{yourName}'] as $var)
            <button type="button" onclick="insertVar('{{ $var }}')"
                    class="text-xs px-2.5 py-1.5 rounded-lg font-mono font-semibold bg-[#7c6ef7]/10 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/20 transition">
              {{ $var }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- SAMPLES -->
      <div class="bg-emerald-500/[0.06] border border-emerald-500/20 rounded-xl p-4">
        <p class="text-xs font-semibold text-emerald-400 mb-2.5">Load sample:</p>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="loadSample('initial')"
                  class="text-xs px-3.5 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition font-medium">
            📧 Initial
          </button>
          <button type="button" onclick="loadSample('followup1')"
                  class="text-xs px-3.5 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition font-medium">
            🔄 Follow-up 1
          </button>
          <button type="button" onclick="loadSample('followup2')"
                  class="text-xs px-3.5 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition font-medium">
            🔄 Follow-up 2
          </button>
        </div>
      </div>

      <button type="button" id="saveBtn" onclick="saveTemplate()"
              class="w-full bg-[#7c6ef7] hover:bg-[#8d80f9] text-white rounded-xl py-3.5 text-sm font-bold transition">
        Save Template
      </button>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
let currentType       = 'bulk_template';
let editingTemplateId = null;
let templatesCache    = {};

const TAB_ACTIVE        = 'bg-[#7c6ef7] text-white border-[#7c6ef7]';
const TAB_INACTIVE_LG   = 'px-4 py-2 rounded-xl text-sm font-semibold border transition-all bg-white/[0.03] text-zinc-500 border-white/10 hover:text-zinc-200 hover:border-white/20';
const TAB_INACTIVE_SM   = 'px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all bg-white/[0.03] text-zinc-500 border-white/10 hover:text-zinc-200 hover:border-white/20';
const SAVE_CREATE_CLASS = 'w-full bg-[#7c6ef7] hover:bg-[#8d80f9] text-white rounded-xl py-3.5 text-sm font-bold transition';
const SAVE_EDIT_CLASS   = 'w-full bg-amber-500 hover:bg-amber-400 text-white rounded-xl py-3.5 text-sm font-bold transition';

const samples = {
  initial: {
    subject: '{company} — {domain} domain opportunity',
    body: `Hi {firstName},\n\nI came across {company} and noticed that {domain} is currently available.\n\nThis domain would be a perfect match for your business — easy to remember, professional, and directly relevant to what you do.\n\nI'm offering it at {price}. Would you be open to a quick conversation?\n\nBest regards,\n{yourName}`
  },
  followup1: {
    subject: 'Re: {domain}',
    body: `Hi {firstName},\n\nJust following up on my previous email about {domain}.\n\nI wanted to make sure it didn't get lost in your inbox. This domain is still available and I believe it could add real value to {company}.\n\nHappy to answer any questions.\n\nBest,\n{yourName}`
  },
  followup2: {
    subject: 'Re: {domain}',
    body: `Hi {firstName},\n\nI'll keep this brief — {domain} is still available.\n\nA domain like this can significantly improve how customers find {company} online. Would {price} work for you?\n\nBest,\n{yourName}`
  },
};

document.addEventListener('DOMContentLoaded', () => switchType('bulk_template'));

function switchType(type) {
  currentType = type;
  document.querySelectorAll('[id^="tab-"]').forEach(t => {
    t.className = t.id.includes('followup') ? TAB_INACTIVE_SM : TAB_INACTIVE_LG;
  });
  const active = document.getElementById(`tab-${type}`);
  if (active) active.className = TAB_ACTIVE;
  document.getElementById('typeLabel').textContent = type === 'bulk_template' ? 'Initial Outbound Templates' : `Follow-up ${type.replace('followup_', '')} Templates`;
  document.getElementById('tplType').value = type;
  loadTemplates(type);
}

function onTypeChange() {
  currentType = document.getElementById('tplType').value;
  loadTemplates(currentType);
}

function loadSample(key) {
  const s = samples[key];
  if (!s) return;
  document.getElementById('tplSubject').value = s.subject;
  document.getElementById('tplBody').value    = s.body;
  toast('Loaded', 'Sample loaded into form', 'success');
}

async function loadTemplates(type) {
  const res = await apiGet(`/api/templates?type=${type}`);
  const el  = document.getElementById('templatesList');
  const cnt = res.templates?.length || 0;

  templatesCache = {};
  res.templates?.forEach(t => templatesCache[t.id] = t);

  const countEl = document.getElementById('templateCount');
  countEl.textContent = `${cnt}/6`;
  countEl.className = cnt >= 6
    ? 'text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/25 rounded-full px-2.5 py-1'
    : cnt >= 4
    ? 'text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/25 rounded-full px-2.5 py-1'
    : 'text-xs font-semibold bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25 rounded-full px-2.5 py-1';

  if (!cnt) {
    el.innerHTML = `
      <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-10 text-center">
        <p class="text-zinc-500">No templates yet. Load a sample or create one →</p>
      </div>`;
    return;
  }

  el.innerHTML = res.templates.map((t, i) => `
    <div class="bg-[#121218] border border-white/[0.06] rounded-2xl p-5 mb-3">
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2.5">
          <span class="text-xs font-bold bg-[#7c6ef7]/15 text-[#a78bfa] border border-[#7c6ef7]/25 rounded-full px-2 py-0.5">#${i+1}</span>
          <p class="font-bold text-white text-sm">${t.name}</p>
        </div>
        <div class="flex gap-2">
          <button onclick="editTemplate(${t.id})"
                  class="text-xs px-3 py-1.5 rounded-xl bg-[#7c6ef7]/10 text-[#a78bfa] border border-[#7c6ef7]/25 hover:bg-[#7c6ef7]/20 font-medium transition">
            Edit
          </button>
          <button onclick="deleteTemplate(${t.id})"
                  class="text-xs px-3 py-1.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/25 hover:bg-red-500/20 font-medium transition">
            Delete
          </button>
        </div>
      </div>
      <div class="bg-[#0e0e14] rounded-xl p-3 mb-2.5 border border-white/[0.05]">
        <p class="text-xs text-zinc-600 mb-1 font-medium">Subject:</p>
        <p class="text-sm text-zinc-300">${t.subject_template}</p>
      </div>
      <div class="bg-[#0e0e14] rounded-xl p-3 border border-white/[0.05]">
        <p class="text-xs text-zinc-600 mb-1 font-medium">Preview:</p>
        <p class="text-xs text-zinc-500 font-mono whitespace-pre-wrap">${t.body_template.substring(0, 120)}${t.body_template.length > 120 ? '...' : ''}</p>
      </div>
    </div>`
  ).join('');
}

function insertVar(variable) {
  const el = document.getElementById('tplBody');
  const s  = el.selectionStart;
  el.value = el.value.substring(0, s) + variable + el.value.substring(el.selectionEnd);
  el.selectionStart = el.selectionEnd = s + variable.length;
  el.focus();
}

function insertSubjectVar(variable) {
  const el = document.getElementById('tplSubject');
  const s  = el.selectionStart;
  el.value = el.value.substring(0, s) + variable + el.value.substring(el.selectionEnd);
  el.selectionStart = el.selectionEnd = s + variable.length;
  el.focus();
}

function editTemplate(id) {
  const t = templatesCache[id];
  if (!t) return;

  editingTemplateId = id;
  document.getElementById('tplName').value    = t.name;
  document.getElementById('tplType').value    = t.type;
  document.getElementById('tplSubject').value = t.subject_template;
  document.getElementById('tplBody').value    = t.body_template;
  document.getElementById('formTitle').textContent = 'Edit Template';
  document.getElementById('cancelEditBtn').classList.remove('hidden');
  const btn = document.getElementById('saveBtn');
  btn.textContent = 'Update Template';
  btn.className   = SAVE_EDIT_CLASS;
  btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelEdit() {
  editingTemplateId = null;
  document.getElementById('tplName').value    = '';
  document.getElementById('tplSubject').value = '';
  document.getElementById('tplBody').value    = '';
  document.getElementById('formTitle').textContent = 'Create Template';
  document.getElementById('cancelEditBtn').classList.add('hidden');
  const btn = document.getElementById('saveBtn');
  btn.textContent = 'Save Template';
  btn.className   = SAVE_CREATE_CLASS;
}

async function saveTemplate() {
  const name    = document.getElementById('tplName').value.trim();
  const type    = document.getElementById('tplType').value;
  const subject = document.getElementById('tplSubject').value.trim();
  const body    = document.getElementById('tplBody').value.trim();

  if (!name || !body) { toast('Error', 'Name and body are required', 'error'); return; }
  if (type === 'bulk_template' && !subject) { toast('Error', 'Subject required for initial emails', 'error'); return; }

  const btn = document.getElementById('saveBtn');
  btn.textContent = 'Saving...';
  btn.disabled    = true;

  const url = editingTemplateId ? `/api/templates/${editingTemplateId}` : '/api/templates';
  const res = editingTemplateId
    ? await apiPut(url, { name, subject_template: subject || 'Re: {domain}', body_template: body })
    : await apiPost(url, { name, type, subject_template: subject || 'Re: {domain}', body_template: body });

  btn.disabled    = false;
  btn.textContent = editingTemplateId ? 'Update Template' : 'Save Template';
  btn.className   = editingTemplateId ? SAVE_EDIT_CLASS : SAVE_CREATE_CLASS;

  if (res.success) {
    toast('Saved!', editingTemplateId ? 'Template updated' : 'Template saved', 'success');
    cancelEdit();
    loadTemplates(type);
  } else {
    toast('Error', res.error || res.message, 'error');
  }
}

async function deleteTemplate(id) {
  if (!confirm('Delete this template?')) return;
  const res = await apiDelete(`/api/templates/${id}`);
  if (res.success) { toast('Deleted', 'Template deleted', 'success'); loadTemplates(currentType); }
}
</script>
@endsection