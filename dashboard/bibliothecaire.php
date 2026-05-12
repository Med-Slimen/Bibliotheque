<?php
require_once '../includes/auth_check.php';
requireAuth(['bibliothecaire', 'administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bibliothécaire — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
  .modal-overlay {
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
  }
  .input-field {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.65rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
    background: #f8fafc;
  }
  .input-field:focus {
    outline: none;
    border-color: #f59e0b;
    background: white;
  }
  .btn-primary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    padding: 0.65rem 1.25rem;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245,158,11,0.35);
  }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="playfair text-3xl font-bold text-slate-800">Gestion des prêts</h1>
        <p class="text-slate-500 mt-1">
          Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋
        </p>
      </div>
      <button onclick="openEmpruntModal()"
        class="btn-primary flex items-center gap-2 self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Enregistrer un prêt
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-slate-400 text-xs uppercase tracking-wider mb-2">En cours</p>
        <p class="text-3xl font-bold text-amber-500" id="s-encours">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-slate-400 text-xs uppercase tracking-wider mb-2">En retard</p>
        <p class="text-3xl font-bold text-red-500" id="s-retard">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-slate-400 text-xs uppercase tracking-wider mb-2">Retours aujourd'hui</p>
        <p class="text-3xl font-bold text-green-500" id="s-retours">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-slate-400 text-xs uppercase tracking-wider mb-2">Total prêts</p>
        <p class="text-3xl font-bold text-slate-800" id="s-total">—</p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">

      <!-- Filtres -->
      <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row gap-3">
        <input type="text" id="search-input"
          placeholder="Rechercher adhérent ou document…"
          oninput="filterTable()"
          class="input-field flex-1">
        <select id="filter-statut" onchange="loadEmprunts()"
          class="input-field sm:w-48">
          <option value="">Tous les statuts</option>
          <option value="en_cours">En cours</option>
          <option value="en_retard">En retard</option>
          <option value="retourne">Retournés</option>
        </select>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Adhérent</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Document</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Emprunté le</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Retour prévu</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Statut</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody id="emprunts-tbody">
            <tr>
              <td colspan="6" class="text-center py-12 text-slate-400">
                Chargement…
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</main>

<!-- ═══════════════════════════════════════════════════════
     MODAL — Enregistrer un prêt
════════════════════════════════════════════════════════ -->
<div id="modal-emprunt"
  class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">

    <div class="flex items-center justify-between p-6 border-b border-slate-100">
      <h3 class="font-bold text-slate-800 text-lg">Enregistrer un prêt</h3>
      <button onclick="closeModal('modal-emprunt')"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="p-6 space-y-4">

      <div>
        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">
          Adhérent
        </label>
        <select id="em-adherent" class="input-field">
          <option value="">Sélectionner un adhérent…</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">
          Document
        </label>
        <select id="em-document" class="input-field" onchange="checkDispo()">
          <option value="">Sélectionner un document…</option>
        </select>
      </div>

      <!-- Info disponibilité -->
      <div id="em-dispo-info" class="hidden px-4 py-2.5 rounded-xl text-xs font-medium"></div>

      <!-- Date retour prévue -->
      <div class="bg-slate-50 rounded-xl px-4 py-3 text-xs text-slate-500 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Durée du prêt : <strong class="text-slate-700">14 jours</strong>
        — Retour prévu le <strong class="text-slate-700" id="em-date-retour">—</strong>
      </div>

      <!-- Erreur -->
      <div id="em-error"
        class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl text-xs">
      </div>

    </div>

    <div class="p-6 pt-0 flex gap-3">
      <button onclick="closeModal('modal-emprunt')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="enregistrerPret()" id="btn-enregistrer"
        class="flex-1 btn-primary text-center">
        Enregistrer
      </button>
    </div>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL — Confirmer retour
════════════════════════════════════════════════════════ -->
<div id="modal-retour"
  class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">

    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>

    <h3 class="font-bold text-slate-800 text-lg mb-2">Confirmer le retour</h3>
    <p class="text-slate-500 text-sm mb-7 leading-relaxed" id="retour-info">
      Confirmer le retour de ce document ?
    </p>

    <div class="flex gap-3">
      <button onclick="closeModal('modal-retour')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="confirmerRetour()"
        class="flex-1 px-4 py-3 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-semibold transition">
        Confirmer
      </button>
    </div>

  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TOAST notification
