<div class="flex flex-col h-full">
    <div class="px-6 py-4 space-y-6 sidebar-scroll no-scrollbar overflow-y-auto">
        <?php
        $sections = [
            'PILOTAGE' => [
                ['url' => '/admin/dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Tableau de bord'],
                ['url' => '/admin/finances', 'icon' => 'fas fa-chart-line', 'label' => 'Finances & Stats'],
            ],
            'VALIDATIONS (EN ATTENTE)' => [
                ['url' => '/admin/annonces', 'icon' => 'fas fa-bullhorn', 'label' => 'Annonces'],
                ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box-open', 'label' => 'Dépôts Conteneurs'],
                ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap', 'label' => 'Formations'],
                ['url' => '/admin/conseils', 'icon' => 'fas fa-lightbulb', 'label' => 'Conseils'],
            ],
            'CATALOGUE & OFFRES' => [
                ['url' => '/admin/services', 'icon' => 'fas fa-concierge-bell', 'label' => 'Prestations / Services'],
                ['url' => '/admin/categories', 'icon' => 'fas fa-tags', 'label' => 'Catégories'],
                ['url' => '/admin/evenements', 'icon' => 'fas fa-calendar-alt', 'label' => 'Événements'],
            ],
            'LOGISTIQUE & B2B' => [
                ['url' => '/admin/utilisateurs', 'icon' => 'fas fa-users', 'label' => 'Utilisateurs'],
                ['url' => '/admin/conteneurs-box', 'icon' => 'fas fa-boxes', 'label' => 'Conteneurs & Box'],
                ['url' => '/admin/contrats', 'icon' => 'fas fa-file-contract', 'label' => 'Contrats Pro'],
                ['url' => '/admin/factures', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Factures'],
            ],
            'COMMUNICATION' => [
                ['url' => '/admin/forum', 'icon' => 'fas fa-comments', 'label' => 'Modération Forum'],
                ['url' => '/admin/messages', 'icon' => 'fas fa-envelope-open-text', 'label' => 'Messages Internes'],
            ]
        ];

        $current = $_SERVER['REQUEST_URI'];
        foreach ($sections as $title => $items): ?>
            <div class="pt-4">
                <h3 class="px-4 mb-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]"><?= $title ?></h3>
                <div class="space-y-1">
                    <?php foreach ($items as $item): 
                        $isActive = (strpos($current, $item['url']) !== false);
                    ?>
                        <a href="<?= $item['url'] ?>" 
                           class="group flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-300 
                           <?= $isActive ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                            <i class="<?= $item['icon'] ?> w-5 text-sm <?= $isActive ? 'text-white' : 'group-hover:text-emerald-400' ?>"></i>
                            <span class="text-[13px] font-medium"><?= $item['label'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>