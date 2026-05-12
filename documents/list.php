<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent', 'bibliothecaire', 'administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catalogue — Bibliothèque</title>
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
  .doc-card {
    background: white; border: 1px solid #e2e8f0; border-radius: 1.25rem;
    padding: 1.25rem; transition: all 0.2s; cursor: pointer;
  }
  .doc-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.08); transform: translateY(-2px); }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Catalogue</h1>
      <p class="text-slate-500 mt-1" id="doc-count">Chargement…</p>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6
                flex flex-col sm:flex-row gap-3 items-center">
      <input type="text" id="search-input"
        placeholder="Rechercher par titre, auteur, mot-clé…"
        oninput="filterDocs()"
        class="input-field flex-1">
      <div class="flex gap-2 flex-shrink-0">
        <button onclick="setType('')"  id="btn-all"
          class="px-4 py-2 rounded-xl text-sm font-medium transition border border-amber-400 bg-amber-400 text-white">
          Tous
        </button>
        <button onclick="setType('livre')" id="btn-livre"
          class="px-4 py-2 rounded-xl text-sm font-medium transition border border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50">
          📖 Livres
        </button>
        <button onclick="setType('revue')" id="btn-revue"
          class="px-4 py-2 rounded-xl text-sm font-medium transition border border-slate-200 text-slate-600 hover:border-purple-300 hover:bg-purple-50">
          📰 Revues
        </button>
      </div>
    </div>

    <!-- Grid -->
    <div id="docs-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div class="col-span-full text-center py-16 text-slate-400">Chargement…</div>
    </div>

  </div>
</main>

<!-- MODAL DETAIL -->
<div id="modal-detail" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">

    <div class="flex items-center justify-between p-6 border-b border-slate-100 flex-shrink-0">
      <h3 class="font-bold text-slate-800 text-lg pr-4" id="detail-titre">—</h3>
      <button onclick="closeDetail()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center
               justify-center transition flex-shrink-0">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 p-6 space-y-4" id="detail-body">
      <div class="text-center text-slate-400 py-8">Chargement…</div>
    </div>

    <div class="p-6 border-t border-slate-100 flex-shrink-0" id="detail-footer"></div>

  </div>
</div>

<!-- MODAL CONFIRMATION EMPRUNT -->
<div id="modal-emprunt" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
    <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
             C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
             C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
             C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
      </svg>
    </div>
    <h3 class="font-bold text-slate-800 text-lg mb-2">Emprunter ce document ?</h3>
    <p class="text-slate-500 text-sm mb-2" id="emprunt-titre">—</p>
    <p class="text-xs text-slate-400 mb-7">Durée : 14 jours — Retour prévu le <strong id="emprunt-date" class="text-slate-600"></strong></p>
    <div id="emprunt-error" class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl text-xs mb-4"></div>
    <div class="flex gap-3">
      <button onclick="document.getElementById('modal-emprunt').classList.add('hidden')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="confirmerEmprunt()" id="btn-confirmer"
        class="flex-1 btn-primary text-center">
        Emprunter
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl
     text-white text-sm font-medium shadow-xl"></div>

<script>
let allDocs    = [];
let activeType = '';
let empruntDoc = null;

// ── LOAD ──────────────────────────────────────────────────
async function loadDocs() {
  allDocs = await fetch('/api/documents.php?action=list&q=').then(r => r.json());
  filterDocs();
}

// ── FILTER ────────────────────────────────────────────────
function setType(type) {
  activeType = type;
  ['all','livre','revue'].forEach(t => {
    const btn = document.getElementById('btn-' + (t === '' ? 'all' : t));
    if (!btn) return;
  });
  document.getElementById('btn-all').className   = `px-4 py-2 rounded-xl text-sm font-medium transition border ${
    type === '' ? 'border-amber-400 bg-amber-400 text-white' : 'border-slate-200 text-slate-600 hover:border-amber-300 hover:bg-amber-50'
  }`;
  document.getElementById('btn-livre').className = `px-4 py-2 rounded-xl text-sm font-medium transition border ${
    type === 'livre' ? 'border-blue-400 bg-blue-400 text-white' : 'border-slate-200 text-slate-600 hover:border-blue-300 hover:bg-blue-50'
  }`;
  document.getElementById('btn-revue').className = `px-4 py-2 rounded-xl text-sm font-medium transition border ${
    type === 'revue' ? 'border-purple-400 bg-purple-400 text-white' : 'border-slate-200 text-slate-600 hover:border-purple-300 hover:bg-purple-50'
  }`;
  filterDocs();
}

