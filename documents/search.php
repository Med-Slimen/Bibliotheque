<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent', 'bibliothecaire', 'administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recherche — Bibliothèque</title>
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
  .result-row {
    background: white; border: 1px solid #e2e8f0; border-radius: 1rem;
    padding: 1rem 1.25rem; transition: all 0.15s; cursor: pointer;
  }
  .result-row:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.07); transform: translateY(-1px); border-color: #f59e0b; }
  .highlight { background: #fef3c7; border-radius: 3px; padding: 0 2px; font-weight: 600; color: #92400e; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Recherche avancée</h1>
      <p class="text-slate-500 mt-1">Trouvez un document par titre, auteur, genre ou mot-clé</p>
    </div>

    <!-- Search Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">

      <!-- Barre principale -->
      <div class="relative mb-4">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" id="search-main"
          placeholder="Titre, auteur, ISBN, mot-clé…"
          oninput="onSearch()"
          onkeydown="if(event.key==='Enter') doSearch()"
          class="input-field pl-12 pr-4 py-3.5 text-base"
          style="padding-left:3rem">
        <button onclick="doSearch()" id="btn-search"
          class="absolute right-2 top-1/2 -translate-y-1/2 btn-primary px-5 py-2 text-sm">
          Rechercher
        </button>
      </div>

      <!-- Filtres avancés -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Type
          </label>
          <select id="filter-type" class="input-field" onchange="doSearch()">
            <option value="">Tous les types</option>
            <option value="livre">📖 Livres</option>
            <option value="revue">📰 Revues</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Disponibilité
          </label>
          <select id="filter-dispo" class="input-field" onchange="filterResults()">
            <option value="">Tous</option>
            <option value="dispo">Disponibles uniquement</option>
            <option value="indispo">Indisponibles</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
            Trier par
          </label>
          <select id="sort-by" class="input-field" onchange="filterResults()">
            <option value="titre">Titre (A→Z)</option>
            <option value="date">Date (récent)</option>
            <option value="dispo">Disponibilité</option>
          </select>
        </div>
      </div>

    </div>

    <!-- Recherches rapides (état initial) -->
    <div id="quick-searches">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
        Suggestions
      </p>
      <div class="flex flex-wrap gap-2 mb-8">
        <?php
        $suggestions = ['Algorithmique','Base de données','Génie logiciel',
                        'Clean Code','Design Patterns','Informatique'];
        foreach ($suggestions as $s): ?>
        <button onclick="quickSearch('<?= $s ?>')"
          class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-sm
                 text-slate-600 hover:border-amber-400 hover:bg-amber-50
                 hover:text-amber-700 transition font-medium">
          <?= $s ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Tous les documents -->
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
        Tous les documents
      </p>
      <div id="all-docs-list">
        <div class="text-center text-slate-400 py-8">Chargement…</div>
      </div>
    </div>

    <!-- Résultats de recherche -->
    <div id="search-results" class="hidden">
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500" id="results-count">—</p>
        <button onclick="clearSearch()"
          class="text-xs text-slate-400 hover:text-slate-600 flex items-center gap-1 transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Effacer
        </button>
      </div>
      <div id="results-list" class="space-y-3"></div>
    </div>

  </div>
</main>

<!-- MODAL DETAIL -->
<div id="modal-detail" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
    <div class="flex items-center justify-between p-6 border-b border-slate-100 flex-shrink-0">
      <h3 class="font-bold text-slate-800 text-lg pr-4" id="detail-titre">—</h3>
      <button onclick="closeDetail()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition flex-shrink-0">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="overflow-y-auto flex-1 p-6 space-y-4" id="detail-body"></div>
    <div class="p-6 border-t border-slate-100 flex-shrink-0" id="detail-footer"></div>
  </div>
</div>

<!-- MODAL EMPRUNT -->
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
    <p class="text-xs text-slate-400 mb-7">Retour prévu le <strong id="emprunt-date" class="text-slate-600"></strong></p>
    <div id="emprunt-error" class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl text-xs mb-4"></div>
    <div class="flex gap-3">
      <button onclick="document.getElementById('modal-emprunt').classList.add('hidden')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="confirmerEmprunt()" id="btn-confirmer" class="flex-1 btn-primary">
        Emprunter
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl"></div>

<script>
let allDocs    = [];
let filtered   = [];
let empruntDoc = null;
const userRole = '<?= htmlspecialchars($_SESSION["user_role"] ?? "") ?>';

// ── INIT ──────────────────────────────────────────────────
async function init() {
  allDocs = await fetch('/api/documents.php?action=list&q=').then(r => r.json());
  renderAllDocs(allDocs);
}

// ── RENDER ALL DOCS (état initial) ───────────────────────
function renderAllDocs(data) {
  const el = document.getElementById('all-docs-list');
  if (!data.length) {
    el.innerHTML = `<p class="text-center text-slate-400 text-sm py-4">Aucun document</p>`;
    return;
  }
  el.innerHTML = `<div class="space-y-2">${data.slice(0,8).map(d => renderRow(d, '')).join('')}</div>
    ${data.length > 8 ? `<p class="text-xs text-slate-400 text-center mt-3">${data.length - 8} autres documents — lancez une recherche pour tout voir</p>` : ''}`;
}

// ── SEARCH ────────────────────────────────────────────────
let searchTimeout = null;
function onSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(doSearch, 350);
}

