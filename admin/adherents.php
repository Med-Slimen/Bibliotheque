<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur', 'bibliothecaire']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Adhérents — Bibliothèque</title>
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
        <h1 class="playfair text-3xl font-bold text-slate-800">Fichier des adhérents</h1>
        <p class="text-slate-500 mt-1" id="adherent-count">Chargement…</p>
      </div>
    </div>

    <!-- Stats Pills -->
    <div class="flex flex-wrap gap-3 mb-6">
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Total adhérents : </span>
        <span class="font-bold text-slate-800" id="st-total">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Actifs : </span>
        <span class="font-bold text-green-600" id="st-actifs">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Avec abonnement valide : </span>
        <span class="font-bold text-amber-600" id="st-abonnes">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Emprunts en cours : </span>
        <span class="font-bold text-blue-600" id="st-emprunts">—</span>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
      <input type="text" id="search-input" placeholder="Rechercher par nom, prénom, email, téléphone…"
        oninput="filterTable()" class="input-field flex-1">
      <select id="filter-status" onchange="filterTable()" class="input-field sm:w-44">
        <option value="">Tous les statuts</option>
        <option value="actif">Actifs</option>
        <option value="inactif">Inactifs</option>
      </select>
      <select id="filter-abo" onchange="filterTable()" class="input-field sm:w-48">
        <option value="">Tous abonnements</option>
        <option value="1">Abonnement valide</option>
        <option value="0">Sans abonnement</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Adhérent</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Téléphone</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Statut</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Abonnement</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Emprunts actifs</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Inscrit le</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="adherents-tbody">
            <tr><td colspan="7" class="text-center py-12 text-slate-400">Chargement…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- MODAL DETAIL / HISTORIQUE -->
<div id="modal-detail" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">

    <div class="flex items-center justify-between p-6 border-b border-slate-100 flex-shrink-0">
      <h3 class="font-bold text-slate-800 text-lg" id="detail-name">Détail adhérent</h3>
      <button onclick="closeDetail()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 p-6 space-y-6">

      <!-- Info personnelles -->
      <div class="grid grid-cols-2 gap-4" id="detail-info"></div>

      <!-- Abonnement actuel -->
      <div>
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Abonnement</h4>
        <div id="detail-abo" class="bg-slate-50 rounded-xl p-4"></div>
      </div>

      <!-- Historique emprunts -->
      <div>
        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Historique des emprunts</h4>
        <div id="detail-emprunts">
          <div class="text-center text-slate-400 text-sm py-4">Chargement…</div>
        </div>
      </div>

    </div>

    <div class="p-6 border-t border-slate-100 flex-shrink-0">
      <button onclick="closeDetail()"
        class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
        Fermer
      </button>
    </div>

  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl text-white text-sm font-medium shadow-xl"></div>

<script>
let allAdherents = [];

async function loadAll() {
  const users = await fetch('/api/utilisateurs.php?action=list&role=adherent').then(r => r.json());
  allAdherents = users;
  renderTable(allAdherents);
  document.getElementById('adherent-count').textContent = `${users.length} adhérent(s)`;

  // Calcul stats
  const actifs   = users.filter(u => u.adherent_status === 'actif').length;
  const abonnes  = users.filter(u => u.abonnement_actif > 0).length;
  const emprunts = users.reduce((sum, u) => sum + parseInt(u.emprunts_actifs || 0), 0);

  document.getElementById('st-total').textContent    = users.length;
  document.getElementById('st-actifs').textContent   = actifs;
  document.getElementById('st-abonnes').textContent  = abonnes;
  document.getElementById('st-emprunts').textContent = emprunts;
}

function filterTable() {
  const q      = document.getElementById('search-input').value.toLowerCase();
  const status = document.getElementById('filter-status').value;
  const abo    = document.getElementById('filter-abo').value;

  const res = allAdherents.filter(u => {
    const matchQ = (u.nom + ' ' + u.prenom).toLowerCase().includes(q) ||
                   u.email.toLowerCase().includes(q) ||
                   (u.telephone || '').toLowerCase().includes(q);
    const matchS = !status || u.adherent_status === status;
    const matchA = abo === '' || (abo === '1' ? u.abonnement_actif > 0 : u.abonnement_actif == 0);
    return matchQ && matchS && matchA;
  });
  renderTable(res);
}

