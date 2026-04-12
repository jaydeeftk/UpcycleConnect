<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | UpcycleConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);

        window.themeToggle = () => {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        };
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dark body { background-color: #0f172a; color: white; }
        .dark .bg-white { background-color: #1e293b !important; }
        
        /* Sidebar Scrollbar */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-950">

    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col hide-mobile">
        <div class="p-6">
             <a href="/" class="flex items-center gap-2 font-bold text-xl">
                <i class="fas fa-recycle text-emerald-500"></i>
                <span>Upcycle<span class="text-emerald-500">Connect</span></span>
            </a>
        </div>
        <div class="flex-1 overflow-y-auto sidebar-scroll">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 z-10">
            <div class="flex items-center gap-4">
                <h2 class="font-bold text-slate-400 text-xs uppercase tracking-widest">Dashboard Admin</h2>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="themeToggle()" class="btn btn-ghost btn-circle btn-sm">
                    <i class="fas fa-sun dark:hidden text-orange-400"></i>
                    <i class="fas fa-moon hidden dark:inline text-blue-400"></i>
                </button>
                
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-semibold"><?php echo $_SESSION['admin_user'] ?? 'Administrateur'; ?></span>
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-emerald-500/20">A</div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <?php echo $content; ?>
        </main>
    </div>

</body>
</html>