════════════════════════════════════════════════════════ -->
<div id="toast"
  class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all">
</div>

<script>
let allEmprunts = [];
let retourId    = null;

// ── STATS ──────────────────────────────────────────────────
async function loadStats() {
  const s = await fetch('/api/emprunts.php?action=stats').then(r => r.json());
  document.getElementById('s-encours').textContent = s.en_cours;
  document.getElementById('s-retard').textContent  = s.en_retard;
  document.getElementById('s-retours').textContent = s.retournes_auj;
  document.getElementById('s-total').textContent   = s.total;
}

// ── LOAD EMPRUNTS ──────────────────────────────────────────
async function loadEmprunts() {
  const statut = document.getElementById('filter-statut').value;
  const url    = '/api/emprunts.php?action=all' + (statut ? '&statut=' + statut : '');
  allEmprunts  = await fetch(url).then(r => r.json());
  renderTable(allEmprunts);
}

// ── FILTER TABLE ───────────────────────────────────────────
function filterTable() {
  const q   = document.getElementById('search-input').value.toLowerCase();
  const res = allEmprunts.filter(e =>
    (e.prenom_adherent + ' ' + e.nom_adherent).toLowerCase().includes(q) ||
    e.titre.toLowerCase().includes(q) ||
    e.email.toLowerCase().includes(q)
  );
  renderTable(res);
}