function filterDocs() {
  const q   = document.getElementById('search-input').value.toLowerCase();
  const res = allDocs.filter(d =>
    (!activeType || d.type_doc === activeType) &&
    (d.titre.toLowerCase().includes(q) ||
     (d.auteurs  || '').toLowerCase().includes(q) ||
     (d.mots_cles|| '').toLowerCase().includes(q))
  );
  renderGrid(res);
  document.getElementById('doc-count').textContent =
    `${res.length} document(s) trouvé(s)`;
}

// ── RENDER GRID ───────────────────────────────────────────
function renderGrid(data) {
  const grid = document.getElementById('docs-grid');
  if (!data.length) {
    grid.innerHTML = `
      <div class="col-span-full text-center py-16">
        <p class="text-slate-400 text-lg mb-2">Aucun document trouvé</p>
        <p class="text-slate-300 text-sm">Essayez d'autres mots-clés</p>
      </div>`;
    return;
  }

  grid.innerHTML = data.map(d => {
    const dispo   = d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes;
    const isLivre = d.type_doc === 'livre';
    return `
    <div class="doc-card" onclick="openDetail(${d.code_doc})">

      <!-- Type badge + dispo -->
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs px-2.5 py-1 rounded-full font-medium ${
          isLivre ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'
        }">${isLivre ? '📖 Livre' : '📰 Revue'}</span>
        <span class="text-xs font-medium ${dispo > 0 ? 'text-green-600' : 'text-red-500'}">
          ${dispo > 0 ? `${dispo} dispo.` : 'Indispo.'}
        </span>
      </div>

      <!-- Icône -->
      <div class="w-12 h-12 rounded-xl mb-3 flex items-center justify-center
                  ${isLivre ? 'bg-blue-50' : 'bg-purple-50'}">
        <svg class="w-6 h-6 ${isLivre ? 'text-blue-400' : 'text-purple-400'}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
               C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
               C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
               C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>

      <!-- Titre -->
      <h3 class="font-semibold text-slate-800 text-sm leading-snug mb-2 line-clamp-2"
          style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
        ${d.titre}
      </h3>

      <!-- Auteur -->
      <p class="text-xs text-slate-400 mb-3 truncate">
        ${d.auteurs || 'Auteur inconnu'}
      </p>

      <!-- Éditeur + année -->
      <div class="flex items-center justify-between text-xs text-slate-400 pt-3
                  border-t border-slate-100">
        <span class="truncate max-w-[120px]">${d.editeur || '—'}</span>
        <span>${d.date_parution ? d.date_parution.substring(0,4) : '—'}</span>
      </div>

    </div>`;
  }).join('');
}

