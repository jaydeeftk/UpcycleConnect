<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salarié | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#10b981', darkBlue: '#0f172a' } } }
        };

        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        }

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const isCollapsed = sb.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed ? 'true' : 'false');
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        #sidebar { background-color: #0f172a !important; width: 18rem; transition: width 220ms ease-in-out; overflow: hidden; }
        #sidebar.collapsed { width: 5rem; }
        #sidebar.collapsed .sb-text { display: none !important; }
        #sidebar.collapsed .sb-section { display: none !important; }
        .nav-link { transition: background 150ms ease-out; }
        .nav-link.active { background: #10b981; color: white !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .dark body { background-color: #020617; }
        body:not(.dark) { background-color: #f8fafc; }

        .dark .bg-white { background-color: #0f172a !important; }
        .dark .text-slate-800, .dark .text-slate-900, .dark .text-gray-900, .dark .text-gray-800 { color: #f1f5f9 !important; }
        .dark .border-slate-200, .dark .border-gray-200 { border-color: #1e293b !important; }
        .dark input:not([class*="bg-"]), .dark select:not([class*="bg-"]), .dark textarea:not([class*="bg-"]) {
            background-color: #1e293b; color: #f1f5f9; border-color: #334155;
        }
        .dark table thead tr { color: #94a3b8; border-color: #1e293b; }
        .dark table tbody tr { border-color: #1e293b; }
        .dark table tbody tr:hover { background-color: rgba(30,41,59,0.5) !important; }
        .dark .badge { border-color: #334155; }
        .dark .modal-box, .dark .dropdown-content { background-color: #0f172a !important; border: 1px solid #1e293b; }
        .dark select option { background-color: #1e293b; }
    </style>
    <script>
        (function() {
            var t = localStorage.getItem('theme') || 'dark';
            if (t === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            }
            if (localStorage.getItem('sidebar-collapsed') === 'true') {
                document.addEventListener('DOMContentLoaded', function() {
                    var sb = document.getElementById('sidebar');
                    if (sb) sb.classList.add('collapsed');
                });
            }
        })();
    </script>
</head>
<body class="h-screen flex text-slate-900 dark:text-slate-100 transition-colors duration-300">

    <aside id="sidebar" class="flex flex-col z-30 border-r border-slate-800 flex-shrink-0">
        <div class="p-6 h-20 flex items-center gap-3 border-b border-slate-800/50 overflow-hidden">
            <div class="min-w-[40px] w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white">
                <i class="fas fa-recycle text-xl"></i>
            </div>
            <span class="sb-text font-bold text-lg text-white whitespace-nowrap">UpcycleConnect</span>
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar py-6 px-3">
            <p class="sb-section px-3 mb-2 text-xs font-black uppercase tracking-widest text-slate-500">Menu</p>
            <ul class="space-y-1">
                <li>
                    <a href="/salaries/dashboard"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard') ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/conseils"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'conseils') ? 'active' : '' ?>">
                        <i class="fas fa-lightbulb text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Conseils</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/formations"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'formations') ? 'active' : '' ?>">
                        <i class="fas fa-graduation-cap text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Formations</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/evenements"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'evenements') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Événements</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/ateliers"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'ateliers') ? 'active' : '' ?>">
                        <i class="fas fa-tools text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Ateliers</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/planning"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'planning') ? 'active' : '' ?>">
                        <i class="fas fa-calendar-week text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Planning</span>
                    </a>
                </li>
                <li>
                    <a href="/salaries/forum"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'forum') ? 'active' : '' ?>">
                        <i class="fas fa-comments text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Modération forum</span>
                    </a>
                </li>
            </ul>

            <div class="border-t border-slate-800/50 mt-6 pt-6">
                <ul class="space-y-1">
                    <li>
                        <a href="/" target="_blank"
                           class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white">
                            <i class="fas fa-external-link-alt text-lg w-5 text-center"></i>
                            <span class="sb-text font-semibold text-sm">Voir le site</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-4 border-t border-slate-800/50">
            <a href="/logout" class="flex items-center gap-4 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span class="sb-text font-bold text-xs uppercase tracking-wider">Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-[#020617]">
        <header class="h-20 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-[#0f172a]/50 backdrop-blur-md z-20">
            <div class="flex items-center gap-6">
                <button onclick="toggleSidebar()" class="p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all active:scale-95">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <h2 class="font-bold text-sm text-slate-400 uppercase tracking-widest sb-text">Espace Salarié</h2>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all">
                    <i class="fas fa-sun dark:hidden text-orange-400 text-lg"></i>
                    <i class="fas fa-moon hidden dark:inline text-blue-400 text-lg"></i>
                </button>

                <div class="flex items-center gap-3 pl-3 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                        <p class="text-[9px] text-emerald-500 font-black uppercase"><?= htmlspecialchars($_SESSION['user']['poste'] ?? 'Salarié') ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-500/20">
                        <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'S', 0, 1)) ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10 no-scrollbar">
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <?php echo $content; ?>
        </main>
    </div>

</body>
</html>