async function doSearch() {
  const q    = document.getElementById('search-main').value.trim();
  const type = document.getElementById('filter-type').value;

  if (!q && !type) { clearSearch(); return; }

  const url = `/api/documents.php?action=list&q=${encodeURIComponent(q)}&type=${type}`;
  const res = await fetch(url).then(r => r.json());
  filtered  = res;

  document.getElementById('quick-searches').classList.add('hidden');
  document.getElementById('search-results').classList.remove('hidden');
  filterResults();
}

function filterResults() {
  const dispo = document.getElementById('filter-dispo').value;
  const sort  = document.getElementById('sort-by').value;
  const q     = document.getElementById('search-main').value.trim().toLowerCase();

  let data = [...filtered];

  // Filtre disponibilité
  if (dispo === 'dispo')   data = data.filter(d => (d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes) > 0);
  if (dispo === 'indispo') data = data.filter(d => (d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes) <= 0);

  // Tri
  if (sort === 'titre') data.sort((a, b) => a.titre.localeCompare(b.titre));
  if (sort === 'date')  data.sort((a, b) => (b.date_parution || '').localeCompare(a.date_parution || ''));
  if (sort === 'dispo') data.sort((a, b) =>
    (b.nombre_exemplaires_acquis - b.nombre_exemplaires_pretes) -
    (a.nombre_exemplaires_acquis - a.nombre_exemplaires_pretes)
  );

  document.getElementById('results-count').textContent =
    `${data.length} résultat(s) pour "${document.getElementById('search-main').value}"`;

  const list = document.getElementById('results-list');
  if (!data.length) {
    list.innerHTML = `
      <div class="text-center py-12">
        <p class="text-slate-400 text-lg mb-2">Aucun résultat</p>
        <p class="text-slate-300 text-sm">Essayez d'autres termes ou retirez des filtres</p>
      </div>`;
    return;
  }
  list.innerHTML = data.map(d => renderRow(d, q)).join('');
}

// ── RENDER ROW ────────────────────────────────────────────
function renderRow(d, query) {
  const dispo   = d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes;
  const isLivre = d.type_doc === 'livre';

  function hl(text) {
    if (!query || !text) return text || '';
    const re = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
    return text.replace(re, '<mark class="highlight">$1</mark>');
  }

  return `
  <div class="result-row" onclick="openDetail(${d.code_doc})">
    <div class="flex items-start gap-4">

      <!-- Icône -->
      <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center mt-0.5
                  ${isLivre ? 'bg-blue-50' : 'bg-purple-50'}">
        <svg class="w-5 h-5 ${isLivre ? 'text-blue-400' : 'text-purple-400'}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
               C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
               C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
               C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
      </div>

      <!-- Contenu -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-3">
          <p class="font-semibold text-slate-800 text-sm leading-snug">${hl(d.titre)}</p>
          <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-medium ${
            dispo > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'
          }">${dispo > 0 ? `${dispo} dispo.` : 'Indispo.'}</span>
        </div>
        <p class="text-xs text-slate-400 mt-1">${hl(d.auteurs) || 'Auteur inconnu'}</p>
        <div class="flex items-center gap-3 mt-2">
          <span class="text-xs px-2 py-0.5 rounded-full ${
            isLivre ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'
          }">${isLivre ? '📖 Livre' : '📰 Revue'}${d.genre ? ' · ' + d.genre : ''}</span>
          ${d.editeur ? `<span class="text-xs text-slate-400">${d.editeur}</span>` : ''}
          ${d.date_parution ? `<span class="text-xs text-slate-400">${d.date_parution.substring(0,4)}</span>` : ''}
        </div>
        ${d.mots_cles ? `
        <div class="flex flex-wrap gap-1 mt-2">
          ${d.mots_cles.split(',').slice(0,4).map(k => `
            <span class="text-xs px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md">${hl(k.trim())}</span>
          `).join('')}
        </div>` : ''}
      </div>

    </div>
  </div>`;
}