// ── DETAIL ────────────────────────────────────────────────
async function openDetail(id) {
  document.getElementById('detail-titre').textContent = 'Chargement…';
  document.getElementById('detail-body').innerHTML    = '<div class="text-center text-slate-400 py-8">Chargement…</div>';
  document.getElementById('detail-footer').innerHTML  = '';
  document.getElementById('modal-detail').classList.remove('hidden');

  const d     = await fetch(`/api/documents.php?action=detail&id=${id}`).then(r => r.json());
  const dispo = d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes;
  const isLivre = d.type_doc === 'livre';

  document.getElementById('detail-titre').textContent = d.titre;

  // Body
  document.getElementById('detail-body').innerHTML = `

    <!-- Disponibilité -->
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl ${
      dispo > 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
    }">
      <span class="text-lg">${dispo > 0 ? '✓' : '✗'}</span>
      <div>
        <p class="text-sm font-semibold ${dispo > 0 ? 'text-green-700' : 'text-red-700'}">
          ${dispo > 0 ? `${dispo} exemplaire(s) disponible(s)` : 'Aucun exemplaire disponible'}
        </p>
        <p class="text-xs ${dispo > 0 ? 'text-green-500' : 'text-red-400'}">
          ${d.nombre_exemplaires_pretes} emprunté(s) sur ${d.nombre_exemplaires_acquis}
        </p>
      </div>
    </div>

    <!-- Infos -->
    <div class="grid grid-cols-2 gap-3">
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Type</p>
        <p class="text-sm font-medium text-slate-700">${isLivre ? '📖 Livre' : '📰 Revue'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Parution</p>
        <p class="text-sm font-medium text-slate-700">${d.date_parution ? d.date_parution.substring(0,4) : '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Auteur(s)</p>
        <p class="text-sm font-medium text-slate-700">${d.auteurs || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Éditeur</p>
        <p class="text-sm font-medium text-slate-700">${d.editeur || '—'}</p>
      </div>
      ${isLivre ? `
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">ISBN</p>
        <p class="text-sm font-medium text-slate-700">${d.isbn || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Genre</p>
        <p class="text-sm font-medium text-slate-700">${d.genre || '—'}</p>
      </div>
      ` : `
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Périodicité</p>
        <p class="text-sm font-medium text-slate-700">${d.periodicite || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">ISSN</p>
        <p class="text-sm font-medium text-slate-700">${d.issn || '—'}</p>
      </div>
      `}
    </div>

    <!-- Mots-clés -->
    ${d.mots_cles ? `
    <div>
      <p class="text-xs text-slate-400 mb-2">Mots-clés</p>
      <div class="flex flex-wrap gap-2">
        ${d.mots_cles.split(',').map(k => `
          <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs">${k.trim()}</span>
        `).join('')}
      </div>
    </div>` : ''}
  `;

  // Footer — bouton emprunt (adhérents uniquement)
  const role = '<?= htmlspecialchars($_SESSION["user_role"] ?? "") ?>';
  if (role === 'adherent') {
    document.getElementById('detail-footer').innerHTML = dispo > 0 ? `
      <button onclick="initEmprunt(${d.code_doc}, '${d.titre.replace(/'/g,"\\'")}')"
        class="w-full btn-primary">
        Emprunter ce document
      </button>` : `
      <button disabled
        class="w-full px-4 py-3 rounded-xl bg-slate-100 text-slate-400
               text-sm font-medium cursor-not-allowed">
        Indisponible actuellement
      </button>`;
  } else {
    document.getElementById('detail-footer').innerHTML = `
      <button onclick="closeDetail()"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Fermer
      </button>`;
  }
}

function closeDetail() {
  document.getElementById('modal-detail').classList.add('hidden');
}

// ── EMPRUNT ───────────────────────────────────────────────
function initEmprunt(codeDoc, titre) {
  empruntDoc = codeDoc;
  const dateRetour = new Date();
  dateRetour.setDate(dateRetour.getDate() + 14);
  document.getElementById('emprunt-titre').textContent = `"${titre}"`;
  document.getElementById('emprunt-date').textContent  =
    dateRetour.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
  document.getElementById('emprunt-error').classList.add('hidden');
  document.getElementById('modal-detail').classList.add('hidden');
  document.getElementById('modal-emprunt').classList.remove('hidden');
}

async function confirmerEmprunt() {
  const btn   = document.getElementById('btn-confirmer');
  const errEl = document.getElementById('emprunt-error');
  btn.textContent = 'Enregistrement…';
  btn.disabled    = true;

  const res  = await fetch('/api/emprunts.php?action=emprunter', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ code_doc: empruntDoc })
  });
  const data = await res.json();

  if (data.success) {
    document.getElementById('modal-emprunt').classList.add('hidden');
    showToast('Emprunt enregistré avec succès !', 'green');
    loadDocs();
  } else {
    errEl.textContent = data.message || 'Erreur lors de l\'emprunt';
    errEl.classList.remove('hidden');
  }
  btn.textContent = 'Emprunter';
  btn.disabled    = false;
}

// ── TOAST ─────────────────────────────────────────────────
function showToast(msg, color) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl text-white
                 text-sm font-medium shadow-xl ${color === 'green' ? 'bg-green-500' : 'bg-red-500'}`;
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3000);
}

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

loadDocs();
</script>
</body>
</html>