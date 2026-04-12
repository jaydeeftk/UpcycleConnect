<header class="bg-base-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-5 flex items-center justify-between">

        <div class="text-2xl font-bold tracking-tight">
            <a href="/" class="flex items-center gap-2">
                <i class="fas fa-recycle text-green-500 text-3xl"></i>
                UpcycleConnect
            </a>
        </div>

        <nav class="hidden md:flex items-center gap-8 text-sm font-medium">

            <a href="/" class="hover:text-primary transition">
                Accueil
            </a>

            <div class="dropdown dropdown-hover" data-tuto="prestations">
                <div tabindex="0" role="button" class="cursor-pointer hover:text-primary transition flex items-center gap-2">
                    Catalogue
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/catalogue/services" class="flex items-center gap-3">
                            <i class="fas fa-tools text-orange-500"></i>
                            <div>
                                <div class="font-medium">Services</div>
                                <div class="text-xs text-base-content/60">Réparation, transformation...</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/catalogue/formations" class="flex items-center gap-3">
                            <i class="fas fa-graduation-cap text-purple-500"></i>
                            <div>
                                <div class="font-medium">Formations</div>
                                <div class="text-xs text-base-content/60">Ateliers, cours, workshops...</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/catalogue/evenements" class="flex items-center gap-3">
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
                <div tabindex="0" role="button" class="cursor-pointer hover:text-primary transition flex items-center gap-2">
                    Objets
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/annonces" class="flex items-center gap-3">
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
                <div tabindex="0" role="button" class="cursor-pointer hover:text-primary transition flex items-center gap-2">
                    Déposer
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-64 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/annonces/create" class="flex items-center gap-3">
                            <i class="fas fa-bullhorn text-green-500"></i>
                            <div>
                                <div class="font-medium">Déposer une annonce</div>
                                <div class="text-xs text-base-content/60">Don ou vente d'un objet</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="/conteneurs/create" class="flex items-center gap-3">
                            <i class="fas fa-box-open text-blue-500"></i>
                            <div>
                                <div class="font-medium">Déposer dans un conteneur</div>
                                <div class="text-xs text-base-content/60">Demande de dépôt d'objet</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <a href="/conseils" class="hover:text-primary transition" data-tuto="conseils">
                Conseils
            </a>

            <a href="/a-propos" class="hover:text-primary transition">
                À propos
            </a>

        </nav>

        <div class="flex items-center gap-4">

            <?php include __DIR__ . '/darkmode.php'; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <div class="relative">
                    <button id="user-menu-btn" class="flex items-center gap-2 text-sm font-medium">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Mon compte') ?></span>
                    </button>
                    <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-base-100 rounded-xl shadow-lg border border-base-300 py-2 hidden z-50">
                        <a href="/mes-demandes" class="block px-4 py-2 text-sm hover:bg-base-200">Mes demandes</a>
                        <a href="/mes-prestations" class="block px-4 py-2 text-sm hover:bg-base-200">Mes prestations</a>
                        <a href="/planning" class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="fas fa-calendar-alt text-blue-500 mr-1"></i> Mon Planning
                        </a>
                        <a href="/score" class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="fas fa-leaf text-emerald-500 mr-1"></i> Mon Upcycling Score
                        </a>
                        <a href="/paiements" class="block px-4 py-2 text-sm hover:bg-base-200">Paiements</a>
                        <a href="/messages" class="block px-4 py-2 text-sm hover:bg-base-200">
                            <i class="fas fa-envelope text-blue-500 mr-1"></i> Mes messages
                        </a>
                        <div class="border-t border-base-300 my-1"></div>
                        <a href="/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-base-200">Déconnexion</a>
                    </div>
                </div>
                <script>
                    const btn = document.getElementById('user-menu-btn');
                    const dropdown = document.getElementById('user-menu-dropdown');
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dropdown.classList.toggle('hidden');
                    });
                    document.addEventListener('click', function() {
                        dropdown.classList.add('hidden');
                    });
                </script>
            <?php else: ?>
                <a href="/login"
                    class="bg-black text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-neutral-800 transition">
                    Inscription / Connexion
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="/admin/dashboard" class="nav-link">
                    <i class="fas fa-cog"></i> Administration
                </a>
            <?php endif; ?>

        </div>
    </div>
</header>