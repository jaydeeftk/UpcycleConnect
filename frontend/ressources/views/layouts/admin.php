<!DOCTYPE html>
<html lang="fr" data-theme="dark" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '#10b981' } } } };
        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        }
        applyTheme(localStorage.getItem('theme') || 'dark');
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .sidebar-scroll { height: calc(100vh - 120px); }
        .dark body { background-color: #020617; }
        .dark .bg-sidebar { background-color: #0f172a; }
        .card-stats { transition: transform 0.3s ease; }
        .card-stats:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="h-screen flex overflow-hidden text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <aside class="w-64 bg-sidebar border-r border-slate-800 flex flex-col z-30">
        <div class="p-6 border-b border-slate-800/50">
            <a href="/" class="flex items-center gap-2 font-bold text-lg">
                <i class="fas fa-recycle text-emerald-500"></i>
                <span>Upcycle<span class="text-emerald-500">Connect</span></span>
            </a>
        </div>
        <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        <div class="mt-auto p-6 border-t border-slate-800/50">
            <a href="/logout" class="flex items-center gap-2 text-red-500 text-sm font-bold hover:translate-x-1 transition-transform">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-[#020617]">
        <header class="h-16 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-[#0f172a]/50 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <button class="lg:hidden text-slate-500"><i class="fas fa-bars"></i></button>
                <h2 class="font-bold text-sm">Dashboard</h2>
            </div>
            <div class="flex items-center gap-4">
                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="btn btn-ghost btn-sm btn-circle">
                    <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline"></i>
                </button>
                <div class="flex items-center gap-3 pl-4 border-l border-slate-800">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold"><?= $_SESSION['admin_user'] ?? 'Admin Sys' ?></p>
                        <p class="text-[10px] text-emerald-500 font-bold">Administrateur</p>
                    </div>
                    <div class="avatar placeholder">
                        <div class="bg-emerald-500 text-white rounded-xl w-10 h-10 shadow-lg shadow-emerald-500/20">
                            <span class="text-xs font-bold">A</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-8 no-scrollbar">
            <?php echo $content; ?>
        </main>
    </div>
</body>
</html>