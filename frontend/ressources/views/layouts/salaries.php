<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Espace Salarié - UpcycleConnect' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">

        <aside class="w-64 bg-green-900 text-white flex-shrink-0 overflow-y-auto">
            <div class="p-6 border-b border-green-700">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-recycle text-3xl text-green-400"></i>
                    <div>
                        <h1 class="text-xl font-bold">UpcycleConnect</h1>
                        <p class="text-sm text-green-300">Espace Salarié</p>
                    </div>
                </div>
            </div>

            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/dashboard"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/conseils"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'conseils') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-lightbulb"></i>
                            <span>Conseils</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/formations"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'formations') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Formations</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/evenements"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'evenements') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Événements</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/ateliers"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'ateliers') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-tools"></i>
                            <span>Ateliers</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/planning"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'planning') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-calendar-week"></i>
                            <span>Planning</span>
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/salaries/forum"
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'forum') !== false ? 'bg-green-700' : '' ?>">
                            <i class="fas fa-comments"></i>
                            <span>Modération forum</span>
                        </a>
                    </li>
                </ul>

                <div class="border-t border-green-700 mt-6 pt-6">
                    <ul class="space-y-2">
                        <li>
                            <a href="/UpcycleConnect-PA2526/frontend/public/" target="_blank"
                               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition">
                                <i class="fas fa-external-link-alt"></i>
                                <span>Voir le site</span>
                            </a>
                        </li>
                        <li>
                            <a href="/UpcycleConnect-PA2526/frontend/public/logout"
                               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-300 hover:text-white">
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
                        <h2 class="text-2xl font-bold text-gray-800"><?= $page_title ?? 'Tableau de bord' ?></h2>
                        <p class="text-gray-600 text-sm"><?= $page_subtitle ?? '' ?></p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="relative group">
                            <button class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded-lg">
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'S', 0, 1)) ?>
                                </div>
                                <div class="text-left hidden md:block">
                                    <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Salarié') ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user']['poste'] ?? 'Salarié') ?></p>
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
</body>
</html>