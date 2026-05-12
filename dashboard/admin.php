<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administration — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
  .stat-card {
    background: white;
    border-radius: 1.25rem;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
  }
  .stat-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transform: translateY(-2px);
  }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Tableau de bord</h1>
      <p class="text-slate-500 mt-1">
        Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋
        — <?= date('l d F Y') ?>
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

      <!-- Documents -->
      <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
          <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                   C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                   C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                   C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <a href="/admin/documents.php"
             class="text-xs text-blue-500 hover:text-blue-700 font-medium">Voir →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="s-docs">—</p>
        <p class="text-sm text-slate-500 mt-1">Documents</p>
        <p class="text-xs text-slate-400 mt-1" id="s-docs-sub">…</p>
      </div>

      <!-- Utilisateurs -->
      <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
          <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                   c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857
                   M7 20v-2c0-.656.126-1.283.356-1.857
                   m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <a href="/admin/utilisateurs.php"
             class="text-xs text-amber-500 hover:text-amber-700 font-medium">Voir →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="s-users">—</p>
        <p class="text-sm text-slate-500 mt-1">Utilisateurs</p>
        <p class="text-xs text-slate-400 mt-1" id="s-users-sub">…</p>
      </div>

      <!-- Prêts -->
      <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
          <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7
                   a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2
                   M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <a href="/admin/emprunts.php"
             class="text-xs text-green-500 hover:text-green-700 font-medium">Voir →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="s-prets">—</p>
        <p class="text-sm text-slate-500 mt-1">Prêts actifs</p>
        <p class="text-xs text-red-400 mt-1" id="s-retards">…</p>
      </div>

      <!-- Abonnements -->
      <div class="stat-card">
        <div class="flex items-center justify-between mb-4">
          <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8
                   a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <a href="/admin/abonnements.php"
             class="text-xs text-purple-500 hover:text-purple-700 font-medium">Voir →</a>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="s-abos">—</p>
        <p class="text-sm text-slate-500 mt-1">Abonnements actifs</p>
        <p class="text-xs text-slate-400 mt-1" id="s-abos-sub">…</p>
      </div>

    </div>

    <!-- Two columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Derniers emprunts -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
          <h2 class="font-semibold text-slate-800">Derniers prêts</h2>
          <a href="/admin/emprunts.php"
             class="text-xs text-amber-500 hover:text-amber-600 font-medium">Voir tout →</a>
        </div>
        <div id="recent-emprunts">
          <div class="p-8 text-center text-slate-400 text-sm">Chargement…</div>
        </div>
      </div>

      <!-- Colonne droite -->
      <div class="space-y-5">

        <!-- Accès rapide -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <h2 class="font-semibold text-slate-800 mb-4">Accès rapide</h2>
          <div class="grid grid-cols-2 gap-2">
            <a href="/admin/documents.php"
               class="flex flex-col items-center p-3 rounded-xl bg-blue-50 hover:bg-blue-100 transition text-center">
              <svg class="w-5 h-5 text-blue-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              <span class="text-xs font-medium text-blue-700 leading-tight">Ajouter<br>document</span>
            </a>
            <a href="/admin/utilisateurs.php"
               class="flex flex-col items-center p-3 rounded-xl bg-amber-50 hover:bg-amber-100 transition text-center">
              <svg class="w-5 h-5 text-amber-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0z
                     M3 20a6 6 0 0112 0v1H3v-1z"/>
              </svg>
              <span class="text-xs font-medium text-amber-700 leading-tight">Nouvel<br>utilisateur</span>
            </a>
            <a href="/admin/abonnements.php"
               class="flex flex-col items-center p-3 rounded-xl bg-green-50 hover:bg-green-100 transition text-center">
              <svg class="w-5 h-5 text-green-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              <span class="text-xs font-medium text-green-700 leading-tight">Abonne-<br>ments</span>
            </a>
            <a href="/admin/stats.php"
               class="flex flex-col items-center p-3 rounded-xl bg-purple-50 hover:bg-purple-100 transition text-center">
              <svg class="w-5 h-5 text-purple-500 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0
                     V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0
                     V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <span class="text-xs font-medium text-purple-700 leading-tight">Statis-<br>tiques</span>
            </a>
          </div>
        </div>

        <!-- Prêts en retard -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
          <div class="flex items-center gap-2 p-5 border-b border-slate-100">
            <span class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></span>
            <h2 class="font-semibold text-slate-800">Prêts en retard</h2>
          </div>
          <div id="retards-list">
            <div class="p-5 text-center text-slate-400 text-sm">Chargement…</div>
          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<script>
