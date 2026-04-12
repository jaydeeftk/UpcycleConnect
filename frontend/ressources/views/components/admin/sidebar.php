<?php
$sections = [
    'PILOTAGE' => [
        ['url' => '/admin/dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
        ['url' => '/admin/finances', 'icon' => 'fas fa-chart-line', 'label' => 'Finances & Stats'],
    ],
    'VALIDATIONS' => [
        ['url' => '/admin/annonces', 'icon' => 'fas fa-bullhorn', 'label' => 'Annonces'],
        ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box-open', 'label' => 'Dépôts Objets'],
        ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap', 'label' => 'Formations'],
        ['url' => '/admin/conseils', 'icon' => 'fas fa-lightbulb', 'label' => 'Conseils'],
    ],
    'CATALOGUE & OFFRES' => [
        ['url' => '/admin/services', 'icon' => 'fas fa-concierge-bell', 'label' => 'Prestations'],
        ['url' => '/admin/categories', 'icon' => 'fas fa-tags', 'label' => 'Catégories'],
        ['url' => '/admin/evenements', 'icon' => 'fas fa-calendar-alt', 'label' => 'Événements'],
    ],
    'LOGISTIQUE & B2B' => [
        ['url' => '/admin/utilisateurs', 'icon' => 'fas fa-users', 'label' => 'Utilisateurs'],
        ['url' => '/admin/contrats', 'icon' => 'fas fa-file-contract', 'label' => 'Contrats Pro'],
        ['url' => '/admin/factures', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Factures'],
    ],
    'COMMUNICATION' => [
        ['url' => '/admin/forum', 'icon' => 'fas fa-comments', 'label' => 'Modération Forum'],
        ['url' => '/admin/messages', 'icon' => 'fas fa-envelope-open-text', 'label' => 'Messages Internes'],
    ],
    'CONFIGURATION' => [
        ['url' => '/admin/parametres', 'icon' => 'fas fa-cog', 'label' => 'Paramètres'],
    ]
];

$current = $_SERVER['REQUEST_URI'];
foreach ($sections as $title => $items): ?>
    <div class="mb-6">
        <h3 class="sb-section px-8 mb-4 text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] whitespace-nowrap"><?= $title ?></h3>
        <div class="px-4 space-y-1">
            <?php foreach ($items as $item): 
                $isActive = (strpos($current, $item['url']) !== false);
            ?>
                <a href="<?= $item['url'] ?>" 
                   class="nav-link group flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-200 
                   <?= $isActive ? 'active' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white' ?>">
                    <i class="<?= $item['icon'] ?> text-lg min-w-[20px] text-center"></i>
                    <span class="sb-text text-sm font-medium whitespace-nowrap"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>