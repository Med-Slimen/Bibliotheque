<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role        = $_SESSION['user_role'] ?? '';
$userName    = $_SESSION['user_name'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$roleLabels = [
    'administrateur' => 'Administrateur',
    'bibliothecaire' => 'Bibliothécaire',
    'adherent'       => 'Adhérent',
    'visiteur'       => 'Visiteur'
];

$roleColors = [
    'administrateur' => 'bg-red-500',
    'bibliothecaire' => 'bg-blue-500',
    'adherent'       => 'bg-green-500',
    'visiteur'       => 'bg-slate-500'
];

$navItems = [];
if ($role === 'administrateur') {
    $navItems = [
        ['icon' => 'grid',       'label' => 'Tableau de bord',  'href' => '/dashboard/admin.php',       'page' => 'admin'],
        ['icon' => 'users',      'label' => 'Utilisateurs',     'href' => '/admin/utilisateurs.php',    'page' => 'utilisateurs'],
        ['icon' => 'book-open',  'label' => 'Documents',        'href' => '/admin/documents.php',       'page' => 'documents'],
        ['icon' => 'clipboard',  'label' => 'Prêts',            'href' => '/admin/emprunts.php',        'page' => 'emprunts'],
        ['icon' => 'credit-card','label' => 'Abonnements',      'href' => '/admin/abonnements.php',     'page' => 'abonnements'],
        ['icon' => 'users',      'label' => 'Adhérents',        'href' => '/admin/adherents.php',       'page' => 'adherents'],
        ['icon' => 'bar-chart',  'label' => 'Statistiques',     'href' => '/admin/stats.php',           'page' => 'stats'],
    ];
} elseif ($role === 'bibliothecaire') {
    $navItems = [
        ['icon' => 'grid',       'label' => 'Tableau de bord',  'href' => '/dashboard/bibliothecaire.php', 'page' => 'bibliothecaire'],
        ['icon' => 'book-open',  'label' => 'Documents',        'href' => '/admin/documents.php',          'page' => 'documents'],
        ['icon' => 'clipboard',  'label' => 'Prêts en cours',   'href' => '/admin/emprunts.php',           'page' => 'emprunts'],
        ['icon' => 'users',      'label' => 'Adhérents',        'href' => '/admin/adherents.php',          'page' => 'adherents'],
    ];
} elseif ($role === 'adherent') {
    $navItems = [
        ['icon' => 'grid',       'label' => 'Mon espace',       'href' => '/dashboard/adherent.php',    'page' => 'adherent'],
        ['icon' => 'book-open',  'label' => 'Catalogue',        'href' => '/documents/list.php',        'page' => 'list'],
        ['icon' => 'search',     'label' => 'Recherche',        'href' => '/documents/search.php',      'page' => 'search'],
        ['icon' => 'clock',      'label' => 'Mes emprunts',     'href' => '/emprunts/historique.php',   'page' => 'historique'],
    ];
}
?>

<aside class="w-64 bg-slate-900 text-white flex flex-col flex-shrink-0 h-screen sticky top-0">

    <!-- Logo -->
    <div class="p-5 border-b border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                           C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                           C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                           C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm leading-tight">Bibliothèque</p>
                <p class="text-xs text-slate-400 leading-tight">Faculté des Sciences</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-slate-700/60">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl <?= $roleColors[$role] ?? 'bg-slate-600' ?> flex items-center justify-center text-sm font-bold flex-shrink-0">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium truncate"><?= htmlspecialchars($userName) ?></p>
                <p class="text-xs text-slate-400"><?= $roleLabels[$role] ?? '' ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-150
                  <?= $currentPage === $item['page']
                      ? 'bg-amber-500 text-white font-medium'
                      : 'text-slate-300 hover:bg-slate-800 hover:text-white' ?>">
            <?= getNavIcon($item['icon']) ?>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Logout -->
    <div class="p-3 border-t border-slate-700/60">
        <a href="/api/auth.php?action=logout"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-400
                  hover:bg-red-500/10 transition-all duration-150">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7
                       a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Déconnexion
        </a>
    </div>
</aside>

<?php
function getNavIcon($name) {
    $icons = [
        'grid' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z
                           M14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z
                           M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z
                           M14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',

        'users' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                           M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                           m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',

        'book-open' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                           C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                           C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13
                           C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',

        'credit-card' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6
                           a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',

        'bar-chart' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0
                           V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0
                           V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',

        'clipboard' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2
                           M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',

        'search' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',

        'clock' => '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ];
    return $icons[$name] ?? '';
}
?>