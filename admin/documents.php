<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur', 'bibliothecaire']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documents — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
  .modal-overlay { background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
  .input-field {
    width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem;
    padding: 0.65rem 1rem; font-size: 0.875rem; transition: all 0.2s; background: #f8fafc;
  }
  .input-field:focus { outline: none; border-color: #f59e0b; background: white; }
  .btn-primary {
    background: linear-gradient(135deg, #f59e0b, #d97706); color: white;
    font-weight: 600; border-radius: 0.75rem; padding: 0.65rem 1.25rem;
    font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s;
  }
  .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(245,158,11,0.35); }
  .label { display: block; font-size: 0.7rem; font-weight: 600; color: #64748b;
           text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.35rem; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="playfair text-3xl font-bold text-slate-800">Gestion des documents</h1>
        <p class="text-slate-500 mt-1" id="doc-count">Chargement…</p>
      </div>
      <button onclick="openModal('add')"
        class="btn-primary flex items-center gap-2 self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Ajouter un document
      </button>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
      <input type="text" id="search-input" placeholder="Rechercher par titre, auteur, mot-clé…"
        oninput="filterDocs()" class="input-field flex-1">
      <select id="filter-type" onchange="filterDocs()" class="input-field sm:w-44">
        <option value="">Tous les types</option>
        <option value="livre">Livres</option>
        <option value="revue">Revues</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Document</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Auteur(s)</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Éditeur</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Parution</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Stock</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="docs-tbody">
            <tr><td colspan="6" class="text-center py-12 text-slate-400">Chargement…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- ═══════════════════════════ MODAL ADD/EDIT ═══════════════════════════ -->
<div id="modal-doc" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-100 flex-shrink-0">
      <h3 class="font-bold text-slate-800 text-lg" id="modal-title">Ajouter un document</h3>
      <button onclick="closeModal()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Body scrollable -->
    <div class="overflow-y-auto flex-1 p-6 space-y-5">

      <!-- Type -->
      <div>
        <label class="label">Type de document</label>
        <div class="flex gap-3">
          <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition"
                 id="type-livre-label">
            <input type="radio" name="type_doc" value="livre" checked onchange="toggleType()"
              class="accent-amber-500">
            <span class="text-sm font-medium text-slate-700">📖 Livre</span>
          </label>
          <label class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition"
                 id="type-revue-label">
            <input type="radio" name="type_doc" value="revue" onchange="toggleType()"
              class="accent-amber-500">
            <span class="text-sm font-medium text-slate-700">📰 Revue</span>
          </label>
        </div>
      </div>

      <!-- Titre -->
      <div>
        <label class="label">Titre *</label>
        <input type="text" id="f-titre" class="input-field" placeholder="Titre du document">
      </div>

      <!-- Auteurs + Editeur -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Auteur(s)</label>
          <select id="f-auteurs" multiple class="input-field" style="height:90px"></select>
          <p class="text-xs text-slate-400 mt-1">Ctrl+clic pour plusieurs</p>
        </div>
        <div>
          <label class="label">Maison d'édition</label>
          <select id="f-edition" class="input-field">
            <option value="">Sélectionner…</option>
          </select>
        </div>
      </div>

      <!-- Date + Exemplaires -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Date de parution</label>
          <input type="date" id="f-date" class="input-field">
        </div>
        <div>
          <label class="label">Nb exemplaires *</label>
          <input type="number" id="f-exemplaires" class="input-field" min="1" value="1">
        </div>
      </div>

      <!-- Mots clés -->
      <div>
        <label class="label">Mots-clés</label>
        <input type="text" id="f-mots" class="input-field" placeholder="séparés par des virgules">
      </div>

      <!-- Champs LIVRE -->
      <div id="champs-livre" class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">ISBN</label>
          <input type="text" id="f-isbn" class="input-field" placeholder="978-…">
        </div>
        <div>
          <label class="label">Genre</label>
          <input type="text" id="f-genre" class="input-field" placeholder="Informatique…">
        </div>
      </div>

      <!-- Champs REVUE -->
      <div id="champs-revue" class="grid grid-cols-2 gap-4" style="display:none">
        <div>
          <label class="label">Périodicité</label>
          <select id="f-periodicite" class="input-field">
            <option>Mensuelle</option>
            <option>Bimestrielle</option>
            <option>Trimestrielle</option>
            <option>Semestrielle</option>
            <option>Annuelle</option>
          </select>
        </div>
        <div>
          <label class="label">ISSN</label>
          <input type="text" id="f-issn" class="input-field" placeholder="XXXX-XXXX">
        </div>
        <div>
          <label class="label">Date d'abonnement</label>
          <input type="date" id="f-date-abo" class="input-field">
        </div>
        <div>
          <label class="label">Montant abonnement (DT)</label>
          <input type="number" id="f-montant-abo" class="input-field" step="0.01" min="0" value="0">
        </div>
      </div>

      <!-- Erreur -->
      <div id="form-error"
        class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
      </div>

    </div>

    <!-- Footer -->
    <div class="p-6 pt-0 flex gap-3 flex-shrink-0 border-t border-slate-100 mt-2">
      <button onclick="closeModal()"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="saveDoc()" id="btn-save" class="flex-1 btn-primary text-center">
        Enregistrer
      </button>
    </div>

  </div>
</div>

<!-- ═══════════════════════════ MODAL DELETE ═══════════════════════════ -->
<div id="modal-delete" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
             4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
    </div>
    <h3 class="font-bold text-slate-800 text-lg mb-2">Supprimer ce document ?</h3>
    <p class="text-slate-500 text-sm mb-7" id="delete-title">—</p>
    <div class="flex gap-3">
      <button onclick="document.getElementById('modal-delete').classList.add('hidden')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="confirmDelete()"
        class="flex-1 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600
               text-white text-sm font-semibold transition">
        Supprimer
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast"
  class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl
         text-white text-sm font-medium shadow-xl"></div>

<script>
let allDocs    = [];
let auteursList = [];
let editionsList = [];
let deleteId   = null;
let editId     = null;

// ── LOAD DATA ──────────────────────────────────────────────
async function loadAll() {
  const [docs, auteurs, editions] = await Promise.all([
    fetch('/api/documents.php?action=list&q=').then(r => r.json()),
    fetch('/api/catalogue.php?action=list&entity=auteur').then(r => r.json()),
    fetch('/api/catalogue.php?action=list&entity=edition').then(r => r.json()),
  ]);
  allDocs      = docs;
  auteursList  = auteurs;
  editionsList = editions;
  renderTable(allDocs);
  document.getElementById('doc-count').textContent =
    `${docs.length} document(s) dans la bibliothèque`;
}

// ── FILTER ────────────────────────────────────────────────
function filterDocs() {
  const q    = document.getElementById('search-input').value.toLowerCase();
  const type = document.getElementById('filter-type').value;
  const res  = allDocs.filter(d =>
    (!type || d.type_doc === type) &&
    (d.titre.toLowerCase().includes(q) ||
     (d.auteurs || '').toLowerCase().includes(q) ||
     (d.mots_cles || '').toLowerCase().includes(q))
  );
  renderTable(res);
}

// ── RENDER TABLE ──────────────────────────────────────────
function renderTable(data) {
  const tbody = document.getElementById('docs-tbody');
  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-slate-400">Aucun document trouvé</td></tr>`;
    return;
  }
  tbody.innerHTML = data.map(d => {
    const dispo = d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes;
    return `
    <tr class="border-t border-slate-50 hover:bg-slate-50/70 transition">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
            ${d.type_doc === 'livre' ? 'bg-blue-50 text-blue-500' : 'bg-purple-50 text-purple-500'}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                   C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                   C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                   C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <div>
            <p class="font-medium text-slate-800 text-sm max-w-[220px] truncate">${d.titre}</p>
            <span class="text-xs px-2 py-0.5 rounded-full
              ${d.type_doc === 'livre' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'}">
              ${d.type_doc}${d.genre ? ' · ' + d.genre : ''}
            </span>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm max-w-[150px] truncate">
        ${d.auteurs || '<span class="text-slate-300">—</span>'}
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm">
        ${d.editeur || '<span class="text-slate-300">—</span>'}
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm">
        ${d.date_parution ? d.date_parution.substring(0,4) : '—'}
      </td>
      <td class="px-5 py-4">
        <div class="flex items-center gap-1.5">
          <span class="text-sm font-semibold ${dispo > 0 ? 'text-green-600' : 'text-red-500'}">${dispo}</span>
          <span class="text-xs text-slate-400">/ ${d.nombre_exemplaires_acquis}</span>
        </div>
        <div class="w-16 h-1 rounded-full bg-slate-100 mt-1 overflow-hidden">
          <div class="h-full rounded-full ${dispo > 0 ? 'bg-green-400' : 'bg-red-400'}"
               style="width:${d.nombre_exemplaires_acquis > 0 ? Math.round(dispo/d.nombre_exemplaires_acquis*100) : 0}%"></div>
        </div>
      </td>
      <td class="px-5 py-4">
        <div class="flex items-center gap-2">
          <button onclick="openModal('edit', ${d.code_doc})"
            class="p-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                   m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button onclick="initDelete(${d.code_doc}, '${d.titre.replace(/'/g,"\\'")}')"
            class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                   4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// ── OPEN MODAL ────────────────────────────────────────────
async function openModal(mode, id = null) {
  editId = id;
  resetForm();
  fillSelects();

  document.getElementById('modal-title').textContent =
    mode === 'add' ? 'Ajouter un document' : 'Modifier le document';

  if (mode === 'edit' && id) {
    const d = await fetch(`/api/documents.php?action=detail&id=${id}`).then(r => r.json());

    // Type
    const typeVal = d.type_doc;
    document.querySelector(`input[name="type_doc"][value="${typeVal}"]`).checked = true;
    toggleType();

    document.getElementById('f-titre').value       = d.titre || '';
    document.getElementById('f-date').value        = d.date_parution || '';
    document.getElementById('f-exemplaires').value = d.nombre_exemplaires_acquis || 1;
    document.getElementById('f-mots').value        = d.mots_cles || '';
    document.getElementById('f-edition').value     = d.id_edition || '';

    if (typeVal === 'livre') {
      document.getElementById('f-isbn').value  = d.isbn  || '';
      document.getElementById('f-genre').value = d.genre || '';
    } else {
      document.getElementById('f-periodicite').value  = d.periodicite || 'Mensuelle';
      document.getElementById('f-issn').value         = d.issn || '';
      document.getElementById('f-date-abo').value     = d.date_abonnement || '';
      document.getElementById('f-montant-abo').value  = d.montant_abonnement || 0;
    }

    // Auteurs
    if (d.auteur_ids) {
      const ids = d.auteur_ids.split(',').map(Number);
      [...document.getElementById('f-auteurs').options].forEach(o => {
        o.selected = ids.includes(parseInt(o.value));
      });
    }
  }

  document.getElementById('modal-doc').classList.remove('hidden');
}

function fillSelects() {
  // Auteurs
  document.getElementById('f-auteurs').innerHTML =
    auteursList.map(a => `<option value="${a.id}">${a.prenom} ${a.nom}</option>`).join('');

  // Editions
  document.getElementById('f-edition').innerHTML =
    '<option value="">Sélectionner…</option>' +
    editionsList.map(e => `<option value="${e.id_edition}">${e.raison_social}</option>`).join('');
}

function resetForm() {
  ['f-titre','f-date','f-mots','f-isbn','f-genre','f-issn','f-date-abo'].forEach(id => {
    document.getElementById(id).value = '';
  });
  document.getElementById('f-exemplaires').value  = 1;
  document.getElementById('f-montant-abo').value  = 0;
  document.querySelector('input[name="type_doc"][value="livre"]').checked = true;
  document.getElementById('form-error').classList.add('hidden');
  toggleType();
}

function toggleType() {
  const type = document.querySelector('input[name="type_doc"]:checked').value;
  document.getElementById('champs-livre').style.display = type === 'livre' ? 'grid' : 'none';
  document.getElementById('champs-revue').style.display = type === 'revue' ? 'grid' : 'none';
  document.getElementById('type-livre-label').className =
    `flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition ${type === 'livre' ? 'border-amber-400 bg-amber-50' : 'border-slate-200'}`;
  document.getElementById('type-revue-label').className =
    `flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition ${type === 'revue' ? 'border-amber-400 bg-amber-50' : 'border-slate-200'}`;
}

function closeModal() {
  document.getElementById('modal-doc').classList.add('hidden');
  editId = null;
}

// ── SAVE ──────────────────────────────────────────────────
async function saveDoc() {
  const titre = document.getElementById('f-titre').value.trim();
  const type  = document.querySelector('input[name="type_doc"]:checked').value;
  const errEl = document.getElementById('form-error');

  if (!titre) {
    errEl.textContent = 'Le titre est obligatoire';
    errEl.classList.remove('hidden');
    return;
  }

  const auteursSel = [...document.getElementById('f-auteurs').selectedOptions].map(o => parseInt(o.value));

  const payload = {
    titre,
    type_doc:                   type,
    date_parution:              document.getElementById('f-date').value,
    nombre_exemplaires_acquis:  parseInt(document.getElementById('f-exemplaires').value) || 1,
    mots_cles:                  document.getElementById('f-mots').value,
    id_edition:                 parseInt(document.getElementById('f-edition').value) || null,
    auteur_ids:                 auteursSel,
    isbn:           document.getElementById('f-isbn').value,
    genre:          document.getElementById('f-genre').value,
    periodicite:    document.getElementById('f-periodicite').value,
    issn:           document.getElementById('f-issn').value,
    date_abonnement:      document.getElementById('f-date-abo').value,
    montant_abonnement:   parseFloat(document.getElementById('f-montant-abo').value) || 0,
  };

  const btn = document.getElementById('btn-save');
  btn.textContent = 'Enregistrement…';
  btn.disabled    = true;

  let action = 'create';
  if (editId) { payload.code_doc = editId; action = 'update'; }

  const res  = await fetch(`/api/documents.php?action=${action}`, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.success) {
    closeModal();
    showToast(editId ? 'Document modifié !' : 'Document ajouté !', 'green');
    loadAll();
  } else {
    errEl.textContent = data.message || 'Erreur lors de l\'enregistrement';
    errEl.classList.remove('hidden');
  }

  btn.textContent = 'Enregistrer';
  btn.disabled    = false;
}

// ── DELETE ────────────────────────────────────────────────
function initDelete(id, titre) {
  deleteId = id;
  document.getElementById('delete-title').textContent = `"${titre}"`;
  document.getElementById('modal-delete').classList.remove('hidden');
}

async function confirmDelete() {
  const res  = await fetch('/api/documents.php?action=delete', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: deleteId })
  });
  const data = await res.json();

  document.getElementById('modal-delete').classList.add('hidden');

  if (data.success) {
    showToast('Document supprimé', 'green');
    loadAll();
  } else {
    showToast(data.message || 'Erreur lors de la suppression', 'red');
  }
}

// ── TOAST ─────────────────────────────────────────────────
function showToast(msg, color) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl text-white text-sm
                 font-medium shadow-xl ${color === 'green' ? 'bg-green-500' : 'bg-red-500'}`;
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3000);
}

// Fermer modals en cliquant dehors
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

loadAll();
</script>
</body>
</html>