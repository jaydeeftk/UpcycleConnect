<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | UpcycleConnect</title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#10b981',
                        dark: { 950: '#020617', 900: '#0f172a', 800: '#1e293b' }
                    }
                }
            }
        };

        function applyTheme(theme) {
            const html = document.documentElement;
            html.setAttribute('data-theme', theme);
            if (theme === 'dark') { html.classList.add('dark'); }
            else { html.classList.remove('dark'); }
            localStorage.setItem('theme', theme);
        }

        applyTheme(localStorage.getItem('theme') || 'light');

        window.themeToggle = () => {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        };
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Effet Glassmorphism Header */
        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .dark .glass-header {
            background: rgba(15, 23, 42, 0.7);
        }

        /* Sidebar Design & Animations */
        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .nav-link:hover {
            background: rgba(16, 185, 129, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background: #10b981;
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            height: 70%;
            width: 4px;
            background: #10b981;
            border-radius: 0 4px 4px 0;
        }

        /* Scrollbar moderne */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }

        .sidebar-scroll { height: calc(100vh - 120px); overflow-y: auto; }
        
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-[#f8fafc] dark:bg-dark-950 text-slate-900 dark:text-slate-100">

    <aside class="w-72 bg-white dark:bg-dark-900 border-r border-slate-200 dark:border-slate-800 flex flex-col z-20 transition-all duration-300">
        <div class="p-8">
            <a href="/" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <i class="fas fa-recycle text-white text-xl"></i>
                </div>
                <span class="text-xl font-bold tracking-tight">Upcycle<span class="text-primary">Connect</span></span>
            </a>
        </div>

        <nav class="flex-1 px-6 space-y-2 sidebar-scroll no-scrollbar">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </nav>

        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            <a href="/logout" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 relative">
        
        <header class="h-20 glass-header border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-10 z-10">
            <div>
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-widest">Espace Administration</h2>
            </div>
            
            <div class="flex items-center gap-6">
                <button onclick="themeToggle()" class="p-2.5 rounded-xl bg-slate-100 dark:bg-dark-800 hover:text-primary transition-all">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:inline"></i>
                </button>

                <div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm font-bold"><?php echo $_SESSION['admin_user'] ?? 'Admin'; ?></p>
                        <p class="text-[10px] text-primary font-bold uppercase tracking-tighter">Super Utilisateur</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 border-2 border-primary/20 flex items-center justify-center text-primary font-black shadow-inner">
                        A
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-10 fade-in">
            <?php echo $content; ?>
        </main>
    </div>

</body>
</html>