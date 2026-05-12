<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Utilisateurs — Bibliothèque</title>
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
        <h1 class="playfair text-3xl font-bold text-slate-800">Gestion des utilisateurs</h1>
        <p class="text-slate-500 mt-1" id="user-count">Chargement…</p>
      </div>
      <button onclick="openModal('add')"
        class="btn-primary flex items-center gap-2 self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        Ajouter un utilisateur
      </button>
    </div>

    <!-- Stats Pills -->
    <div class="flex flex-wrap gap-3 mb-6" id="stats-pills">
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Total : </span>
        <span class="font-bold text-slate-800" id="st-total">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Adhérents actifs : </span>
        <span class="font-bold text-green-600" id="st-adherents">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Bibliothécaires : </span>
        <span class="font-bold text-blue-600" id="st-biblio">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Administrateurs : </span>
        <span class="font-bold text-red-600" id="st-admins">—</span>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
      <input type="text" id="search-input" placeholder="Rechercher par nom, prénom, email…"
        oninput="filterUsers()" class="input-field flex-1">
      <select id="filter-role" onchange="loadUsers()" class="input-field sm:w-48">
        <option value="">Tous les rôles</option>
        <option value="administrateur">Administrateurs</option>
        <option value="bibliothecaire">Bibliothécaires</option>
        <option value="adherent">Adhérents</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Utilisateur</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Rôle</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Téléphone</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Emprunts</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Abonnement</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Inscrit le</th>
              <th class="text-left px-5 py-3 text-slate-500 font-medium text-xs uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="users-tbody">
            <tr><td colspan="7" class="text-center py-12 text-slate-400">Chargement…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- ═══════════════════════════ MODAL ADD/EDIT ═══════════════════════════ -->
<div id="modal-user" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg">

    <div class="flex items-center justify-between p-6 border-b border-slate-100">
      <h3 class="font-bold text-slate-800 text-lg" id="modal-title">Ajouter un utilisateur</h3>
      <button onclick="closeModal()"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="p-6 space-y-4">

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Prénom *</label>
          <input type="text" id="f-prenom" class="input-field" placeholder="Prénom">
        </div>
        <div>
          <label class="label">Nom *</label>
          <input type="text" id="f-nom" class="input-field" placeholder="Nom">
        </div>
      </div>

      <div>
        <label class="label">Email *</label>
        <input type="email" id="f-email" class="input-field" placeholder="email@exemple.com">
      </div>

      <div>
        <label class="label">Rôle *</label>
        <select id="f-role" class="input-field" onchange="toggleAdherentFields()">
          <option value="adherent">Adhérent</option>
          <option value="bibliothecaire">Bibliothécaire</option>
          <option value="administrateur">Administrateur</option>
        </select>
      </div>

      <!-- Champs adhérent -->
      <div id="adherent-fields" class="grid grid-cols-2 gap-4">
        <div>
          <label class="label">Téléphone</label>
          <input type="tel" id="f-telephone" class="input-field" placeholder="55 000 000">
        </div>
        <div id="status-field" class="hidden">
          <label class="label">Statut</label>
          <select id="f-status" class="input-field">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>
        </div>
      </div>

      <!-- Mot de passe (add uniquement) -->
      <div id="password-field">
        <label class="label">Mot de passe *</label>
        <input type="password" id="f-password" class="input-field" placeholder="Min. 6 caractères">
      </div>

      <!-- Erreur -->
      <div id="form-error"
        class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
      </div>

    </div>

    <div class="p-6 pt-0 flex gap-3">
      <button onclick="closeModal()"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="saveUser()" id="btn-save" class="flex-1 btn-primary text-center">
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
    <h3 class="font-bold text-slate-800 text-lg mb-2">Supprimer cet utilisateur ?</h3>
    <p class="text-slate-500 text-sm mb-7" id="delete-name">—</p>
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

<!-- ═══════════════════════════ MODAL PASSWORD ═══════════════════════════ -->
<div id="modal-pwd" class="fixed inset-0 modal-overlay z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm">
    <div class="flex items-center justify-between p-6 border-b border-slate-100">
      <h3 class="font-bold text-slate-800">Changer le mot de passe</h3>
      <button onclick="document.getElementById('modal-pwd').classList.add('hidden')"
        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    <div class="p-6 space-y-4">
      <div>
        <label class="label">Nouveau mot de passe</label>
        <input type="password" id="pwd-new" class="input-field" placeholder="Min. 6 caractères">
      </div>
      <div id="pwd-error"
        class="hidden bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm">
      </div>
    </div>
    <div class="p-6 pt-0 flex gap-3">
      <button onclick="document.getElementById('modal-pwd').classList.add('hidden')"
        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600
               text-sm font-medium hover:bg-slate-50 transition">
        Annuler
      </button>
      <button onclick="savePassword()"
        class="flex-1 btn-primary text-center">
        Enregistrer
      </button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast"
  class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-2xl
         text-white text-sm font-medium shadow-xl"></div>

