<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Espace — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Mon Espace</h1>
      <p class="text-slate-500 mt-1">
        Bonjour, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋
      </p>
    </div>

    <!-- Cards stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

      <!-- Abonnement -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Abonnement</p>
        <p class="text-lg font-bold text-slate-800" id="abo-statut">—</p>
        <p class="text-xs text-slate-400 mt-1" id="abo-detail">Chargement…</p>
      </div>

      <!-- Emprunts en cours -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                 C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                 C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                 C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">En cours</p>
        <p class="text-3xl font-bold text-slate-800" id="nb-encours">—</p>
        <p class="text-xs text-slate-400 mt-1">livre(s) emprunté(s)</p>
      </div>

      <!-- En retard -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">En retard</p>
        <p class="text-3xl font-bold text-red-500" id="nb-retard">—</p>
        <p class="text-xs text-slate-400 mt-1">à retourner d'urgence</p>
      </div>

    </div>

    <!-- Two columns -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

      <!-- Emprunts en cours (col large) -->
      <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
          <h2 class="font-semibold text-slate-800">Mes emprunts en cours</h2>
          <a href="/emprunts/historique.php"
             class="text-xs text-amber-500 hover:text-amber-600 font-medium">Tout voir →</a>
        </div>
        <div id="emprunts-list">
          <div class="p-8 text-center text-slate-400 text-sm">Chargement…</div>
        </div>
      </div>

      <!-- Colonne droite -->
      <div class="lg:col-span-2 space-y-5">

        <!-- Info abonnement -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <h2 class="font-semibold text-slate-800 mb-4">Mon abonnement</h2>
          <div id="abo-info">
            <div class="text-center text-slate-400 text-sm py-4">Chargement…</div>
          </div>
        </div>

        <!-- Accès rapide -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <h2 class="font-semibold text-slate-800 mb-4">Accès rapide</h2>
          <div class="space-y-2">
            <a href="/documents/list.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 hover:bg-blue-100 transition">
              <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                     C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                     C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                     C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              <span class="text-sm font-medium text-blue-700">Parcourir le catalogue</span>
            </a>
            <a href="/documents/search.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
              <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <span class="text-sm font-medium text-slate-600">Rechercher un document</span>
            </a>
            <a href="/emprunts/historique.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition">
              <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm font-medium text-slate-600">Historique des emprunts</span>
            </a>
          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<script>
async function loadDashboard() {
  const [emprunts, abo] = await Promise.all([
    fetch('/api/emprunts.php?action=historique').then(r => r.json()),
    fetch('/api/abonnements.php?action=mon').then(r => r.json()),
  ]);

  // Stats emprunts
  const enCours  = emprunts.filter(e => e.statut === 'en_cours').length;
  const enRetard = emprunts.filter(e => e.statut === 'en_retard').length;

  document.getElementById('nb-encours').textContent = enCours;
  document.getElementById('nb-retard').textContent  = enRetard;

  // Liste emprunts actifs
  const actifs = emprunts.filter(e => e.statut !== 'retourne');
  const listEl = document.getElementById('emprunts-list');

  if (!actifs.length) {
    listEl.innerHTML = `
      <div class="p-8 text-center">
        <p class="text-slate-400 text-sm mb-3">Aucun emprunt en cours</p>
        <a href="/documents/list.php"
           class="text-sm text-amber-500 hover:text-amber-600 font-medium">
          Parcourir le catalogue →
        </a>
      </div>`;
  } else {
    listEl.innerHTML = actifs.map(e => `
      <div class="flex items-center justify-between px-5 py-4
                  border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center
                      ${e.type_doc === 'livre' ? 'bg-blue-50 text-blue-500' : 'bg-purple-50 text-purple-500'}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                   C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                   C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                   C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-slate-800 max-w-[220px] truncate">${e.titre}</p>
            <p class="text-xs text-slate-400">
              Retour prévu : <span class="${e.statut === 'en_retard' ? 'text-red-500 font-medium' : ''}">
                ${e.date_retour_prevue}
              </span>
            </p>
          </div>
        </div>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0 ${
          e.statut === 'en_retard' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'
        }">${e.statut.replace('_', ' ')}</span>
      </div>
    `).join('');
  }

  // Abonnement
  const aboEl = document.getElementById('abo-info');
  if (!abo) {
    document.getElementById('abo-statut').textContent  = 'Aucun';
    document.getElementById('abo-detail').textContent  = 'Pas d\'abonnement';
    aboEl.innerHTML = `
      <div class="text-center py-3">
        <p class="text-sm text-red-500 font-medium">Aucun abonnement actif</p>
        <p class="text-xs text-slate-400 mt-1">Contactez la bibliothèque</p>
      </div>`;
  } else {
    const actif = abo.etat === 'actif';
    const jours = parseInt(abo.jours_restants);

    document.getElementById('abo-statut').textContent = actif ? '✓ Actif' : '✗ Expiré';
    document.getElementById('abo-statut').className   =
      'text-lg font-bold ' + (actif ? 'text-green-600' : 'text-red-600');
    document.getElementById('abo-detail').textContent =
      actif ? `${jours} jour(s) restants` : `Expiré le ${abo.date_fin}`;

    aboEl.innerHTML = `
      <div class="space-y-3">
        <div class="flex justify-between text-xs">
          <span class="text-slate-400">Début</span>
          <span class="font-medium text-slate-700">${abo.date_debut}</span>
        </div>
        <div class="flex justify-between text-xs">
          <span class="text-slate-400">Fin</span>
          <span class="font-medium ${actif ? 'text-slate-700' : 'text-red-600'}">${abo.date_fin}</span>
        </div>
        <div class="flex justify-between text-xs">
          <span class="text-slate-400">Montant</span>
          <span class="font-medium text-slate-700">${parseFloat(abo.montant).toFixed(2)} DT</span>
        </div>
        ${actif ? `
        <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full rounded-full bg-green-400 transition-all"
               style="width: ${Math.min(100, Math.round(jours / 365 * 100))}%">
          </div>
        </div>
        <p class="text-xs text-slate-400 text-center">${jours} jour(s) restants</p>
        ` : ''}
      </div>`;
  }
}

loadDashboard();
</script>

</body>
</html>