<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        tailwind.config = { 
            darkMode: 'class', 
            theme: { extend: { colors: { primary: '#10b981', darkBlue: '#0f172a', darkDeep: '#020617' } } } 
        };

        // Gestion du Thème (Contenu seulement)
        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        }

        // Gestion Sidebar Rétractable
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const main = document.getElementById('main-content');
            const isCollapsed = sb.classList.toggle('w-20');
            sb.classList.toggle('w-72');
            
            // Masquer/Afficher les textes
            document.querySelectorAll('.sb-text').forEach(el => el.classList.toggle('hidden'));
            document.querySelectorAll('.sb-section-title').forEach(el => el.classList.toggle('hidden'));
            
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('theme') || 'dark');
            if (localStorage.getItem('sidebar-collapsed') === 'true') toggleSidebar();
        });
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; overflow: hidden; }
        
        /* SIDEBAR TOUJOURS SOMBRE */
        #sidebar { 
            background-color: #0f172a !important; 
            color: #94a3b8; 
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #sidebar .nav-link:hover { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        #sidebar .nav-link.active { background: #10b981; color: white; shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4); }

        /* Scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        .dark body { background-color: #020617; }
        body:not(.dark) { background-color: #f8fafc; }
    </style>
</head>
<body class="h-screen flex text-slate-900 dark:text-slate-100 transition-colors duration-300">

    <aside id="sidebar" class="w-72 flex flex-col z-30 border-r border-slate-800">
        <div class="p-6 h-20 flex items-center gap-3 border-b border-slate-800/50 overflow-hidden">
            <div class="min-w-[40px] w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                <i class="fas fa-recycle text-xl"></i>
            </div>
            <span class="sb-text font-bold text-lg whitespace-nowrap text-white">Upcycle<span class="text-emerald-500">Connect</span></span>
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar py-6">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </nav>

        <div class="p-4 border-t border-slate-800/50">
            <a href="/logout" class="flex items-center gap-4 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span class="sb-text font-bold text-xs uppercase tracking-wider">Déconnexion</span>
            </a>
        </div>
    </aside>

    <div id="main-content" class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-[#020617]">
        
        <header class="h-20 flex items-center justify-between px-8 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-[#0f172a]/50 backdrop-blur-md">
            <div class="flex items-center gap-6">
                <button onclick="toggleSidebar()" class="p-2.5 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 transition-all">
                    <i class="fas fa-bars-staggered text-xl"></i>
                </button>
                <h2 class="font-bold text-sm tracking-tight sb-text">Espace Administration</h2>
            </div>

            <div class="flex items-center gap-4">
                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="btn btn-ghost btn-circle btn-sm">
                    <i class="fas fa-sun dark:hidden text-orange-400"></i>
                    <i class="fas fa-moon hidden dark:inline text-blue-400"></i>
                </button>
                
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-bold"><?= $_SESSION['admin_user'] ?? 'Admin Sys' ?></p>
                        <p class="text-[9px] text-emerald-500 font-black uppercase tracking-widest">Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-500/20">A</div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 no-scrollbar">
            <div class="max-w-7xl mx-auto">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

</body>
</html>