<script>
let allUsers = [];
let deleteId = null;
let editId   = null;
let pwdId    = null;

const roleConfig = {
  administrateur: { label: 'Administrateur', bg: 'bg-red-100',   text: 'text-red-700'   },
  bibliothecaire: { label: 'Bibliothécaire', bg: 'bg-blue-100',  text: 'text-blue-700'  },
  adherent:       { label: 'Adhérent',       bg: 'bg-green-100', text: 'text-green-700' },
  visiteur:       { label: 'Visiteur',       bg: 'bg-slate-100', text: 'text-slate-700' },
};

// ── LOAD ──────────────────────────────────────────────────
async function loadUsers() {
  const role = document.getElementById('filter-role').value;
  const url  = '/api/utilisateurs.php?action=list' + (role ? '&role=' + role : '');
  allUsers   = await fetch(url).then(r => r.json());
  renderTable(allUsers);
  document.getElementById('user-count').textContent =
    `${allUsers.length} utilisateur(s) trouvé(s)`;
}

async function loadStats() {
  const s = await fetch('/api/utilisateurs.php?action=stats').then(r => r.json());
  document.getElementById('st-total').textContent    = s.total;
  document.getElementById('st-adherents').textContent = s.adherents;
  document.getElementById('st-biblio').textContent   = s.bibliothecaires;
  document.getElementById('st-admins').textContent   = s.admins;
}

// ── FILTER ────────────────────────────────────────────────
function filterUsers() {
  const q   = document.getElementById('search-input').value.toLowerCase();
  const res = allUsers.filter(u =>
    (u.nom + ' ' + u.prenom).toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q)
  );
  renderTable(res);
}

// ── RENDER TABLE ──────────────────────────────────────────
function renderTable(data) {
  const tbody = document.getElementById('users-tbody');
  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-12 text-slate-400">Aucun utilisateur trouvé</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(u => {
    const rc   = roleConfig[u.role] || roleConfig.visiteur;
    const init = (u.prenom.charAt(0) + u.nom.charAt(0)).toUpperCase();
    const avatarColors = {
      administrateur: 'bg-red-100 text-red-600',
      bibliothecaire: 'bg-blue-100 text-blue-600',
      adherent:       'bg-green-100 text-green-600',
    };
    return `
    <tr class="border-t border-slate-50 hover:bg-slate-50/70 transition">
      <td class="px-5 py-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
                      text-xs font-bold ${avatarColors[u.role] || 'bg-slate-100 text-slate-600'}">
            ${init}
          </div>
          <div>
            <p class="font-medium text-slate-800 text-sm">${u.prenom} ${u.nom}</p>
            <p class="text-xs text-slate-400">${u.email}</p>
          </div>
        </div>
      </td>
      <td class="px-5 py-4">
        <span class="px-2.5 py-1 rounded-full text-xs font-medium ${rc.bg} ${rc.text}">
          ${rc.label}
        </span>
      </td>
      <td class="px-5 py-4 text-slate-500 text-sm">
        ${u.telephone || '<span class="text-slate-300">—</span>'}
      </td>
      <td class="px-5 py-4">
        ${u.emprunts_actifs > 0
          ? `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
               ${u.emprunts_actifs} en cours
             </span>`
          : `<span class="text-xs text-slate-300">—</span>`}
      </td>
      <td class="px-5 py-4">
        ${u.role === 'adherent'
          ? (u.abonnement_actif > 0
              ? `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">✓ Actif</span>`
              : `<span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">✗ Expiré</span>`)
          : `<span class="text-xs text-slate-300">—</span>`}
      </td>
      <td class="px-5 py-4 text-slate-400 text-xs">
        ${u.created_at ? u.created_at.substring(0,10) : '—'}
      </td>
      <td class="px-5 py-4">
        <div class="flex items-center gap-1.5">
          <button onclick="openModal('edit', ${u.id})"
            class="p-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-500 transition"
            title="Modifier">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                   m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
          <button onclick="openPwdModal(${u.id})"
            class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-500 transition"
            title="Changer mot de passe">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4
                   a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </button>
          <button onclick="initDelete(${u.id}, '${u.prenom} ${u.nom}')"
            class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-500 transition"
            title="Supprimer">
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

