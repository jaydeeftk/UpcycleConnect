<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | UpcycleConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#10b981' } } }
        };

        function applyTheme(theme) {
            const html = document.documentElement;
            html.setAttribute('data-theme', theme);
            if (theme === 'dark') { html.classList.add('dark'); }
            else { html.classList.remove('dark'); }
            localStorage.setItem('theme', theme);
        }

        applyTheme(localStorage.getItem('theme') || 'light');
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.3s ease; }

        /* FIX COULEURS DEGEU : On force le sombre sur les éléments DaisyUI */
        .dark body { background-color: #020617; color: white; }
        .dark .bg-white, .dark .bg-base-100, .dark .card { background-color: #0f172a !important; border-color: #1e293b !important; }
        .dark .table { background-color: #0f172a !important; color: #cbd5e1 !important; }
        .dark .table tr { border-bottom-color: #1e293b !important; }
        .dark .table th { background-color: #1e293b !important; color: white !important; }
        
        /* Design Sidebar */
        .sidebar-item { transition: all 0.2s; border-radius: 12px; margin: 4px 8px; }
        .sidebar-item:hover { background: rgba(16, 185, 129, 0.1); transform: translateX(5px); }
        
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-950">

    <aside class="w-72 bg-white dark:bg-[#0f172a] border-r border-slate-200 dark:border-slate-800 flex flex-col z-20">
        <div class="p-8">
            <a href="/" class="flex items-center gap-3">
                <i class="fas fa-recycle text-primary text-2xl"></i>
                <span class="text-xl font-bold">Admin<span class="text-primary">Panel</span></span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar px-4">
            <?php 
                // Chemin sécurisé vers le composant admin
                $sidebarPath = __DIR__ . '/../components/admin/sidebar.php';
                if (file_exists($sidebarPath)) {
                    include $sidebarPath;
                } else {
                    echo "<p class='p-4 text-xs text-red-500'>Sidebar introuvable : $sidebarPath</p>";
                }
            ?>
        </nav>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            <a href="/logout" class="flex items-center gap-3 p-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt"></i>
                <span class="text-sm font-bold">Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 relative">
        <header class="h-20 bg-white/80 dark:bg-[#0f172a]/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-10 z-10">
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">UpcycleConnect Administration</h2>
            
            <div class="flex items-center gap-6">
                <button onclick="window.themeToggle ? window.themeToggle() : (document.documentElement.classList.contains('dark') ? applyTheme('light') : applyTheme('dark'))" class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:text-primary transition-all shadow-sm">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline"></i>
                </button>
                <div class="flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-black"><?= $_SESSION['admin_user'] ?? 'Admin' ?></p>
                        <p class="text-[10px] text-primary font-bold tracking-tighter">SUPER-ADMIN</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-primary flex items-center justify-center text-white font-bold shadow-lg shadow-primary/20">A</div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10">
            <div class="animate-in fade-in duration-500">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

</body>
</html>