<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Administration - UpcycleConnect' ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        #sidebar.sidebar-collapsed { width: 5rem !important; }

        .sidebar-text, .sidebar-group-title { 
            transition: opacity 0.2s ease-in-out; 
            white-space: nowrap; 
            opacity: 1;
        }
        #sidebar.sidebar-collapsed .sidebar-text,
        #sidebar.sidebar-collapsed .sidebar-group-title { 
            opacity: 0; 
            visibility: hidden; 
            width: 0;
            height: 0;
            margin: 0;
            padding: 0;
        }
        #sidebar.sidebar-collapsed .group-divider {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin: 1rem 1rem;
        }

        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-track { background: transparent; }
        #sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 4px; }
        #sidebar:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">

        <aside id="sidebar" class="w-64 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-slate-300 flex-shrink-0 overflow-y-auto z-20 shadow-xl relative">

            <script>
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    document.getElementById('sidebar').classList.add('sidebar-collapsed');
                }
            </script>

            <div class="h-16 flex items-center px-5 border-b border-white/10 sticky top-0 bg-slate-900/80 backdrop-blur-sm z-10">
                <i class="fas fa-recycle text-3xl text-emerald-500 drop-shadow-[0_0_8px_rgba(16,185,129,0.5)]"></i>
                <span class="sidebar-text ml-3 text-lg font-bold text-white tracking-wide">UpcycleConnect</span>
            </div>

            <nav class="p-3 pb-20">
                <?php 
                $menuGroups = [
                    'Pilotage' => [
                        ['url' => '/admin/dashboard', 'icon' => 'fa-tachometer-alt', 'label' => 'Tableau de bord'],
                        ['url' => '/admin/finances', 'icon' => 'fa-chart-line', 'label' => 'Finances & Stats'],
                    ],
                    'Validations (En attente)' => [
                        ['url' => '/admin/annonces', 'icon' => 'fa-bullhorn', 'label' => 'Annonces'],
                        ['url' => '/admin/demandes', 'icon' => 'fa-box-open', 'label' => 'Dépôts Conteneurs'],
                        ['url' => '/admin/formations', 'icon' => 'fa-graduation-cap', 'label' => 'Formations'],
                        ['url' => '/admin/conseils', 'icon' => 'fa-lightbulb', 'label' => 'Conseils'],
                    ],
                    'Catalogue & Offres' => [
                        ['url' => '/admin/services', 'icon' => 'fa-handshake', 'label' => 'Prestations / Services'],
                        ['url' => '/admin/categories', 'icon' => 'fa-tags', 'label' => 'Catégories'],
                        ['url' => '/admin/evenements', 'icon' => 'fa-calendar-alt', 'label' => 'Événements'],
                    ],
                    'Logistique & B2B' => [
                        ['url' => '/admin/utilisateurs', 'icon' => 'fa-users', 'label' => 'Utilisateurs'],
                        ['url' => '/admin/conteneurs', 'icon' => 'fa-dumpster', 'label' => 'Conteneurs & Box'],
                        ['url' => '/admin/contrats', 'icon' => 'fa-file-signature', 'label' => 'Contrats Pro'],
                        ['url' => '/admin/factures', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Factures'],
                    ],
                    'Communication' => [
                        ['url' => '/admin/forum', 'icon' => 'fa-comments', 'label' => 'Modération Forum'],
                        ['url' => '/admin/messages', 'icon' => 'fa-envelope', 'label' => 'Messages Internes'],
                        ['url' => '/admin/notifications', 'icon' => 'fa-bell', 'label' => 'Notifications Push'],
                        ['url' => '/admin/planning', 'icon' => 'fa-calendar-check', 'label' => 'Planning Global'],
                    ]
                ];

                foreach ($menuGroups as $groupName => $links) {
                    echo '<div class="group-divider"></div>';
                    echo '<div class="sidebar-group-title text-xs font-bold text-slate-500 uppercase tracking-wider mt-5 mb-2 px-3">' . $groupName . '</div>';
                    echo '<ul class="space-y-1.5">';

                    foreach ($links as $link) {
                        $isActive = strpos($_SERVER['REQUEST_URI'] ?? '', $link['url']) !== false;
                        if ($isActive) {
                            $activeClass = 'bg-gradient-to-r from-emerald-500/20 to-transparent text-emerald-400 border-l-4 border-emerald-500 shadow-[inset_0_1px_0_rgba(255,255,255,0.1)]';
                            $iconClass = 'text-emerald-400';
                        } else {
                            $activeClass = 'text-slate-400 hover:bg-white/5 hover:text-white border-l-4 border-transparent';
                            $iconClass = 'text-slate-500 group-hover:text-emerald-400';
                        }
                    ?>
                    <li>
                        <a href="<?= $link['url'] ?>" class="group flex items-center px-3 py-2.5 rounded-r-lg transition-all duration-200 <?= $activeClass ?>" title="<?= $link['label'] ?>">
                            <div class="w-8 flex justify-center">
                                <i class="fas <?= $link['icon'] ?> text-lg transition-transform duration-300 group-hover:scale-110 <?= $iconClass ?>"></i>
                            </div>
                            <span class="sidebar-text ml-3 font-medium"><?= $link['label'] ?></span>
                        </a>
                    </li>
                    <?php 
                    }
                    echo '</ul>';
                } 
                ?>

                <div class="border-t border-white/10 mt-6 pt-4 mb-4">
                    <ul class="space-y-1.5">
                        <li>
                            <a href="/admin/parametres" class="group flex items-center px-3 py-2.5 rounded-r-lg text-slate-400 hover:bg-white/5 hover:text-white border-l-4 border-transparent transition-all duration-200" title="Paramètres">
                                <div class="w-8 flex justify-center"><i class="fas fa-cog text-lg transition-transform duration-300 group-hover:rotate-90 group-hover:text-white text-slate-500"></i></div>
                                <span class="sidebar-text ml-3 font-medium">Paramètres</span>
                            </a>
                        </li>
                        <li>
                            <a href="/" target="_blank" class="group flex items-center px-3 py-2.5 rounded-r-lg text-slate-400 hover:bg-white/5 hover:text-white border-l-4 border-transparent transition-all duration-200" title="Voir le site">
                                <div class="w-8 flex justify-center"><i class="fas fa-external-link-alt text-lg transition-transform duration-300 group-hover:-translate-y-1 group-hover:translate-x-1 group-hover:text-white text-slate-500"></i></div>
                                <span class="sidebar-text ml-3 font-medium">Voir le site</span>
                            </a>
                        </li>
                        <li>
                            <a href="/logout" class="group flex items-center px-3 py-2.5 mt-2 rounded-r-lg text-rose-400/80 hover:bg-rose-500/10 hover:text-rose-400 border-l-4 border-transparent hover:border-rose-500 transition-all duration-200" title="Déconnexion">
                                <div class="w-8 flex justify-center"><i class="fas fa-sign-out-alt text-lg transition-transform duration-300 group-hover:scale-110"></i></div>
                                <span class="sidebar-text ml-3 font-medium">Déconnexion</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden bg-slate-50">
            <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 shrink-0 shadow-sm z-10">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="mr-5 p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl focus:outline-none transition-all duration-200">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <div>
                        <h2 class="text-xl font-bold text-slate-800 leading-tight"><?= $page_title ?? 'Dashboard' ?></h2>
                        <?php if (isset($page_subtitle)) { ?>
                            <p class="text-slate-500 text-xs mt-0.5"><?= $page_subtitle ?></p>
                        <?php } ?>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="relative" id="notif-container">
                        <button onclick="toggleNotifDropdown()" class="relative p-2 text-slate-400 hover:text-emerald-600 transition-colors duration-200 hover:bg-emerald-50 rounded-xl">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notif-badge" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
                        </button>
                        <div id="notif-dropdown" class="hidden absolute right-0 top-full mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                                <span class="font-semibold text-sm text-slate-800">Notifications</span>
                                <a href="/admin/notifications" class="text-xs text-emerald-600 hover:underline">Gérer</a>
                            </div>
                            <div id="notif-list" class="max-h-72 overflow-y-auto">
                                <div class="p-4 text-center text-slate-400 text-sm">Chargement...</div>
                            </div>
                            <div class="px-4 py-3 border-t border-slate-100 text-center">
                                <a href="/admin/notifications" class="text-xs text-slate-500 hover:text-slate-800">Voir toutes les notifications</a>
                            </div>
                        </div>
                    </div>
                    <script>
                    function toggleNotifDropdown() {
                        const dd = document.getElementById('notif-dropdown');
                        dd.classList.toggle('hidden');
                        if (!dd.classList.contains('hidden')) {
                            fetch('/api/admin/notifications', {headers: {'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?? '' ?>'}})
                                .then(r => r.json())
                                .then(data => {
                                    const list = data.data || data || [];
                                    const el = document.getElementById('notif-list');
                                    if (!list.length) { el.innerHTML = '<div class="p-4 text-center text-slate-400 text-sm">Aucune notification</div>'; return; }
                                    el.innerHTML = list.slice(0,5).map(n => `
                                        <div class="px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                                            <p class="text-sm font-medium text-slate-800">${n.titre || n.message || ''}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">${n.date || ''}</p>
                                        </div>`).join('');
                                }).catch(() => {
                                    document.getElementById('notif-list').innerHTML = '<div class="p-4 text-center text-slate-400 text-sm">Aucune notification</div>';
                                });
                        }
                    }
                    document.addEventListener('click', function(e) {
                        if (!document.getElementById('notif-container').contains(e.target)) {
                            document.getElementById('notif-dropdown').classList.add('hidden');
                        }
                    });
                    </script>

                    <div class="relative flex items-center pl-4 border-l border-slate-200 cursor-pointer" id="profile-container">
                        <button onclick="document.getElementById('profile-dropdown').classList.toggle('hidden')" class="flex items-center gap-3 hover:opacity-80 transition">
                            <div class="w-9 h-9 bg-gradient-to-tr from-emerald-500 to-teal-400 rounded-full flex items-center justify-center text-white font-bold shadow-md shadow-emerald-500/20">
                                <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-slate-700 leading-none"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                                <p class="text-xs text-emerald-600 font-medium mt-1">Administrateur</p>
                            </div>
                        </button>
                        <div id="profile-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                                <p class="text-xs text-slate-400">Administrateur</p>
                            </div>
                            <a href="/admin/parametres" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Paramètres</a>
                            <a href="/" target="_blank" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Voir le site</a>
                            <a href="/logout" class="block px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 border-t border-slate-100">Déconnexion</a>
                        </div>
                    </div>
                    <script>
                    document.addEventListener('click', function(e) {
                        if (!document.getElementById('profile-container').contains(e.target)) {
                            document.getElementById('profile-dropdown').classList.add('hidden');
                        }
                    });
                    </script>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <?php if (isset($error)) { ?>
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-700 p-4 rounded-lg shadow-sm mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <p class="font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
                <?php } ?>

                <?php if (isset($success)) { ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-lg shadow-sm mb-6 flex items-center">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <p class="font-medium"><?= htmlspecialchars($success) ?></p>
                </div>
                <?php } ?>

                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed'));
            });
        });
    </script>
</body>
</html>