<?php
$sections = [
    'PILOTAGE' => [
        ['url' => '/admin/dashboard',  'icon' => 'fas fa-th-large',           'label' => 'Dashboard'],
        ['url' => '/admin/finances',   'icon' => 'fas fa-chart-line',          'label' => 'Finances & Stats'],
        ['url' => '/admin/planning',   'icon' => 'fas fa-calendar-check',      'label' => 'Planning Global'],
    ],
    'VALIDATIONS' => [
        ['url' => '/admin/demandes',   'icon' => 'fas fa-inbox',               'label' => 'Demandes Dépôt'],
        ['url' => '/admin/annonces',   'icon' => 'fas fa-bullhorn',            'label' => 'Annonces'],
        ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box-open',            'label' => 'Conteneurs'],
        ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap',      'label' => 'Formations'],
        ['url' => '/admin/conseils',   'icon' => 'fas fa-lightbulb',           'label' => 'Conseils'],
    ],
    'CATALOGUE' => [
        ['url' => '/admin/services',   'icon' => 'fas fa-concierge-bell',      'label' => 'Prestations'],
        ['url' => '/admin/categories', 'icon' => 'fas fa-tags',                'label' => 'Catégories'],
        ['url' => '/admin/evenements', 'icon' => 'fas fa-calendar-alt',        'label' => 'Événements'],
    ],
    'GESTION' => [
        ['url' => '/admin/utilisateurs','icon' => 'fas fa-users',              'label' => 'Utilisateurs'],
        ['url' => '/admin/contrats',   'icon' => 'fas fa-file-contract',       'label' => 'Contrats Pro'],
        ['url' => '/admin/factures',   'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Factures'],
    ],
    'COMMUNICATION' => [
        ['url' => '/admin/messages',      'icon' => 'fas fa-envelope-open-text', 'label' => 'Messages'],
        ['url' => '/admin/notifications', 'icon' => 'fas fa-bell',               'label' => 'Notifications'],
        ['url' => '/admin/forum',         'icon' => 'fas fa-comments',           'label' => 'Forum'],
    ],
    'SYSTÈME' => [
        ['url' => '/admin/parametres', 'icon' => 'fas fa-cog',                 'label' => 'Paramètres'],
    ],
];

$current = $_SERVER['REQUEST_URI'];
foreach ($sections as $title => $items):
    $sectionKey = 'sb-section-' . strtolower(str_replace(' ', '-', $title));
    $hasActive = false;
    foreach ($items as $item) {
        if (strpos($current, $item['url']) !== false) { $hasActive = true; break; }
    }
?>
<div class="mb-1">
    <button
        onclick="toggleSection('<?= $sectionKey ?>')"
        class="sb-section w-full flex items-center justify-between px-6 py-2 group <?= $hasActive ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-300' ?> transition-colors duration-150">
        <span class="text-[9px] font-black uppercase tracking-[0.2em] whitespace-nowrap"><?= $title ?></span>
        <i id="icon-<?= $sectionKey ?>" class="fas fa-chevron-down text-[8px] opacity-60 transition-transform duration-300 sb-text"></i>
    </button>
    <div id="<?= $sectionKey ?>" class="overflow-hidden transition-all duration-300 ease-out">
        <div class="px-3 pb-2 space-y-0.5">
            <?php foreach ($items as $item):
                $isActive = (strpos($current, $item['url']) !== false);
            ?>
            <a href="<?= $item['url'] ?>"
               class="nav-link group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150
               <?= $isActive ? 'active' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' ?>">
                <i class="<?= $item['icon'] ?> text-sm min-w-[18px] text-center flex-shrink-0"></i>
                <span class="sb-text text-sm font-medium whitespace-nowrap"><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
(function() {
    var STORAGE_KEY = 'sb-sections';

    function getSaved() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch(e) { return {}; }
    }

    function setSaved(state) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    }

    function applySection(key, open, animate) {
        var el = document.getElementById(key);
        var icon = document.getElementById('icon-' + key);
        if (!el) return;
        if (open) {
            el.style.maxHeight = el.scrollHeight + 'px';
            if (icon) icon.style.transform = 'rotate(0deg)';
        } else {
            el.style.maxHeight = '0px';
            if (icon) icon.style.transform = 'rotate(-90deg)';
        }
    }

    window.toggleSection = function(key) {
        var state = getSaved();
        var el = document.getElementById(key);
        if (!el) return;
        var isOpen = el.style.maxHeight && el.style.maxHeight !== '0px';
        state[key] = !isOpen;
        setSaved(state);
        applySection(key, !isOpen, true);
    };

    document.addEventListener('DOMContentLoaded', function() {
        var state = getSaved();
        <?php foreach ($sections as $title => $items):
            $sectionKey = 'sb-section-' . strtolower(str_replace(' ', '-', $title));
            $hasActive = false;
            foreach ($items as $item) {
                if (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) { $hasActive = true; break; }
            }
        ?>
        (function() {
            var key = '<?= $sectionKey ?>';
            var defaultOpen = <?= $hasActive ? 'true' : 'false' ?>;
            var open = (key in state) ? state[key] : defaultOpen;
            var el = document.getElementById(key);
            if (el) {
                el.style.maxHeight = open ? el.scrollHeight + 'px' : '0px';
                var icon = document.getElementById('icon-' + key);
                if (icon) icon.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        })();
        <?php endforeach; ?>
    });
})();
</script>
