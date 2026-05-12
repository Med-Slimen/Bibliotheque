<?php
require_once '../includes/auth_check.php';
requireAuth(['administrateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistiques — Bibliothèque</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; }
  .playfair { font-family: 'Playfair Display', serif; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex">

<?php require_once '../includes/header.php'; ?>

<main class="flex-1 overflow-auto p-8">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
      <h1 class="playfair text-3xl font-bold text-slate-800">Statistiques</h1>
      <p class="text-slate-500 mt-1">Vue d'ensemble de la bibliothèque</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                 C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                 C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                 C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="kpi-docs">—</p>
        <p class="text-sm text-slate-500 mt-1">Documents</p>
        <p class="text-xs text-slate-400" id="kpi-docs-sub">…</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2
                 c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857
                 M7 20v-2c0-.656.126-1.283.356-1.857
                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="kpi-adherents">—</p>
        <p class="text-sm text-slate-500 mt-1">Adhérents actifs</p>
        <p class="text-xs text-slate-400" id="kpi-adherents-sub">…</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7
                 a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2
                 M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="kpi-prets">—</p>
        <p class="text-sm text-slate-500 mt-1">Prêts actifs</p>
        <p class="text-xs text-red-400" id="kpi-retards">…</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2
                 m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1
                 m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-3xl font-bold text-slate-800" id="kpi-revenus">—</p>
        <p class="text-sm text-slate-500 mt-1">Revenus (DT)</p>
        <p class="text-xs text-slate-400" id="kpi-revenus-sub">…</p>
      </div>

    </div>

    <!-- Charts row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

      <!-- Prêts par statut (Doughnut) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-1">Répartition des prêts</h2>
        <p class="text-xs text-slate-400 mb-5">Par statut actuel</p>
        <div class="flex items-center justify-center" style="height:220px">
          <canvas id="chart-prets"></canvas>
        </div>
        <div class="flex justify-center gap-5 mt-4" id="legend-prets"></div>
      </div>

      <!-- Documents par type (Doughnut) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-1">Répartition des documents</h2>
        <p class="text-xs text-slate-400 mb-5">Livres vs Revues</p>
        <div class="flex items-center justify-center" style="height:220px">
          <canvas id="chart-docs"></canvas>
        </div>
        <div class="flex justify-center gap-5 mt-4" id="legend-docs"></div>
      </div>

    </div>

    <!-- Charts row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

      <!-- Abonnements actifs vs expirés -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-1">Abonnements</h2>
        <p class="text-xs text-slate-400 mb-5">Actifs vs expirés</p>
        <div class="flex items-center justify-center" style="height:200px">
          <canvas id="chart-abos"></canvas>
        </div>
        <div class="flex justify-center gap-5 mt-4" id="legend-abos"></div>
      </div>

      <!-- Top documents empruntés -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-1">Documents les plus empruntés</h2>
        <p class="text-xs text-slate-400 mb-5">Nombre total d'emprunts</p>
        <div id="top-docs">
          <div class="text-center text-slate-400 text-sm py-8">Chargement…</div>
        </div>
      </div>

    </div>

    <!-- Tableau récapitulatif -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
      <h2 class="font-semibold text-slate-800 mb-5">Récapitulatif général</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="recap-grid"></div>
    </div>

  </div>
</main>

<script>
// Couleurs cohérentes
const COLORS = {
  amber:  '#f59e0b',
  blue:   '#3b82f6',
  green:  '#22c55e',
  red:    '#ef4444',
  purple: '#a855f7',
  slate:  '#94a3b8',
};

function makeDoughnut(canvasId, labels, values, colors, legendId) {
  const ctx = document.getElementById(canvasId).getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
      cutout: '68%',
      plugins: { legend: { display: false }, tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label} : ${ctx.parsed}`
        }
      }},
      responsive: true,
      maintainAspectRatio: true,
    }
  });

  // Légende custom
  const legendEl = document.getElementById(legendId);
  if (legendEl) {
    legendEl.innerHTML = labels.map((l, i) => `
      <div class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:${colors[i]}"></span>
        <span class="text-xs text-slate-500">${l} <strong class="text-slate-700">(${values[i]})</strong></span>
      </div>
    `).join('');
  }
}

async function loadStats() {
  const [docs, users, prets, abos] = await Promise.all([
    fetch('/api/documents.php?action=stats').then(r => r.json()),
    fetch('/api/utilisateurs.php?action=stats').then(r => r.json()),
    fetch('/api/emprunts.php?action=stats').then(r => r.json()),
    fetch('/api/abonnements.php?action=stats').then(r => r.json()),
  ]);

  // KPIs
  document.getElementById('kpi-docs').textContent        = docs.total;
  document.getElementById('kpi-docs-sub').textContent    = `${docs.livres} livres · ${docs.revues} revues`;
  document.getElementById('kpi-adherents').textContent   = users.adherents;
  document.getElementById('kpi-adherents-sub').textContent = `${users.total} utilisateurs au total`;
  document.getElementById('kpi-prets').textContent       = parseInt(prets.en_cours) + parseInt(prets.en_retard);
  document.getElementById('kpi-retards').textContent     = `⚠ ${prets.en_retard} en retard`;
  document.getElementById('kpi-revenus').textContent     = parseFloat(abos.revenus).toFixed(0);
  document.getElementById('kpi-revenus-sub').textContent = `${parseFloat(abos.revenus_annee).toFixed(0)} DT cette année`;

  // Chart — Prêts par statut
  makeDoughnut(
    'chart-prets',
    ['En cours', 'En retard', 'Retournés'],
    [
      parseInt(prets.en_cours),
      parseInt(prets.en_retard),
      parseInt(prets.total) - parseInt(prets.en_cours) - parseInt(prets.en_retard)
    ],
    [COLORS.amber, COLORS.red, COLORS.green],
    'legend-prets'
  );

  // Chart — Documents
  makeDoughnut(
    'chart-docs',
    ['Livres', 'Revues'],
    [parseInt(docs.livres), parseInt(docs.revues)],
    [COLORS.blue, COLORS.purple],
    'legend-docs'
  );

  // Chart — Abonnements
  makeDoughnut(
    'chart-abos',
    ['Actifs', 'Expirés'],
    [parseInt(abos.actifs), parseInt(abos.expires)],
    [COLORS.green, COLORS.slate],
    'legend-abos'
  );

  // Récap grid
  document.getElementById('recap-grid').innerHTML = [
    { label: 'Total documents',      value: docs.total,           color: 'text-blue-600'   },
    { label: 'Total utilisateurs',   value: users.total,          color: 'text-slate-800'  },
    { label: 'Total prêts',          value: prets.total,          color: 'text-amber-600'  },
    { label: 'Total abonnements',    value: abos.total,           color: 'text-purple-600' },
    { label: 'Livres',               value: docs.livres,          color: 'text-blue-500'   },
    { label: 'Revues',               value: docs.revues,          color: 'text-purple-500' },
    { label: 'Bibliothécaires',      value: users.bibliothecaires,color: 'text-blue-600'   },
    { label: 'Administrateurs',      value: users.admins,         color: 'text-red-600'    },
  ].map(item => `
    <div class="bg-slate-50 rounded-xl p-4 text-center">
      <p class="text-2xl font-bold ${item.color}">${item.value}</p>
      <p class="text-xs text-slate-400 mt-1">${item.label}</p>
    </div>
  `).join('');
}

async function loadTopDocs() {
  const emprunts = await fetch('/api/emprunts.php?action=all').then(r => r.json());

  // Compter par document
  const counts = {};
  emprunts.forEach(e => {
    if (!counts[e.code_doc]) counts[e.code_doc] = { titre: e.titre, type: e.type_doc, count: 0 };
    counts[e.code_doc].count++;
  });

  const sorted = Object.values(counts).sort((a, b) => b.count - a.count).slice(0, 6);
  const max    = sorted[0]?.count || 1;
  const el     = document.getElementById('top-docs');

  if (!sorted.length) {
    el.innerHTML = `<p class="text-center text-slate-400 text-sm py-4">Aucun emprunt enregistré</p>`;
    return;
  }

  el.innerHTML = sorted.map((d, i) => `
    <div class="flex items-center gap-4 py-2.5 border-b border-slate-50 last:border-0">
      <span class="text-sm font-bold text-slate-300 w-5 flex-shrink-0">${i + 1}</span>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-slate-700 truncate">${d.titre}</p>
        <div class="flex items-center gap-2 mt-1">
          <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full bg-amber-400 transition-all"
                 style="width: ${Math.round(d.count / max * 100)}%"></div>
          </div>
          <span class="text-xs text-slate-400 flex-shrink-0">${d.count} emprunt${d.count > 1 ? 's' : ''}</span>
        </div>
      </div>
      <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0 ${
        d.type === 'livre' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600'
      }">${d.type}</span>
    </div>
  `).join('');
}

loadStats();
loadTopDocs();
</script>
</body>
</html>