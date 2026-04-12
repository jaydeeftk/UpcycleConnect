<div class="px-6 space-y-8">
    <?php
    $sections = [
        'PILOTAGE' => [
            ['url' => '/admin/dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard'],
            ['url' => '/admin/finances', 'icon' => 'fas fa-chart-line', 'label' => 'Finances'],
        ],
        'VALIDATIONS' => [
            ['url' => '/admin/annonces', 'icon' => 'fas fa-bullhorn', 'label' => 'Annonces'],
            ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box-open', 'label' => 'Conteneurs'],
            ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap', 'label' => 'Formations'],
        ],
        'LOGISTIQUE' => [
            ['url' => '/admin/utilisateurs', 'icon' => 'fas fa-users', 'label' => 'Utilisateurs'],
            ['url' => '/admin/contrats', 'icon' => 'fas fa-file-contract', 'label' => 'Contrats Pro'],
        ]
    ];
    $current = $_SERVER['REQUEST_URI'];
    foreach ($sections as $title => $items): ?>
    <div>
        <h3 class="px-4 mb-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest"><?= $title ?></h3>
        <div class="space-y-1">
            <?php foreach ($items as $item): 
                $isActive = (strpos($current, $item['url']) !== false);
            ?>
            <a href="<?= $item['url'] ?>" 
               class="nav-link flex items-center gap-4 px-4 py-3 rounded-2xl font-medium transition-all
               <?= $isActive 
                   ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' 
                   : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-emerald-500' ?>">
                <i class="<?= $item['icon'] ?> w-5 text-center"></i>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>