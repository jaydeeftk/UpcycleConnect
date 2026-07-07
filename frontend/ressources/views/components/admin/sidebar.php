<?php
$sections = [
    'PILOTAGE' => [
        ['url' => '/admin/dashboard',  'icon' => 'fas fa-th-large',           'label' => t('adm_nav_dashboard', 'Dashboard')],
        ['url' => '/admin/finances',   'icon' => 'fas fa-chart-line',          'label' => t('adm_nav_finances', 'Finances & Stats')],
        ['url' => '/admin/planning',   'icon' => 'fas fa-calendar-check',      'label' => t('adm_nav_planning', 'Planning Global')],
    ],
    'VALIDATIONS' => [
        ['url' => '/admin/demandes',   'icon' => 'fas fa-inbox',               'label' => t('adm_nav_demandes', 'Demandes Dépôt')],
        ['url' => '/admin/annonces',   'icon' => 'fas fa-bullhorn',            'label' => t('adm_nav_annonces', 'Annonces')],
        ['url' => '/admin/conteneurs', 'icon' => 'fas fa-box-open',            'label' => t('adm_nav_conteneurs', 'Conteneurs')],
        ['url' => '/admin/formations', 'icon' => 'fas fa-graduation-cap',      'label' => t('adm_nav_formations', 'Formations')],
        ['url' => '/admin/conseils',   'icon' => 'fas fa-lightbulb',           'label' => t('adm_nav_conseils', 'Conseils')],
    ],
    'CATALOGUE' => [
        ['url' => '/admin/services',   'icon' => 'fas fa-concierge-bell',      'label' => t('adm_nav_prestations', 'Prestations')],
        ['url' => '/admin/categories', 'icon' => 'fas fa-tags',                'label' => t('adm_nav_categories', 'Catégories')],
        ['url' => '/admin/evenements', 'icon' => 'fas fa-calendar-alt',        'label' => t('adm_nav_evenements', 'Événements')],
    ],
    'GESTION' => [
        ['url' => '/admin/utilisateurs','icon' => 'fas fa-users',              'label' => t('adm_nav_utilisateurs', 'Utilisateurs')],
        ['url' => '/admin/contrats',   'icon' => 'fas fa-file-contract',       'label' => t('adm_nav_contrats', 'Contrats Pro')],
        ['url' => '/admin/abonnements','icon' => 'fas fa-id-card',             'label' => t('adm_nav_abonnements', 'Abonnements Pro')],
        ['url' => '/admin/factures',   'icon' => 'fas fa-file-invoice-dollar', 'label' => t('adm_nav_factures', 'Factures')],
    ],
    'COMMUNICATION' => [
        ['url' => '/admin/tickets',       'icon' => 'fas fa-headset',            'label' => t('adm_nav_tickets', 'Tickets support')],
        ['url' => '/admin/notifications', 'icon' => 'fas fa-bell',               'label' => t('adm_nav_notifications', 'Notifications')],
        ['url' => '/admin/forum',         'icon' => 'fas fa-comments',           'label' => t('adm_nav_forum', 'Forum')],
    ],
    'SYSTÈME' => [
        ['url' => '/admin/parametres', 'icon' => 'fas fa-cog',                 'label' => t('adm_nav_parametres', 'Paramètres')],
    ],
];

$sectionLabels = [
    'PILOTAGE'      => t('adm_sec_pilotage', 'PILOTAGE'),
    'VALIDATIONS'   => t('adm_sec_validations', 'VALIDATIONS'),
    'CATALOGUE'     => t('adm_sec_catalogue', 'CATALOGUE'),
    'GESTION'       => t('adm_sec_gestion', 'GESTION'),
    'COMMUNICATION' => t('adm_sec_communication', 'COMMUNICATION'),
    'SYSTÈME'       => t('adm_sec_systeme', 'SYSTÈME'),
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
        <span class="text-[9px] font-black uppercase tracking-[0.2em] whitespace-nowrap"><?= $sectionLabels[$title] ?? $title ?></span>
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
    var KEY = 'sb-sections';
    function getSaved() { try { return JSON.parse(localStorage.getItem(KEY)||'{}'); } catch(e) { return {}; } }
    function setSaved(s) { localStorage.setItem(KEY, JSON.stringify(s)); }

    window.toggleSection = function(key) {
        var el = document.getElementById(key);
        var icon = document.getElementById('icon-'+key);
        if (!el) return;
        var isOpen = parseInt(el.style.maxHeight) > 0;
        var s = getSaved();
        s[key] = !isOpen;
        setSaved(s);
        el.style.maxHeight = !isOpen ? el.scrollHeight+'px' : '0px';
        if (icon) icon.style.transform = !isOpen ? 'rotate(0deg)' : 'rotate(-90deg)';
    };

    function applyAll() {
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
            var def = <?= $hasActive ? 'true' : 'false' ?>;
            var open = (key in state) ? state[key] : def;
            var el = document.getElementById(key);
            var icon = document.getElementById('icon-'+key);
            if (!el) return;
            el.style.transition = 'none';
            el.style.maxHeight = open ? el.scrollHeight+'px' : '0px';
            if (icon) icon.style.transform = open ? 'rotate(0deg)' : 'rotate(-90deg)';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() { el.style.transition = ''; });
            });
        })();
        <?php endforeach; ?>
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyAll);
    } else {
        applyAll();
    }
})();
</script>
