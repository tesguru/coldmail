@extends('layouts.app')
@section('title', 'Templates')
@section('content')

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">Email Templates</h1>
  <p class="text-gray-500 text-sm mt-1">Max 6 per type · supports initial + follow-up 1 to 20</p>
</div>

<!-- TYPE TABS -->
<div class="mb-6">
  <div class="flex gap-2 mb-2 flex-wrap">
    <button onclick="switchType('bulk_template')" id="tab-bulk_template"
            class="px-4 py-2 rounded-lg text-sm font-semibold border transition-all border-gray-200 bg-white text-gray-500 hover:text-gray-900">
      📧 Initial
    </button>
  </div>
  <div class="flex gap-2 flex-wrap">
    @for($i = 1; $i <= 20; $i++)
      <button onclick="switchType('followup_{{ $i }}')" id="tab-followup_{{ $i }}"
              class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all border-gray-200 bg-white text-gray-500 hover:text-gray-900">
        FU {{ $i }}
      </button>
    @endfor
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- LIST -->
  <div>
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-semibold text-gray-900" id="typeLabel">Templates</h2>
      <span id="templateCount" class="text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5">0/6</span>
    </div>
    <div id="templatesList">
      <div class="flex items-center justify-center py-12 text-gray-400">
        <div class="spinner mr-2"></div> Loading...
      </div>
    </div>
  </div>

  <!-- FORM -->
  <div class="bg-white border border-gray-200 rounded-xl p-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-semibold text-gray-900" id="formTitle">Create Template</h2>
      <button id="cancelEditBtn" onclick="cancelEdit()"
              class="hidden text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
        Cancel Edit
      </button>
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
        <input type="text" id="tplName" placeholder="e.g. Domain Outreach v1"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
        <select id="tplType" onchange="onTypeChange()"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500">
          <option value="bulk_template">📧 Initial Outbound</option>
          @for($i = 1; $i <= 20; $i++)
            <option value="followup_{{ $i }}">🔄 Follow-up {{ $i }}</option>
          @endfor
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
        <input type="text" id="tplSubject" placeholder="{company} — {domain} opportunity"
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 mb-2">
        <div class="flex flex-wrap gap-1.5">
          @foreach(['{company}', '{domain}', '{price}', '{firstName}', '{yourName}'] as $var)
            <button type="button" onclick="insertSubjectVar('{{ $var }}')"
                    class="text-xs px-2 py-1 rounded font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100">
              {{ $var }}
            </button>
          @endforeach
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Body *</label>
        <textarea id="tplBody" rows="10"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-200 font-mono mb-2"
                  placeholder="Hi {firstName},&#10;&#10;I noticed {company} doesn't own {domain}..."></textarea>
        <div class="flex flex-wrap gap-1.5">
          @foreach(['{company}', '{domain}', '{price}', '{firstName}', '{yourName}'] as $var)
            <button type="button" onclick="insertVar('{{ $var }}')"
                    class="text-xs px-2 py-1 rounded font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100">
              {{ $var }}
            </button>
          @endforeach
        </div>
      </div>

      <!-- SAMPLES -->
      <div class="bg-green-50 border border-green-200 rounded-lg p-3">
        <p class="text-xs font-semibold text-green-700 mb-2">Load sample:</p>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="loadSample('initial')"
                  class="text-xs px-3 py-1.5 rounded-lg bg-green-100 text-green-700 border border-green-200 hover:bg-green-200 font-medium">
            📧 Initial
          </button>
          <button type="button" onclick="loadSample('followup1')"
                  class="text-xs px-3 py-1.5 rounded-lg bg-green-100 text-green-700 border border-green-200 hover:bg-green-200 font-medium">
            🔄 Follow-up 1
          </button>
          <button type="button" onclick="loadSample('followup2')"
                  class="text-xs px-3 py-1.5 rounded-lg bg-green-100 text-green-700 border border-green-200 hover:bg-green-200 font-medium">
            🔄 Follow-up 2
          </button>
        </div>
      </div>

      <button type="button" id="saveBtn" onclick="saveTemplate()"
              class="w-full bg-blue-600 text-white rounded-lg py-3 text-sm font-semibold hover:bg-blue-700 transition">
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
    t.className = 'px-4 py-2 rounded-lg text-sm font-semibold border transition-all border-gray-200 bg-white text-gray-500 hover:text-gray-900';
    if (t.id.includes('followup')) t.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all border-gray-200 bg-white text-gray-500 hover:text-gray-900';
  });
  const active = document.getElementById(`tab-${type}`);
  if (active) active.className = active.className.replace('bg-white text-gray-500', 'bg-blue-600 text-white border-blue-600');
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

  const countEl = document.getElementById('templateCount');
  countEl.textContent = `${cnt}/6`;
  countEl.className = cnt >= 6
    ? 'text-xs font-semibold bg-red-50 text-red-700 border border-red-200 rounded-full px-2.5 py-0.5'
    : cnt >= 4
    ? 'text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full px-2.5 py-0.5'
    : 'text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2.5 py-0.5';

  if (!cnt) {
    el.innerHTML = `
      <div class="bg-white border border-gray-200 rounded-xl p-8 text-center">
        <p class="text-gray-400">No templates yet. Load a sample or create one →</p>
      </div>`;
    return;
  }

  el.innerHTML = res.templates.map((t, i) => {
    const safeBody    = t.body_template.replace(/\\/g, '\\\\').replace(/`/g, '\\`').replace(/\$/g, '\\$');
    const safeName    = t.name.replace(/`/g, '\\`');
    const safeSubject = t.subject_template.replace(/`/g, '\\`');

    return `
      <div class="bg-white border border-gray-200 rounded-xl p-4 mb-3">
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5">#${i+1}</span>
            <p class="font-semibold text-gray-900 text-sm">${t.name}</p>
          </div>
          <div class="flex gap-2">
            <button onclick="editTemplate(${t.id}, \`${safeName}\`, \`${t.type}\`, \`${safeSubject}\`, \`${safeBody}\`)"
                    class="text-xs px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 font-medium">
              Edit
            </button>
            <button onclick="deleteTemplate(${t.id})"
                    class="text-xs px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-medium">
              Delete
            </button>
          </div>
        </div>
        <div class="bg-gray-50 rounded-lg p-2.5 mb-2 border border-gray-100">
          <p class="text-xs text-gray-400 mb-0.5 font-medium">Subject:</p>
          <p class="text-sm text-gray-700">${t.subject_template}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-100">
          <p class="text-xs text-gray-400 mb-0.5 font-medium">Preview:</p>
          <p class="text-xs text-gray-500 font-mono whitespace-pre-wrap">${t.body_template.substring(0, 120)}${t.body_template.length > 120 ? '...' : ''}</p>
        </div>
      </div>`;
  }).join('');
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

function editTemplate(id, name, type, subject, body) {
  editingTemplateId = id;
  document.getElementById('tplName').value    = name;
  document.getElementById('tplType').value    = type;
  document.getElementById('tplSubject').value = subject;
  document.getElementById('tplBody').value    = body;
  document.getElementById('formTitle').textContent = 'Edit Template';
  document.getElementById('cancelEditBtn').classList.remove('hidden');
  const btn = document.getElementById('saveBtn');
  btn.textContent = 'Update Template';
  btn.className   = btn.className.replace('bg-blue-600 hover:bg-blue-700', 'bg-amber-500 hover:bg-amber-600');
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
  btn.className   = btn.className.replace('bg-amber-500 hover:bg-amber-600', 'bg-blue-600 hover:bg-blue-700');
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