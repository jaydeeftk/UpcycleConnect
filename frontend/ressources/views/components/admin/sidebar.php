<div class="flex flex-col gap-2 p-4">
    <?php
    $nav = [
        ['url' => '/admin/dashboard', 'icon' => 'fas fa-chart-line', 'label' => 'Dashboard'],
        ['url' => '/admin/utilisateurs', 'icon' => 'fas fa-users', 'label' => 'Utilisateurs'],
        ['url' => '/admin/annonces', 'icon' => 'fas fa-bullhorn', 'label' => 'Annonces'],
        ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box', 'label' => 'Conteneurs'],
        ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap', 'label' => 'Formations'],
        ['url' => '/admin/evenements', 'icon' => 'fas fa-calendar-alt', 'label' => 'Événements'],
        ['url' => '/admin/messages', 'icon' => 'fas fa-envelope', 'label' => 'Messages'],
        ['url' => '/admin/parametres', 'icon' => 'fas fa-cog', 'label' => 'Paramètres'],
    ];

    $current = $_SERVER['REQUEST_URI'];
    foreach ($nav as $item): 
        $isActive = ($current === $item['url']);
    ?>
        <a href="<?= $item['url'] ?>" 
           class="group flex items-center gap-4 px-4 py-3 rounded-2xl transition-all duration-300 
           <?= $isActive ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'hover:bg-emerald-500/10 hover:text-emerald-500 text-slate-500 dark:text-slate-400' ?>">
            
            <div class="flex items-center justify-center w-5 transition-transform duration-300 group-hover:scale-110">
                <i class="<?= $item['icon'] ?> text-lg"></i>
            </div>
            
            <span class="font-semibold text-sm tracking-wide"><?= $item['label'] ?></span>
            
            <?php if ($isActive): ?>
                <div class="ml-auto w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>