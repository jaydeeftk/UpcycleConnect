<!DOCTYPE html>
<html lang="fr" data-theme="dark" class="dark">
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
        const applyTheme = (t) => {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        };
        applyTheme(localStorage.getItem('theme') || 'dark');
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* Sidebar Colors Fix */
        .dark aside { background-color: #0f172a !important; }
        aside { background-color: #ffffff !important; }
        
        .nav-link { transition: transform 200ms ease-out, background 200ms ease-out; }
        .nav-link:active { transform: scale(0.96); }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-[#020617] text-slate-900 dark:text-slate-100">
    
    <aside class="w-72 flex flex-col border-r border-slate-200 dark:border-slate-800 z-30 shrink-0">
        <div class="p-8 h-20 flex items-center gap-3">
             <i class="fas fa-recycle text-emerald-500 text-2xl"></i>
             <span class="font-bold text-slate-900 dark:text-white tracking-tight">Upcycle<span class="text-emerald-500">Connect</span></span>
        </div>
        
        <nav class="flex-1 overflow-y-auto no-scrollbar">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </nav>

        <div class="p-6 border-t border-slate-200 dark:border-slate-800">
            <a href="/logout" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl font-bold transition-all active:scale-95">
                <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="h-20 flex items-center justify-between px-10 border-b border-slate-200 dark:border-slate-800 bg-white/70 dark:bg-[#0f172a]/70 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Administration</h2>
            </div>

            <div class="flex items-center gap-6">
                <div class="relative cursor-pointer hover:text-emerald-500 transition-colors">
                    <i class="far fa-bell text-xl"></i>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] flex items-center justify-center rounded-full border-2 border-white dark:border-slate-900">3</span>
                </div>

                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="btn btn-ghost btn-circle btn-sm">
                    <i class="fas fa-sun dark:hidden text-orange-400"></i>
                    <i class="fas fa-moon hidden dark:inline text-blue-400"></i>
                </button>

                <div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold"><?= $_SESSION['admin_user'] ?? 'Admin' ?></p>
                        <p class="text-[10px] text-emerald-500 font-black uppercase">Administrateur</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-bold shadow-lg shadow-emerald-500/20">A</div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10 no-scrollbar">
            <?php echo $content; ?>
        </main>
    </div>
</body>
</html>