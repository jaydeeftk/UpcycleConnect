<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Administration - UpcycleConnect' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 overflow-y-auto">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-recycle text-3xl text-green-500"></i>
                    <div>
                        <h1 class="text-xl font-bold">UpcycleConnect</h1>
                        <p class="text-sm text-gray-400">Administration</p>
                    </div>
                </div>
            </div>

<nav class="p-4">
    <ul class="space-y-2">
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'utilisateurs') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'annonces') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Annonces</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/conteneurs" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'conteneurs') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-box-open"></i>
                <span>Dépôts (Objets)</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/categories" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'categories') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-folder"></i>
                <span>Catégories</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/evenements" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'evenements') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Événements</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/messages" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'messages') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/formations" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'formations') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-graduation-cap"></i>
                <span>Formations</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/contrats" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'contrats') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-file-contract"></i>
                <span>Contrats</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/factures" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'factures') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-file-invoice"></i>
                <span>Factures</span>
            </a>
        </li>
        <li>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/notifications" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'notifications') !== false ? 'bg-gray-700' : '' ?>">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </li>
    </ul>

    <div class="border-t border-gray-700 mt-6 pt-6">
        <ul class="space-y-2">
            <li>
                <a href="/UpcycleConnect-PA2526/frontend/public/admin/parametres" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'parametres') !== false ? 'bg-gray-700' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            <li>
                <a href="/UpcycleConnect-PA2526/frontend/public/" target="_blank" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Voir le site</span>
                </a>
            </li>
            <li>
                <a href="/UpcycleConnect-PA2526/frontend/public/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
        
            <header class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800"><?= $page_title ?? 'Dashboard' ?></h2>
                        <p class="text-gray-600 text-sm"><?= $page_subtitle ?? 'Vue d\'ensemble de votre activité' ?></p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full"></span>
                            </button>
                        </div>

                        <div class="relative group">
                            <button class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">
                                    A
                                </div>
                                <div class="text-left hidden md:block">
                                    <p class="text-sm font-semibold">Admin</p>
                                    <p class="text-xs text-gray-500">Administrateur</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-confirm]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm || 'Êtes-vous sûr ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>