async function loadStats() {
  const [docs, users, prets, abos] = await Promise.all([
    fetch('/api/documents.php?action=stats').then(r => r.json()),
    fetch('/api/utilisateurs.php?action=stats').then(r => r.json()),
    fetch('/api/emprunts.php?action=stats').then(r => r.json()),
    fetch('/api/abonnements.php?action=stats').then(r => r.json()),
  ]);

  document.getElementById('s-docs').textContent     = docs.total;
  document.getElementById('s-docs-sub').textContent = `${docs.livres} livres · ${docs.revues} revues`;

  document.getElementById('s-users').textContent     = users.total;
  document.getElementById('s-users-sub').textContent = `${users.adherents} adhérents actifs`;

  document.getElementById('s-prets').textContent  = parseInt(prets.en_cours) + parseInt(prets.en_retard);
  document.getElementById('s-retards').textContent = `⚠ ${prets.en_retard} en retard`;

  document.getElementById('s-abos').textContent     = abos.actifs;
  document.getElementById('s-abos-sub').textContent = `${abos.expires} expirés`;
}

async function loadRecentEmprunts() {
  const data = await fetch('/api/emprunts.php?action=all').then(r => r.json());
  const el   = document.getElementById('recent-emprunts');

  if (!data.length) {
    el.innerHTML = '<p class="p-6 text-center text-slate-400 text-sm">Aucun emprunt enregistré</p>';
    return;
  }

  el.innerHTML = data.slice(0, 6).map(e => `
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/70 transition">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
          ${e.prenom_adherent.charAt(0)}${e.nom_adherent.charAt(0)}
        </div>
        <div>
          <p class="text-sm font-medium text-slate-800">
            ${e.prenom_adherent} ${e.nom_adherent}
          </p>
          <p class="text-xs text-slate-400 max-w-xs truncate">${e.titre}</p>
        </div>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        <span class="text-xs text-slate-400">${e.date_emprunt}</span>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium ${
          e.statut === 'en_retard' ? 'bg-red-100 text-red-700'    :
          e.statut === 'retourne'  ? 'bg-green-100 text-green-700':
                                     'bg-amber-100 text-amber-700'
        }">${e.statut.replace('_', ' ')}</span>
      </div>
    </div>
  `).join('');
}

async function loadRetards() {
  const data = await fetch('/api/emprunts.php?action=all&statut=en_retard').then(r => r.json());
  const el   = document.getElementById('retards-list');

  if (!data.length) {
    el.innerHTML = '<p class="p-5 text-center text-green-500 text-sm">✓ Aucun retard en cours</p>';
    return;
  }

  el.innerHTML = data.slice(0, 4).map(e => `
    <div class="px-5 py-3.5 border-b border-slate-50 last:border-0">
      <p class="text-sm font-medium text-slate-800">
        ${e.prenom_adherent} ${e.nom_adherent}
      </p>
      <p class="text-xs text-slate-400 truncate">${e.titre}</p>
      <p class="text-xs text-red-500 mt-0.5">
        Retour prévu : ${e.date_retour_prevue}
      </p>
    </div>
  `).join('');
}

// Charger tout
loadStats();
loadRecentEmprunts();
loadRetards();
</script>

</body>
</html>