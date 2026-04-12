<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Salarié - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1">Espace Salarié</p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/salarie"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false ? 'bg-gray-700' : '' ?>">
                        <i class="fas fa-tachometer-alt w-5"></i><span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="/salarie/formations/create"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'formations') !== false ? 'bg-gray-700' : '' ?>">
                        <i class="fas fa-graduation-cap w-5"></i><span>Créer une formation</span>
                    </a>
                </li>
                <li>
                    <a href="/salarie/conseils/create"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'conseils') !== false ? 'bg-gray-700' : '' ?>">
                        <i class="fas fa-lightbulb w-5"></i><span>Publier un conseil</span>
                    </a>
                </li>
                <li>
                    <a href="/salarie/planning"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'planning') !== false ? 'bg-gray-700' : '' ?>">
                        <i class="fas fa-calendar-alt w-5"></i><span>Mon planning</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
                    <p class="text-gray-600 text-sm">Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom'] ?? '') ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'S', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                        <p class="text-xs text-gray-500">Salarié</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="/salarie/formations/create" class="bg-white rounded-lg shadow p-6 flex items-center space-x-4 hover:shadow-md transition">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Formations</p>
                        <p class="text-lg font-bold"><?= $stats['formations'] ?? 0 ?> créées</p>
                    </div>
                </a>
                <a href="/salarie/conseils/create" class="bg-white rounded-lg shadow p-6 flex items-center space-x-4 hover:shadow-md transition">
                    <div class="w-14 h-14 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-lightbulb text-2xl text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Conseils</p>
                        <p class="text-lg font-bold"><?= $stats['conseils'] ?? 0 ?> publiés</p>
                    </div>
                </a>
                <a href="/salarie/planning" class="bg-white rounded-lg shadow p-6 flex items-center space-x-4 hover:shadow-md transition">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Planning</p>
                        <p class="text-lg font-bold"><?= $stats['events_semaine'] ?? 0 ?> cette semaine</p>
                    </div>
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Actions rapides</h3>
                <div class="flex flex-wrap gap-4">
                    <a href="/salarie/formations/create"
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-plus mr-2"></i>Nouvelle formation
                    </a>
                    <a href="/salarie/conseils/create"
                        class="bg-yellow-500 text-white px-6 py-3 rounded-lg hover:bg-yellow-600 transition">
                        <i class="fas fa-pen mr-2"></i>Nouveau conseil
                    </a>
                    <a href="/salarie/planning"
                        class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-calendar mr-2"></i>Voir mon planning
                    </a>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>