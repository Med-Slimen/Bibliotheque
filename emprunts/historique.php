<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent', 'bibliothecaire', 'administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mes emprunts — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
  .input-field {
    width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem;
    padding: 0.65rem 1rem; font-size: 0.875rem; transition: all 0.2s; background: #f8fafc;
  }
  .input-field:focus { outline: none; border-color: #f59e0b; background: white; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-5xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Mes emprunts</h1>
      <p class="text-slate-500 mt-1" id="emp-count">Chargement…</p>
    </div>

    <!-- Stats pills -->
    <div class="flex flex-wrap gap-3 mb-6">
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">En cours : </span>
        <span class="font-bold text-amber-600" id="st-encours">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">En retard : </span>
        <span class="font-bold text-red-500" id="st-retard">—</span>
      </div>
      <div class="bg-white border border-slate-200 rounded-2xl px-5 py-3 shadow-sm text-sm">
        <span class="text-slate-400">Retournés : </span>
        <span class="font-bold text-green-600" id="st-retournes">—</span>
      </div>
    </div>

    <!-- Abonnement info -->
    <div id="abo-banner" class="mb-6 hidden"></div>

    <!-- Filtre -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5 flex flex-col sm:flex-row gap-3">
      <input type="text" id="search-input" placeholder="Rechercher un document…"
        oninput="filterTable()" class="input-field flex-1">
      <select id="filter-statut" onchange="filterTable()" class="input-field sm:w-44">
        <option value="">Tous les statuts</option>
        <option value="en_cours">En cours</option>
        <option value="en_retard">En retard</option>
        <option value="retourne">Retournés</option>
      </select>
    </div>

    <!-- Liste emprunts -->
    <div class="space-y-3" id="emprunts-list">
      <div class="text-center py-12 text-slate-400">Chargement…</div>
    </div>

  </div>
</main>

<script>
let allEmprunts = [];
const userId    = <?= $_SESSION['user_id'] ?? 0 ?>;

async function loadAll() {
  const [emprunts, abo] = await Promise.all([
    fetch(`/api/emprunts.php?action=historique&id=${userId}`).then(r => r.json()),
    fetch('/api/abonnements.php?action=mon').then(r => r.json()),
  ]);

  allEmprunts = emprunts;
  filterTable();

  // Stats
  const encours   = emprunts.filter(e => e.statut === 'en_cours').length;
  const retard    = emprunts.filter(e => e.statut === 'en_retard').length;
  const retournes = emprunts.filter(e => e.statut === 'retourne').length;

  document.getElementById('st-encours').textContent   = encours;
  document.getElementById('st-retard').textContent    = retard;
  document.getElementById('st-retournes').textContent = retournes;
  document.getElementById('emp-count').textContent    =
    `${emprunts.length} emprunt(s) au total`;

  // Bannière abonnement
  const banner = document.getElementById('abo-banner');
  if (abo) {
    const isActif = abo.etat === 'actif';
    banner.className = `mb-6 px-5 py-4 rounded-2xl border text-sm flex items-center justify-between gap-4 ${
      isActif
        ? 'bg-green-50 border-green-200 text-green-700'
        : 'bg-red-50 border-red-200 text-red-700'
    }`;
    banner.innerHTML = `
      <div class="flex items-center gap-3">
        <span class="text-xl">${isActif ? '✓' : '⚠'}</span>
        <div>
          <p class="font-semibold">
            Abonnement ${isActif ? 'actif' : 'expiré'}
          </p>
          <p class="text-xs opacity-75">
            Valable du ${abo.date_debut} au ${abo.date_fin}
            ${isActif ? `— encore ${abo.jours_restants} jour(s)` : ''}
          </p>
        </div>
      </div>
      <span class="text-xs font-semibold px-3 py-1.5 rounded-full ${
        isActif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
      }">${parseFloat(abo.montant).toFixed(2)} DT</span>
    `;
    banner.classList.remove('hidden');
  } else {
    banner.className = 'mb-6 px-5 py-4 rounded-2xl border bg-red-50 border-red-200 text-red-700 text-sm';
    banner.innerHTML = `
      <div class="flex items-center gap-3">
        <span class="text-xl">⚠</span>
        <p class="font-semibold">Aucun abonnement enregistré — contactez la bibliothèque</p>
      </div>`;
    banner.classList.remove('hidden');
  }
}

function filterTable() {
  const q      = document.getElementById('search-input').value.toLowerCase();
  const statut = document.getElementById('filter-statut').value;
  const res    = allEmprunts.filter(e =>
    (!statut || e.statut === statut) &&
    e.titre.toLowerCase().includes(q)
  );
  renderList(res);
}

function renderList(data) {
  const el = document.getElementById('emprunts-list');

  if (!data.length) {
    el.innerHTML = `
      <div class="text-center py-16">
        <p class="text-slate-400 text-lg mb-2">Aucun emprunt trouvé</p>
        <p class="text-slate-300 text-sm">Consultez le catalogue pour emprunter un document</p>
        <a href="/documents/list.php"
           class="inline-block mt-4 px-5 py-2.5 rounded-xl text-sm font-semibold
                  text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
          Voir le catalogue →
        </a>
      </div>`;
    return;
  }

  el.innerHTML = data.map(e => {
    const isLate = e.statut === 'en_retard';
    const isDone = e.statut === 'retourne';
    const isLivre = e.type_doc === 'livre';

    // Calcul jours restants / retard
    let delaiInfo = '';
    if (!isDone) {
      const today    = new Date();
      const retour   = new Date(e.date_retour_prevue);
      const diffDays = Math.round((retour - today) / (1000 * 60 * 60 * 24));
      if (isLate) {
        delaiInfo = `<span class="text-xs text-red-500 font-medium">⚠ ${Math.abs(diffDays)} jour(s) de retard</span>`;
      } else {
        delaiInfo = `<span class="text-xs text-slate-400">Retour dans ${diffDays} jour(s)</span>`;
      }
    }

    return `
    <div class="bg-white border rounded-2xl p-5 shadow-sm transition hover:shadow-md ${
      isLate ? 'border-red-200' : isDone ? 'border-slate-100' : 'border-slate-200'
    }">
      <div class="flex items-start gap-4">

        <!-- Icône doc -->
        <div class="w-11 h-11 rounded-xl flex-shrink-0 flex items-center justify-center mt-0.5 ${
          isLivre ? 'bg-blue-50' : 'bg-purple-50'
        }">
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
          <div class="flex items-start justify-between gap-3 flex-wrap">
            <h3 class="font-semibold text-slate-800 text-sm leading-snug">${e.titre}</h3>
            <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-medium ${
              isLate ? 'bg-red-100 text-red-700'    :
              isDone ? 'bg-green-100 text-green-700' :
                       'bg-amber-100 text-amber-700'
            }">${e.statut.replace('_', ' ')}</span>
          </div>

          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
            <span class="text-xs px-2 py-0.5 rounded-full ${
              isLivre ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'
            }">${isLivre ? '📖 Livre' : '📰 Revue'}</span>
            <span class="text-xs text-slate-400">Emprunté le ${e.date_emprunt}</span>
            <span class="text-xs text-slate-400">Retour prévu : ${e.date_retour_prevue}</span>
            ${e.date_retour_effective
              ? `<span class="text-xs text-green-600">✓ Retourné le ${e.date_retour_effective}</span>`
              : ''}
          </div>

          <div class="mt-2">${delaiInfo}</div>
        </div>

      </div>
    </div>`;
  }).join('');
}

loadAll();
</script>
</body>
</html>