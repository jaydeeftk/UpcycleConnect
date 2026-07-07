<?php $__proActive = getCleanPath(); ?>
<aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
    <div class="p-6 border-b border-gray-700">
        <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
        <p class="text-xs text-gray-400 mt-1"><?= t('pro_space', 'Espace Professionnel') ?></p>
    </div>
    <nav class="flex-1 p-4">
        <ul class="space-y-1">
            <?php
            $__proNavItems = [
                ['/professionnel', 'fa-tachometer-alt', 'pro_nav_dashboard', 'Tableau de bord', true, null],
                ['/professionnel/recuperation', 'fa-recycle', 'pro_nav_recuperation', 'Récupération', false, null],
                ['/professionnel/projets/create', 'fa-project-diagram', 'pro_nav_new_project', 'Nouveau projet', false, '/professionnel/projets'],
                ['/professionnel/annonces', 'fa-bullhorn', 'pro_nav_annonces', 'Annonces', false, null],
                ['/professionnel/services', 'fa-store', 'pro_nav_services', 'Mes prestations créées', false, null],
                ['/professionnel/prestations', 'fa-tools', 'pro_nav_prestations', 'Demandes reçues', false, null],
                ['/professionnel/commissions', 'fa-hand-holding-usd', 'pro_nav_commissions', 'Mes commissions', false, null],
                ['/professionnel/abonnement', 'fa-crown', 'pro_nav_abonnement', 'Abonnement Premium', false, null],
                ['/professionnel/publicites', 'fa-ad', 'pro_nav_publicites', 'Campagnes publicitaires', false, null],
                ['/messagerie', 'fa-comment-dots', 'pro_nav_messagerie', 'Messagerie', false, null],
            ];
            foreach ($__proNavItems as [$href, $icon, $key, $label, $exact, $matchPrefix]):
                $prefix = $matchPrefix ?? $href;
                $isActive = $exact ? ($__proActive === $href) : str_starts_with($__proActive, $prefix);
            ?>
                <li>
                    <a href="<?= $href ?>" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $isActive ? 'bg-gray-700 text-white' : 'hover:bg-gray-700' ?>">
                        <i class="fas <?= $icon ?> w-5"></i><span><?= t($key, $label) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="p-4 border-t border-gray-700">
        <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
            <i class="fas fa-sign-out-alt w-5"></i><span><?= t('pro_nav_logout', 'Déconnexion') ?></span>
        </a>
    </div>
</aside>