function renderTable(data) {
  const tbody = document.getElementById('adherents-tbody');
  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-slate-400">Aucun adhérent trouvé</td></tr>`;
    return;
  }
  tbody.innerHTML = data.map(u => `
    <tr class="border-t border-slate-50 hover:bg-slate-50/70 transition">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
                      text-xs font-bold bg-green-100 text-green-600">
            ${u.prenom.charAt(0)}${u.nom.charAt(0)}
          </div>
          <div>
            <p class="font-medium text-slate-800 text-sm">${u.prenom} ${u.nom}</p>
            <p class="text-xs text-slate-400">${u.email}</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm">
        ${u.telephone || '<span class="text-slate-300">—</span>'}
      </td>
      <td class="px-5 py-4">
        <span class="px-2.5 py-1 rounded-full text-xs font-medium ${
          u.adherent_status === 'actif'
            ? 'bg-green-100 text-green-700'
            : 'bg-slate-100 text-slate-500'
        }">${u.adherent_status === 'actif' ? '● Actif' : '○ Inactif'}</span>
      </td>
      <td class="px-5 py-4">
        ${u.abonnement_actif > 0
          ? `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">✓ Valide</span>`
          : `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">✗ Expiré</span>`}
      </td>
      <td class="px-5 py-4">
        ${parseInt(u.emprunts_actifs) > 0
          ? `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
               ${u.emprunts_actifs} en cours
             </span>`
          : `<span class="text-xs text-slate-300">—</span>`}
      </td>
      <td class="px-5 py-4 text-slate-400 text-xs">
        ${u.created_at ? u.created_at.substring(0,10) : '—'}
      </td>
      <td class="px-5 py-4">
        <button onclick="openDetail(${u.id})"
          class="text-xs font-medium px-3 py-1.5 rounded-lg bg-slate-100
                 text-slate-600 hover:bg-amber-50 hover:text-amber-600 transition">
          Voir fiche →
        </button>
      </td>
    </tr>
  `).join('');
}

async function openDetail(uid) {
  const u = allAdherents.find(a => a.id == uid);
  if (!u) return;

  document.getElementById('detail-name').textContent = `${u.prenom} ${u.nom}`;

  // Info personnelles
  document.getElementById('detail-info').innerHTML = `
    <div class="bg-slate-50 rounded-xl p-4">
      <p class="text-xs text-slate-400 mb-1">Email</p>
      <p class="text-sm font-medium text-slate-700">${u.email}</p>
    </div>
    <div class="bg-slate-50 rounded-xl p-4">
      <p class="text-xs text-slate-400 mb-1">Téléphone</p>
      <p class="text-sm font-medium text-slate-700">${u.telephone || '—'}</p>
    </div>
    <div class="bg-slate-50 rounded-xl p-4">
      <p class="text-xs text-slate-400 mb-1">Statut</p>
      <p class="text-sm font-medium ${u.adherent_status === 'actif' ? 'text-green-600' : 'text-slate-500'}">
        ${u.adherent_status === 'actif' ? '● Actif' : '○ Inactif'}
      </p>
    </div>
    <div class="bg-slate-50 rounded-xl p-4">
      <p class="text-xs text-slate-400 mb-1">Inscrit le</p>
      <p class="text-sm font-medium text-slate-700">${u.created_at ? u.created_at.substring(0,10) : '—'}</p>
    </div>
  `;

  // Abonnement
  const aboEl = document.getElementById('detail-abo');
  try {
    const res  = await fetch(`/api/abonnements.php?action=list`).then(r => r.json());
    const abos = res.filter(a => a.id_adherent == uid);
    if (!abos.length) {
      aboEl.innerHTML = `<p class="text-sm text-red-500">Aucun abonnement enregistré</p>`;
    } else {
      const latest = abos[0];
      aboEl.innerHTML = `
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="space-y-1">
            <p class="text-xs text-slate-400">Période : <span class="text-slate-700 font-medium">${latest.date_debut} → ${latest.date_fin}</span></p>
            <p class="text-xs text-slate-400">Montant : <span class="text-slate-700 font-medium">${parseFloat(latest.montant).toFixed(2)} DT</span></p>
          </div>
          <span class="px-3 py-1.5 rounded-full text-xs font-semibold ${
            latest.etat === 'actif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
          }">${latest.etat === 'actif' ? '✓ Actif' : '✗ Expiré'}</span>
        </div>
        ${abos.length > 1 ? `<p class="text-xs text-slate-400 mt-2">${abos.length - 1} abonnement(s) précédent(s)</p>` : ''}
      `;
    }
  } catch(e) {
    aboEl.innerHTML = `<p class="text-sm text-slate-400">Impossible de charger l'abonnement</p>`;
  }

  // Historique emprunts
  const empruntsEl = document.getElementById('detail-emprunts');
  try {
    const emprunts = await fetch(`/api/emprunts.php?action=historique&id=${uid}`).then(r => r.json());
    if (!emprunts.length) {
      empruntsEl.innerHTML = `<p class="text-sm text-slate-400 text-center py-4">Aucun emprunt enregistré</p>`;
    } else {
      empruntsEl.innerHTML = `
        <div class="space-y-2">
          ${emprunts.map(e => `
            <div class="flex items-center justify-between px-4 py-3 bg-slate-50 rounded-xl">
              <div>
                <p class="text-sm font-medium text-slate-700 truncate max-w-xs">${e.titre}</p>
                <p class="text-xs text-slate-400">
                  ${e.date_emprunt}
                  ${e.date_retour_effective ? ` → retourné le ${e.date_retour_effective}` : ` → prévu le ${e.date_retour_prevue}`}
                </p>
              </div>
              <span class="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0 ml-3 ${
                e.statut === 'en_retard' ? 'bg-red-100 text-red-700'    :
                e.statut === 'retourne'  ? 'bg-green-100 text-green-700':
                                           'bg-amber-100 text-amber-700'
              }">${e.statut.replace('_',' ')}</span>
            </div>
          `).join('')}
        </div>
      `;
    }
  } catch(e) {
    empruntsEl.innerHTML = `<p class="text-sm text-slate-400 text-center py-4">Impossible de charger les emprunts</p>`;
  }

  document.getElementById('modal-detail').classList.remove('hidden');
}

function closeDetail() {
  document.getElementById('modal-detail').classList.add('hidden');
}

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

loadAll();
</script>
</body>
</html>