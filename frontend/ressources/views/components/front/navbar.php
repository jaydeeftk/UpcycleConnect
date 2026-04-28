<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function navActive(string $path, string $current): string {
    if ($path === '/' && $current === '/') return 'text-primary font-semibold';
    if ($path !== '/' && str_starts_with($current, $path)) return 'text-primary font-semibold';
    return 'hover:text-primary';
}
?>

<header class="bg-base-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-5 flex items-center justify-between">

        <div class="text-2xl font-bold tracking-tight">
            <a href="/" class="flex items-center gap-2">
                <i class="fas fa-recycle text-green-500 text-3xl"></i>
                UpcycleConnect
            </a>
        </div>

        <nav class="hidden md:flex items-center gap-8 text-sm font-medium">

            <a href="/" class="transition <?= navActive('/', $currentPath) ?>">
                Accueil
            </a>

            <div class="dropdown dropdown-hover" data-tuto="prestations">
                <div tabindex="0" role="button"
                     class="cursor-pointer transition flex items-center gap-2 <?= str_starts_with($currentPath, '/catalogue') ? 'text-primary font-semibold' : 'hover:text-primary' ?>">
                    Catalogue
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/catalogue/services" class="flex items-center gap-3 <?= $currentPath === '/catalogue/services' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-tools text-orange-500"></i>
                            <div>
                                <div class="font-medium">Services</div>
                                <div class="text-xs text-base-content/60">Réparation, transformation...</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/catalogue/formations" class="flex items-center gap-3 <?= $currentPath === '/catalogue/formations' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-graduation-cap text-purple-500"></i>
                            <div>
                                <div class="font-medium">Formations</div>
                                <div class="text-xs text-base-content/60">Ateliers, cours, workshops...</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/catalogue/evenements" class="flex items-center gap-3 <?= $currentPath === '/catalogue/evenements' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-calendar-alt text-blue-500"></i>
                            <div>
                                <div class="font-medium">Événements</div>
                                <div class="text-xs text-base-content/60">Rencontres, expos, marchés...</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button"
                     class="cursor-pointer transition flex items-center gap-2 <?= str_starts_with($currentPath, '/annonces') ? 'text-primary font-semibold' : 'hover:text-primary' ?>">
                    Objets
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/annonces" class="flex items-center gap-3 <?= $currentPath === '/annonces' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-bullhorn text-green-500"></i>
                            <div>
                                <div class="font-medium">Toutes les annonces</div>
                                <div class="text-xs text-base-content/60">Dons et ventes disponibles</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="dropdown dropdown-hover" data-tuto="deposer">
                <div tabindex="0" role="button"
                     class="cursor-pointer transition flex items-center gap-2 <?= str_starts_with($currentPath, '/conteneurs') || str_starts_with($currentPath, '/annonces/create') ? 'text-primary font-semibold' : 'hover:text-primary' ?>">
                    Déposer
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/annonces/create" class="flex items-center gap-3 <?= $currentPath === '/annonces/create' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-bullhorn text-green-500"></i>
                            <div>
                                <div class="font-medium">Déposer une annonce</div>
                                <div class="text-xs text-base-content/60">Don ou vente d'un objet</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/conteneurs/create" class="flex items-center gap-3 <?= $currentPath === '/conteneurs/create' ? 'bg-base-200' : '' ?>">
                            <i class="fas fa-box-open text-blue-500"></i>
                            <div>
                                <div class="font-medium">Déposer dans un conteneur</div>
                                <div class="text-xs text-base-content/60">Demande de dépôt d'objet</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <a href="/conseils" class="transition <?= navActive('/conseils', $currentPath) ?>" data-tuto="conseils">
                Conseils
            </a>

            <a href="/a-propos" class="transition <?= navActive('/a-propos', $currentPath) ?>">
                À propos
            </a>

        </nav>

        <div class="flex items-center gap-4">

            <?php include __DIR__ . '/darkmode.php'; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <div class="relative">
                    <button id="user-menu-btn" class="flex items-center gap-2 text-sm font-medium hover:text-primary transition">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                            <i class="fas fa-user text-primary text-sm"></i>
                        </div>
                        <span><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Mon compte') ?></span>
                        <i class="fas fa-chevron-down text-xs text-base-content/40"></i>
                    </button>
                    <div id="user-menu-dropdown"
                         class="absolute right-0 mt-2 w-52 bg-base-100 rounded-xl shadow-lg border border-base-300 py-2 hidden z-50 animate-in">
                        <div class="px-4 py-2 border-b border-base-200 mb-1">
                            <p class="text-xs text-base-content/50">Connecté en tant que</p>
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                        </div>
                        <a href="/mes-demandes" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200 <?= str_starts_with($currentPath, '/mes-demandes') ? 'text-primary' : '' ?>">
                            <i class="fas fa-clipboard-list w-4 text-center"></i> Mes demandes
                        </a>
                        <a href="/mes-prestations" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200 <?= str_starts_with($currentPath, '/mes-prestations') ? 'text-primary' : '' ?>">
                            <i class="fas fa-briefcase w-4 text-center"></i> Mes prestations
                        </a>
                        <a href="/planning" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200 <?= str_starts_with($currentPath, '/planning') ? 'text-primary' : '' ?>">
                            <i class="fas fa-calendar-alt text-blue-500 w-4 text-center"></i> Mon Planning
                        </a>
                        <a href="/score" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200 <?= str_starts_with($currentPath, '/score') ? 'text-primary' : '' ?>">
                            <i class="fas fa-leaf text-emerald-500 w-4 text-center"></i> Mon Score
                        </a>
                        <a href="/messages" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200 <?= str_starts_with($currentPath, '/messages') ? 'text-primary' : '' ?>">
                            <i class="fas fa-envelope text-blue-500 w-4 text-center"></i> Mes messages
                        </a>
                        <a href="/paiements" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-base-200">
                            <i class="fas fa-credit-card w-4 text-center"></i> Paiements
                        </a>
                        <div class="border-t border-base-300 my-1"></div>
                        <a href="/logout" class="flex items-center gap-2 px-4 py-2 text-sm text-red-500 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-4 text-center"></i> Déconnexion
                        </a>
                    </div>
                </div>
                <script>
                    (function() {
                        const btn = document.getElementById('user-menu-btn');
                        const dropdown = document.getElementById('user-menu-dropdown');
                        btn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            dropdown.classList.toggle('hidden');
                        });
                        document.addEventListener('click', function() {
                            dropdown.classList.add('hidden');
                        });
                    })();
                </script>
            <?php else: ?>
                <a href="/login"
                   class="bg-black text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-neutral-800 transition">
                    Connexion
                </a>
                <a href="/register"
                   class="bg-primary text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:opacity-90 transition hidden sm:inline-block">
                    S'inscrire
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/admin/dashboard"
                   class="flex items-center gap-2 text-sm font-medium bg-orange-50 text-orange-600 px-4 py-2 rounded-xl hover:bg-orange-100 transition">
                    <i class="fas fa-cog"></i> Administration
                </a>
            <?php endif; ?>

        </div>

        <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-base-200 transition">
            <i class="fas fa-bars text-lg"></i>
        </button>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t border-base-200 bg-base-100 px-6 py-4 space-y-3 text-sm font-medium">
        <a href="/" class="block py-2 <?= navActive('/', $currentPath) ?>">Accueil</a>
        <a href="/catalogue/services" class="block py-2 <?= navActive('/catalogue/services', $currentPath) ?>">Services</a>
        <a href="/catalogue/formations" class="block py-2 <?= navActive('/catalogue/formations', $currentPath) ?>">Formations</a>
        <a href="/catalogue/evenements" class="block py-2 <?= navActive('/catalogue/evenements', $currentPath) ?>">Événements</a>
        <a href="/annonces" class="block py-2 <?= navActive('/annonces', $currentPath) ?>">Annonces</a>
        <a href="/annonces/create" class="block py-2 <?= navActive('/annonces/create', $currentPath) ?>">Déposer une annonce</a>
        <a href="/conteneurs/create" class="block py-2 <?= navActive('/conteneurs/create', $currentPath) ?>">Déposer dans un conteneur</a>
        <a href="/conseils" class="block py-2 <?= navActive('/conseils', $currentPath) ?>">Conseils</a>
        <a href="/a-propos" class="block py-2 <?= navActive('/a-propos', $currentPath) ?>">À propos</a>
        <?php if (!isset($_SESSION['user'])): ?>
            <div class="pt-2 border-t border-base-200 flex gap-3">
                <a href="/login" class="btn btn-sm btn-ghost flex-1">Connexion</a>
                <a href="/register" class="btn btn-sm btn-neutral flex-1">S'inscrire</a>
            </div>
        <?php else: ?>
            <a href="/logout" class="block py-2 text-red-500">Déconnexion</a>
        <?php endif; ?>
    </div>

    <script>
        (function() {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileBtn) {
                mobileBtn.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
            }
        })();
    </script>
</header>