<button type="button" id="theme-toggle-btn"
        class="text-base-content/70 hover:text-primary transition-colors" title="<?= t('nav_theme_toggle', 'Basculer le thème') ?>">
    <i class="fas fa-moon"></i>
</button>
<script>
(function () {
    var btn = document.getElementById('theme-toggle-btn');
    if (!btn) return;
    var icon = btn.querySelector('i');
    function syncIcon() {
        var isDark = document.documentElement.classList.contains('dark');
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
    syncIcon();
    btn.addEventListener('click', function () {
        if (window.themeToggle) window.themeToggle();
        syncIcon();
    });
})();
</script>