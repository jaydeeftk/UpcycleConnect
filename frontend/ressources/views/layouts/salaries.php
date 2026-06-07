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
        .dark .text-slate-700, .dark .text-gray-700 { color: #e2e8f0 !important; }
        .dark .text-slate-600, .dark .text-gray-600 { color: #cbd5e1 !important; }
        .dark .text-slate-500, .dark .text-gray-500 { color: #94a3b8 !important; }
        .dark .border-slate-200, .dark .border-gray-200 { border-color: #1e293b !important; }
        .dark .bg-green-50,.dark .bg-emerald-50,.dark .bg-teal-50,.dark .bg-lime-50,.dark .bg-green-100,.dark .bg-emerald-100,.dark .bg-teal-100 { background-color: rgba(16,185,129,.14) !important; }
        .dark .bg-blue-50,.dark .bg-sky-50,.dark .bg-cyan-50,.dark .bg-indigo-50,.dark .bg-blue-100,.dark .bg-sky-100,.dark .bg-indigo-100 { background-color: rgba(59,130,246,.14) !important; }
        .dark .bg-purple-50,.dark .bg-violet-50,.dark .bg-fuchsia-50,.dark .bg-purple-100,.dark .bg-violet-100 { background-color: rgba(168,85,247,.14) !important; }
        .dark .bg-amber-50,.dark .bg-yellow-50,.dark .bg-orange-50,.dark .bg-amber-100,.dark .bg-yellow-100,.dark .bg-orange-100 { background-color: rgba(245,158,11,.14) !important; }
        .dark .bg-red-50,.dark .bg-rose-50,.dark .bg-pink-50,.dark .bg-red-100,.dark .bg-rose-100,.dark .bg-pink-100 { background-color: rgba(244,63,94,.14) !important; }
        .dark .text-green-600,.dark .text-emerald-600,.dark .text-teal-600,.dark .text-green-700,.dark .text-emerald-700,.dark .text-teal-700,.dark .text-green-800,.dark .text-emerald-800 { color:#6ee7b7 !important; }
        .dark .text-blue-600,.dark .text-sky-600,.dark .text-indigo-600,.dark .text-blue-700,.dark .text-sky-700,.dark .text-indigo-700,.dark .text-blue-800,.dark .text-indigo-800 { color:#93c5fd !important; }
        .dark .text-purple-600,.dark .text-violet-600,.dark .text-purple-700,.dark .text-violet-700,.dark .text-purple-800 { color:#d8b4fe !important; }
        .dark .text-amber-600,.dark .text-yellow-600,.dark .text-orange-600,.dark .text-amber-700,.dark .text-yellow-700,.dark .text-orange-700,.dark .text-amber-800,.dark .text-orange-800 { color:#fcd34d !important; }
        .dark .text-red-600,.dark .text-rose-600,.dark .text-pink-600,.dark .text-red-700,.dark .text-rose-700,.dark .text-pink-700,.dark .text-red-800,.dark .text-rose-800 { color:#fca5a5 !important; }
        .dark .bg-gray-50,.dark .bg-slate-50 { background-color:#0f172a !important; }
        .dark .bg-gray-100,.dark .bg-slate-100 { background-color:#1e293b !important; }
        .dark .bg-gray-200,.dark .bg-slate-200 { background-color:#334155 !important; }
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
                    <a href="/salaries/forum"
                       class="nav-link flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'forum') ? 'active' : '' ?>">
                        <i class="fas fa-comments text-lg w-5 text-center"></i>
                        <span class="sb-text font-semibold text-sm">Forum</span>
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

    <script>
    function confirmer(m,c){var d=document.documentElement.classList.contains('dark');var s=d?'#1e293b':'#fff',t=d?'#f1f5f9':'#0f172a',b=d?'#334155':'#e2e8f0',u=d?'#94a3b8':'#64748b';var o=document.createElement('div');o.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:center;justify-content:center';o.innerHTML='<div style="background:'+s+';border:1px solid '+b+';border-radius:12px;padding:24px;max-width:360px;width:90%;text-align:center;font-family:inherit"><p style="color:'+t+';margin:0 0 20px;font-size:15px">'+m+'</p><button type="button" id="uc-c" style="margin-right:8px;padding:8px 20px;border:1px solid '+b+';border-radius:8px;background:transparent;color:'+u+';cursor:pointer">Annuler</button><button type="button" id="uc-o" style="padding:8px 20px;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer">Confirmer</button></div>';document.body.appendChild(o);o.querySelector('#uc-c').onclick=function(){o.remove()};o.querySelector('#uc-o').onclick=function(){o.remove();c()};o.addEventListener('click',function(e){if(e.target===o)o.remove()})}
    function ucConfirm(el,m){confirmer(m,function(){if(el.tagName==='A'){window.location.href=el.href}else{var f=el.closest?el.closest('form'):null;if(f)f.submit()}});return false}
    </script>
</body>
</html>