// ── QUICK SEARCH ──────────────────────────────────────────
function quickSearch(term) {
  document.getElementById('search-main').value = term;
  doSearch();
}

function clearSearch() {
  document.getElementById('search-main').value = '';
  document.getElementById('filter-type').value  = '';
  document.getElementById('filter-dispo').value = '';
  document.getElementById('sort-by').value      = 'titre';
  document.getElementById('quick-searches').classList.remove('hidden');
  document.getElementById('search-results').classList.add('hidden');
  filtered = [];
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

  document.getElementById('detail-body').innerHTML = `
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
    <div class="grid grid-cols-2 gap-3">
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Auteur(s)</p>
        <p class="text-sm font-medium text-slate-700">${d.auteurs || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Éditeur</p>
        <p class="text-sm font-medium text-slate-700">${d.editeur || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Type</p>
        <p class="text-sm font-medium text-slate-700">${isLivre ? '📖 Livre' : '📰 Revue'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Parution</p>
        <p class="text-sm font-medium text-slate-700">${d.date_parution ? d.date_parution.substring(0,4) : '—'}</p>
      </div>
      ${isLivre ? `
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">ISBN</p>
        <p class="text-sm font-medium text-slate-700">${d.isbn || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Genre</p>
        <p class="text-sm font-medium text-slate-700">${d.genre || '—'}</p>
      </div>` : `
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">Périodicité</p>
        <p class="text-sm font-medium text-slate-700">${d.periodicite || '—'}</p>
      </div>
      <div class="bg-slate-50 rounded-xl p-3">
        <p class="text-xs text-slate-400 mb-1">ISSN</p>
        <p class="text-sm font-medium text-slate-700">${d.issn || '—'}</p>
      </div>`}
    </div>
    ${d.mots_cles ? `
    <div>
      <p class="text-xs text-slate-400 mb-2">Mots-clés</p>
      <div class="flex flex-wrap gap-2">
        ${d.mots_cles.split(',').map(k =>
          `<span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs">${k.trim()}</span>`
        ).join('')}
      </div>
    </div>` : ''}
  `;

  if (userRole === 'adherent') {
    document.getElementById('detail-footer').innerHTML = dispo > 0 ? `
      <button onclick="initEmprunt(${d.code_doc}, '${d.titre.replace(/'/g,"\\'")}')"
        class="w-full btn-primary">
        Emprunter ce document
      </button>` : `
      <button disabled class="w-full px-4 py-3 rounded-xl bg-slate-100 text-slate-400 text-sm font-medium cursor-not-allowed">
        Indisponible actuellement
      </button>`;
  } else {
    document.getElementById('detail-footer').innerHTML = `
      <button onclick="closeDetail()"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
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
    showToast('Emprunt enregistré !', 'green');
    allDocs = await fetch('/api/documents.php?action=list&q=').then(r => r.json());
    doSearch();
  } else {
    errEl.textContent = data.message || 'Erreur lors de l\'emprunt';
    errEl.classList.remove('hidden');
  }
  btn.textContent = 'Emprunter';
  btn.disabled    = false;
}

function showToast(msg, color) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl ${color === 'green' ? 'bg-green-500' : 'bg-red-500'}`;
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3000);
}

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

init();
</script>
</body>
</html>