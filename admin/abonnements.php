<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Abonnements — Bibliothèque</title>
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
        <h1 class="playfair text-3xl font-bold text-slate-800">Gestion des abonnements</h1>
        <p class="text-slate-500 mt-1" id="abo-count">Chargement…</p>
      </div>
      <button onclick="openModal('add')" class="btn-primary flex items-center gap-2 self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvel abonnement
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Actifs</p>
        <p class="text-3xl font-bold text-green-500" id="s-actifs">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Expirés</p>
        <p class="text-3xl font-bold text-red-400" id="s-expires">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Revenus totaux</p>
        <p class="text-2xl font-bold text-slate-800" id="s-revenus">—</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-2">Revenus cette année</p>
        <p class="text-2xl font-bold text-amber-500" id="s-revenus-annee">—</p>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
      <input type="text" id="search-input" placeholder="Rechercher par nom ou email…"
        oninput="filterTable()" class="input-field flex-1">
      <select id="filter-etat" onchange="filterTable()" class="input-field sm:w-44">
        <option value="">Tous les états</option>
        <option value="actif">Actifs</option>
        <option value="expiré">Expirés</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Adhérent</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Date début</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Date fin</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Montant</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">État</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="abo-tbody">
            <tr><td colspan="6" class="text-center py-12 text-slate-400">Chargement…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- MODAL ADD/EDIT -->
<div id="modal-abo" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md">
    <div class="flex items-center justify-between p-6 border-b border-slate-100">
      <h3 class="font-bold text-slate-800 text-lg" id="modal-title">Nouvel abonnement</h3>
      <button onclick="closeModal()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="p-6 space-y-4">

      <div id="adherent-field">
        <label class="label">Adhérent *</label>
        <select id="f-adherent" class="input-field">
          <option value="">Sélectionner un adhérent…</option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Date début *</label>
          <input type="date" id="f-debut" class="input-field">
        </div>
        <div>
          <label class="label">Date fin *</label>
          <input type="date" id="f-fin" class="input-field">
        </div>
      </div>

      <div>
        <label class="label">Montant (DT) *</label>
        <input type="number" id="f-montant" class="input-field" step="0.01" min="0" value="50">
      </div>

      <!-- Durée rapide -->
      <div>
        <label class="label">Durée prédéfinie</label>
        <div class="flex gap-2 flex-wrap">
          <button type="button" onclick="setDuree(1)"
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition font-medium text-slate-600">
            1 mois
          </button>
          <button type="button" onclick="setDuree(6)"
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition font-medium text-slate-600">
            6 mois
          </button>
          <button type="button" onclick="setDuree(12)"
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition font-medium text-slate-600">
            1 an
          </button>
          <button type="button" onclick="setDuree(24)"
            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50 transition font-medium text-slate-600">
            2 ans
          </button>
        </div>
      </div>

      <div id="form-error" class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm"></div>
    </div>
    <div class="p-6 pt-0 flex gap-3">
      <button onclick="closeModal()"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="saveAbo()" id="btn-save" class="flex-1 btn-primary text-center">
        Enregistrer
      </button>
    </div>
  </div>
</div>

<!-- MODAL DELETE -->
<div id="modal-delete" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
      <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
             4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
      </svg>
    </div>
    <h3 class="font-bold text-slate-800 text-lg mb-2">Supprimer cet abonnement ?</h3>
    <p class="text-slate-500 text-sm mb-7" id="delete-info">—</p>
    <div class="flex gap-3">
      <button onclick="document.getElementById('modal-delete').classList.add('hidden')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="confirmDelete()"
        class="flex-1 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition">
        Supprimer
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl"></div>

<script>
let allAbos   = [];
let deleteId  = null;
let editId    = null;

async function loadAll() {
  const [abos, stats] = await Promise.all([
    fetch('/api/abonnements.php?action=list').then(r => r.json()),
    fetch('/api/abonnements.php?action=stats').then(r => r.json()),
  ]);
  allAbos = abos;
  renderTable(allAbos);
  document.getElementById('abo-count').textContent = `${abos.length} abonnement(s)`;
  document.getElementById('s-actifs').textContent        = stats.actifs;
  document.getElementById('s-expires').textContent       = stats.expires;
  document.getElementById('s-revenus').textContent       = parseFloat(stats.revenus).toFixed(2) + ' DT';
  document.getElementById('s-revenus-annee').textContent = parseFloat(stats.revenus_annee).toFixed(2) + ' DT';
}

function filterTable() {
  const q    = document.getElementById('search-input').value.toLowerCase();
  const etat = document.getElementById('filter-etat').value;
  const res  = allAbos.filter(a =>
    (!etat || a.etat === etat) &&
    ((a.nom + ' ' + a.prenom).toLowerCase().includes(q) ||
     a.email.toLowerCase().includes(q))
  );
  renderTable(res);
}