// ── MODAL ADD/EDIT ─────────────────────────────────────────
async function openModal(mode, id = null) {
  editId = id;
  resetForm();

  document.getElementById('modal-title').textContent =
    mode === 'add' ? 'Ajouter un utilisateur' : 'Modifier l\'utilisateur';

  // Cacher/montrer le champ mot de passe
  document.getElementById('password-field').style.display = mode === 'add' ? 'block' : 'none';

  if (mode === 'edit' && id) {
    const u = await fetch(`/api/utilisateurs.php?action=detail&id=${id}`).then(r => r.json());
    document.getElementById('f-prenom').value    = u.prenom    || '';
    document.getElementById('f-nom').value       = u.nom       || '';
    document.getElementById('f-email').value     = u.email     || '';
    document.getElementById('f-role').value      = u.role      || 'adherent';
    document.getElementById('f-telephone').value = u.telephone || '';
    document.getElementById('f-status').value    = u.adherent_status || 'actif';
    toggleAdherentFields();
    document.getElementById('status-field').classList.remove('hidden');
  }

  document.getElementById('modal-user').classList.remove('hidden');
}

function resetForm() {
  ['f-prenom','f-nom','f-email','f-telephone','f-password'].forEach(id => {
    document.getElementById(id).value = '';
  });
  document.getElementById('f-role').value   = 'adherent';
  document.getElementById('f-status').value = 'actif';
  document.getElementById('form-error').classList.add('hidden');
  document.getElementById('status-field').classList.add('hidden');
  toggleAdherentFields();
}

function toggleAdherentFields() {
  const role = document.getElementById('f-role').value;
  document.getElementById('adherent-fields').style.display =
    role === 'adherent' ? 'grid' : 'none';
}

function closeModal() {
  document.getElementById('modal-user').classList.add('hidden');
  editId = null;
}

// ── SAVE ──────────────────────────────────────────────────
async function saveUser() {
  const prenom = document.getElementById('f-prenom').value.trim();
  const nom    = document.getElementById('f-nom').value.trim();
  const email  = document.getElementById('f-email').value.trim();
  const role   = document.getElementById('f-role').value;
  const errEl  = document.getElementById('form-error');

  if (!prenom || !nom || !email) {
    errEl.textContent = 'Prénom, nom et email sont obligatoires';
    errEl.classList.remove('hidden');
    return;
  }

  const payload = {
    prenom, nom, email, role,
    telephone: document.getElementById('f-telephone').value,
    status:    document.getElementById('f-status').value,
  };

  if (!editId) {
    const pwd = document.getElementById('f-password').value;
    if (!pwd || pwd.length < 6) {
      errEl.textContent = 'Mot de passe requis (min. 6 caractères)';
      errEl.classList.remove('hidden');
      return;
    }
    payload.mot_de_passe = pwd;
  } else {
    payload.id = editId;
  }

  const btn = document.getElementById('btn-save');
  btn.textContent = 'Enregistrement…';
  btn.disabled    = true;

  const action = editId ? 'update' : 'create';
  const res    = await fetch(`/api/utilisateurs.php?action=${action}`, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.success) {
    closeModal();
    showToast(editId ? 'Utilisateur modifié !' : 'Utilisateur créé !', 'green');
    loadUsers();
    loadStats();
  } else {
    errEl.textContent = data.message || 'Erreur lors de l\'enregistrement';
    errEl.classList.remove('hidden');
  }

  btn.textContent = 'Enregistrer';
  btn.disabled    = false;
}

// ── DELETE ────────────────────────────────────────────────
function initDelete(id, name) {
  deleteId = id;
  document.getElementById('delete-name').textContent = name;
  document.getElementById('modal-delete').classList.remove('hidden');
}

async function confirmDelete() {
  const res  = await fetch('/api/utilisateurs.php?action=delete', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: deleteId })
  });
  const data = await res.json();
  document.getElementById('modal-delete').classList.add('hidden');

  if (data.success) {
    showToast('Utilisateur supprimé', 'green');
    loadUsers();
    loadStats();
  } else {
    showToast(data.message || 'Erreur lors de la suppression', 'red');
  }
}

// ── PASSWORD ──────────────────────────────────────────────
function openPwdModal(id) {
  pwdId = id;
  document.getElementById('pwd-new').value = '';
  document.getElementById('pwd-error').classList.add('hidden');
  document.getElementById('modal-pwd').classList.remove('hidden');
}

async function savePassword() {
  const pwd   = document.getElementById('pwd-new').value;
  const errEl = document.getElementById('pwd-error');

  if (!pwd || pwd.length < 6) {
    errEl.textContent = 'Mot de passe trop court (min. 6 caractères)';
    errEl.classList.remove('hidden');
    return;
  }

  const res  = await fetch('/api/utilisateurs.php?action=password', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: pwdId, password: pwd })
  });
  const data = await res.json();

  if (data.success) {
    document.getElementById('modal-pwd').classList.add('hidden');
    showToast('Mot de passe mis à jour !', 'green');
  } else {
    errEl.textContent = 'Erreur lors de la mise à jour';
    errEl.classList.remove('hidden');
  }
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

// Fermer modals en cliquant dehors
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});

loadStats();
loadUsers();
</script>
</body>
</html>