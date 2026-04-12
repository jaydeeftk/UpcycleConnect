<button onclick="themeToggle()" class="btn btn-ghost btn-circle">
    <i class="fas fa-sun dark:hidden text-orange-400"></i>
    <i class="fas fa-moon hidden dark:inline text-blue-400"></i>
</button>

<script>
function applyTheme(theme) {
    const html = document.documentElement;
    html.setAttribute('data-theme', theme);
    if (theme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
}

const saved = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
applyTheme(saved);

window.themeToggle = () => {
    const isDark = document.documentElement.classList.contains('dark');
    applyTheme(isDark ? 'light' : 'dark');
};
</script>