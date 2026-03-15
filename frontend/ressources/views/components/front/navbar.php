<header class="bg-base-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-5 flex items-center justify-between">
        
        <div class="text-2xl font-bold tracking-tight">
    <a href="/UpcycleConnect-PA2526/frontend/public/" class="flex items-center gap-2">
        <i class="fas fa-recycle text-green-500 text-3xl"></i>
        UpcycleConnect
    </a>
</div>
        
        <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
            
            <a href="/UpcycleConnect-PA2526/frontend/public/" class="hover:text-primary transition">
                Accueil
            </a>
            
            <div class="dropdown dropdown-hover">
                <div tabindex="0" role="button" class="cursor-pointer hover:text-primary transition flex items-center gap-2">
                    Prestations
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </div>
                
                <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-56 p-2 shadow border border-base-300 mt-2">
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/prestations">
                            Voir les prestations
                        </a>
                    </li>
                    <li>
                        <a href="/UpcycleConnect-PA2526/frontend/public/demande-prestation">
                            Faire une demande
                        </a>
                    </li>
                </ul>
            </div>
            
            <a href="/UpcycleConnect-PA2526/frontend/public/evenements" class="hover:text-primary transition">
                Événements
            </a>
            
            <a href="/UpcycleConnect-PA2526/frontend/public/a-propos" class="hover:text-primary transition">
                À propos
            </a>
            
        </nav>
        
        <div class="flex items-center gap-4">

    <?php include __DIR__ . '/darkmode.php'; ?>

    <?php if (isset($_SESSION['user'])): ?>
        <div class="relative group">
            <button class="flex items-center gap-2 text-sm font-medium">
                <i class="fas fa-user-circle text-xl"></i>
                <span><?= htmlspecialchars($_SESSION['user']['prenom'] ?? 'Mon compte') ?></span>
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-base-100 rounded-xl shadow-lg border border-base-300 py-2 hidden group-hover:block z-50">
                <a href="/UpcycleConnect-PA2526/frontend/public/mes-demandes" class="block px-4 py-2 text-sm hover:bg-base-200">Mes demandes</a>
                <a href="/UpcycleConnect-PA2526/frontend/public/mes-prestations" class="block px-4 py-2 text-sm hover:bg-base-200">Mes prestations</a>
                <a href="/UpcycleConnect-PA2526/frontend/public/paiements" class="block px-4 py-2 text-sm hover:bg-base-200">Paiements</a>
                <div class="border-t border-base-300 my-1"></div>
                <a href="/UpcycleConnect-PA2526/frontend/public/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-base-200">Déconnexion</a>
            </div>
        </div>
    <?php else: ?>
        <a href="/UpcycleConnect-PA2526/frontend/public/login"
            class="bg-black text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-neutral-800 transition">
            Inscription / Connexion
        </a>
    <?php endif; ?>

    <a href="/UpcycleConnect-PA2526/frontend/public/admin/dashboard" class="text-base-content/50 hover:text-base-content" title="Administration">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
        </svg>
    </a>

</div>
</header>