// ── RENDER TABLE ───────────────────────────────────────────
function renderTable(data) {
  const tbody = document.getElementById('emprunts-tbody');

  if (!data.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="text-center py-12 text-slate-400">
          Aucun résultat trouvé
        </td>
      </tr>`;
    return;
  }

  tbody.innerHTML = data.map(e => `
    <tr class="border-t border-slate-50 hover:bg-slate-50/70 transition">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-600 flex-shrink-0">
            ${e.prenom_adherent.charAt(0)}${e.nom_adherent.charAt(0)}
          </div>
          <div>
            <p class="font-medium text-slate-800 text-sm">${e.prenom_adherent} ${e.nom_adherent}</p>
            <p class="text-xs text-slate-400">${e.email}</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4">
        <p class="font-medium text-slate-800 text-sm max-w-[200px] truncate">${e.titre}</p>
        <span class="text-xs px-2 py-0.5 rounded-full ${
          e.type_doc === 'livre'
            ? 'bg-blue-50 text-blue-600'
            : 'bg-purple-50 text-purple-600'
        }">${e.type_doc}</span>
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm">${e.date_emprunt}</td>
      <td class="px-5 py-4 text-sm font-medium ${
        e.statut === 'en_retard' ? 'text-red-500' : 'text-slate-600'
      }">${e.date_retour_prevue}</td>
      <td class="px-5 py-4">
        <span class="px-2.5 py-1 rounded-full text-xs font-medium ${
          e.statut === 'en_retard' ? 'bg-red-100 text-red-700'    :
          e.statut === 'retourne'  ? 'bg-green-100 text-green-700':
                                     'bg-amber-100 text-amber-700'
        }">${e.statut.replace('_', ' ')}</span>
      </td>
      <td class="px-5 py-4">
        ${(e.statut === 'en_cours' || e.statut === 'en_retard') ? `
          <button onclick="initRetour(${e.id}, '${e.titre.replace(/'/g, "\\'")}')"
            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-green-50 text-green-600
                   hover:bg-green-100 transition">
            ✓ Retour
          </button>` : `<span class="text-xs text-slate-300">—</span>`
        }
      </td>
    </tr>
  `).join('');
}

// ── MODAL EMPRUNT ──────────────────────────────────────────
async function openEmpruntModal() {
  const [users, docs] = await Promise.all([
    fetch('/api/utilisateurs.php?action=list&role=adherent').then(r => r.json()),
    fetch('/api/documents.php?action=list&q=').then(r => r.json()),
  ]);

  // Remplir adhérents
  document.getElementById('em-adherent').innerHTML =
    '<option value="">Sélectionner un adhérent…</option>' +
    users.map(u => `
      <option value="${u.id}">
        ${u.prenom} ${u.nom} — ${u.email}
      </option>`).join('');

  // Remplir documents
  document.getElementById('em-document').innerHTML =
    '<option value="">Sélectionner un document…</option>' +
    docs.map(d => {
      const dispo = d.nombre_exemplaires_acquis - d.nombre_exemplaires_pretes;
      return `<option value="${d.code_doc}" data-dispo="${dispo}">
        ${d.titre} (${dispo} dispo.)
      </option>`;
    }).join('');

  // Date retour par défaut
  const dateRetour = new Date();
  dateRetour.setDate(dateRetour.getDate() + 14);
  document.getElementById('em-date-retour').textContent =
    dateRetour.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });

  document.getElementById('em-dispo-info').classList.add('hidden');
  document.getElementById('em-error').classList.add('hidden');
  document.getElementById('modal-emprunt').classList.remove('hidden');
}

function checkDispo() {
  const sel  = document.getElementById('em-document');
  const opt  = sel.selectedOptions[0];
  const info = document.getElementById('em-dispo-info');

  if (!opt || !opt.dataset.dispo) {
    info.classList.add('hidden');
    return;
  }

  const dispo = parseInt(opt.dataset.dispo);
  info.classList.remove('hidden');

  if (dispo > 0) {
    info.className = 'px-4 py-2.5 rounded-xl text-xs font-medium bg-green-50 border border-green-200 text-green-700';
    info.textContent = `✓ ${dispo} exemplaire(s) disponible(s)`;
  } else {
    info.className = 'px-4 py-2.5 rounded-xl text-xs font-medium bg-red-50 border border-red-200 text-red-700';
    info.textContent = '✗ Aucun exemplaire disponible pour ce document';
  }
}

async function enregistrerPret() {
  const id_adherent = document.getElementById('em-adherent').value;
  const code_doc    = document.getElementById('em-document').value;
  const errEl       = document.getElementById('em-error');

  if (!id_adherent || !code_doc) {
    errEl.textContent = 'Veuillez sélectionner un adhérent et un document';
    errEl.classList.remove('hidden');
    return;
  }

  const btn = document.getElementById('btn-enregistrer');
  btn.textContent = 'Enregistrement…';
  btn.disabled    = true;

  const res  = await fetch('/api/emprunts.php?action=emprunter', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ id_adherent, code_doc })
  });
  const data = await res.json();

  if (data.success) {
    closeModal('modal-emprunt');
    showToast('Prêt enregistré avec succès !', 'green');
    loadEmprunts();
    loadStats();
  } else {
    errEl.textContent = data.message;
    errEl.classList.remove('hidden');
  }

  btn.textContent = 'Enregistrer';
  btn.disabled    = false;
}

// ── RETOUR ─────────────────────────────────────────────────
function initRetour(id, titre) {
  retourId = id;
  document.getElementById('retour-info').textContent =
    `Confirmer le retour de : "${titre}" ?`;
  document.getElementById('modal-retour').classList.remove('hidden');
}

async function confirmerRetour() {
  const res  = await fetch('/api/emprunts.php?action=retourner', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ id_emprunt: retourId })
  });
  const data = await res.json();

  if (data.success) {
    closeModal('modal-retour');
    showToast('Retour enregistré avec succès !', 'green');
    loadEmprunts();
    loadStats();
  } else {
    showToast('Erreur lors du retour', 'red');
  }
}

// ── UTILS ──────────────────────────────────────────────────
function closeModal(id) {
  document.getElementById(id).classList.add('hidden');
  retourId = null;
}

function showToast(msg, color) {
  const toast = document.getElementById('toast');
  toast.textContent  = msg;
  toast.className    = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all ${
    color === 'green' ? 'bg-green-500' : 'bg-red-500'
  }`;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

// Fermer modals en cliquant dehors
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => {
    if (e.target === m) closeModal(m.id);
  });
});

// Init
loadStats();
loadEmprunts();
</script>

</body>
</html>