function renderTable(data) {
  const tbody = document.getElementById('abo-tbody');
  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-slate-400">Aucun abonnement trouvé</td></tr>`;
    return;
  }
  tbody.innerHTML = data.map(a => `
    <tr class="border-t border-slate-50 hover:bg-slate-50/70 transition">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-green-100 flex items-center justify-center text-xs font-bold text-green-600 flex-shrink-0">
            ${a.prenom.charAt(0)}${a.nom.charAt(0)}
          </div>
          <div>
            <p class="font-medium text-slate-800 text-sm">${a.prenom} ${a.nom}</p>
            <p class="text-xs text-slate-400">${a.email}</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-slate-600 text-sm">${a.date_debut}</td>
      <td class="px-5 py-4 text-sm font-medium ${a.etat === 'expiré' ? 'text-red-500' : 'text-slate-600'}">${a.date_fin}</td>
      <td class="px-5 py-4">
        <span class="font-semibold text-slate-800">${parseFloat(a.montant).toFixed(2)}</span>
        <span class="text-xs text-slate-400 ml-1">DT</span>
      </td>
      <td class="px-5 py-4">
        <span class="px-2.5 py-1 rounded-full text-xs font-medium ${
          a.etat === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
        }">
          ${a.etat === 'actif' ? '✓ Actif' : '✗ Expiré'}
        </span>
      </td>
      <td class="px-5 py-4">
        <div class="flex items-center gap-2">
          <button onclick="openModal('edit', ${a.id})"
            class="p-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                   m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button onclick="initDelete(${a.id}, '${a.prenom} ${a.nom}')"
            class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                   4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>
      </td>
    </tr>
  `).join('');
}

async function openModal(mode, id = null) {
  editId = id;
  document.getElementById('form-error').classList.add('hidden');
  document.getElementById('modal-title').textContent =
    mode === 'add' ? 'Nouvel abonnement' : 'Modifier l\'abonnement';

  // Remplir la liste des adhérents (add uniquement)
  const adherentField = document.getElementById('adherent-field');
  if (mode === 'add') {
    adherentField.style.display = 'block';
    const users = await fetch('/api/utilisateurs.php?action=list&role=adherent').then(r => r.json());
    document.getElementById('f-adherent').innerHTML =
      '<option value="">Sélectionner un adhérent…</option>' +
      users.map(u => `<option value="${u.id}">${u.prenom} ${u.nom} — ${u.email}</option>`).join('');
    // Valeurs par défaut
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('f-debut').value   = today;
    document.getElementById('f-fin').value     = '';
    document.getElementById('f-montant').value = '50';
  } else {
    adherentField.style.display = 'none';
    const abo = allAbos.find(a => a.id == id);
    if (abo) {
      document.getElementById('f-debut').value   = abo.date_debut;
      document.getElementById('f-fin').value     = abo.date_fin;
      document.getElementById('f-montant').value = abo.montant;
    }
  }

  document.getElementById('modal-abo').classList.remove('hidden');
}

function setDuree(mois) {
  const debut = document.getElementById('f-debut').value;
  if (!debut) return;
  const d = new Date(debut);
  d.setMonth(d.getMonth() + mois);
  document.getElementById('f-fin').value = d.toISOString().split('T')[0];
}

function closeModal() {
  document.getElementById('modal-abo').classList.add('hidden');
  editId = null;
}

async function saveAbo() {
  const debut   = document.getElementById('f-debut').value;
  const fin     = document.getElementById('f-fin').value;
  const montant = document.getElementById('f-montant').value;
  const errEl   = document.getElementById('form-error');

  if (!debut || !fin || !montant) {
    errEl.textContent = 'Tous les champs sont obligatoires';
    errEl.classList.remove('hidden');
    return;
  }
  if (fin <= debut) {
    errEl.textContent = 'La date de fin doit être après la date de début';
    errEl.classList.remove('hidden');
    return;
  }

  const payload = { date_debut: debut, date_fin: fin, montant: parseFloat(montant) };
  let action = 'create';

  if (editId) {
    payload.id = editId;
    action = 'update';
  } else {
    const adherentId = document.getElementById('f-adherent').value;
    if (!adherentId) {
      errEl.textContent = 'Veuillez sélectionner un adhérent';
      errEl.classList.remove('hidden');
      return;
    }
    payload.id_adherent = parseInt(adherentId);
  }

  const btn = document.getElementById('btn-save');
  btn.textContent = 'Enregistrement…';
  btn.disabled = true;

  const res  = await fetch(`/api/abonnements.php?action=${action}`, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.success) {
    closeModal();
    showToast(editId ? 'Abonnement modifié !' : 'Abonnement créé !', 'green');
    loadAll();
  } else {
    errEl.textContent = data.message || 'Erreur lors de l\'enregistrement';
    errEl.classList.remove('hidden');
  }
  btn.textContent = 'Enregistrer';
  btn.disabled = false;
}

function initDelete(id, name) {
  deleteId = id;
  document.getElementById('delete-info').textContent = `Abonnement de ${name}`;
  document.getElementById('modal-delete').classList.remove('hidden');
}

async function confirmDelete() {
  const res  = await fetch('/api/abonnements.php?action=delete', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: deleteId })
  });
  const data = await res.json();
  document.getElementById('modal-delete').classList.add('hidden');
  if (data.success) { showToast('Abonnement supprimé', 'green'); loadAll(); }
  else showToast('Erreur lors de la suppression', 'red');
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

loadAll();
</script>
</body>
</html>