<!DOCTYPE html>
<html lang="fr" data-theme="dark" class="dark">
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
                        surface: '#0f172a',
                        overlay: 'rgba(30, 41, 59, 0.5)'
                    }
                }
            }
        };

        const applyTheme = (t) => {
            const html = document.documentElement;
            html.setAttribute('data-theme', t);
            t === 'dark' ? html.classList.add('dark') : html.classList.remove('dark');
            localStorage.setItem('theme', t);
        };
        applyTheme(localStorage.getItem('theme') || 'dark');
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        :root { --ease-out: cubic-bezier(0.23, 1, 0.32, 1); }
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Glassmorphism Cards */
        .dark .admin-card {
            background: rgba(30, 41, 59, 0.4) !important;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 180ms var(--ease-out), border-color 180ms var(--ease-out);
        }
        .admin-card:hover {
            transform: translateY(-2px);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .dark body { background: #020617; }
    </style>
</head>
<body class="h-screen flex overflow-hidden">
    <aside id="sidebar" class="w-72 bg-[#0f172a] border-r border-slate-800 flex flex-col z-30">
        <div class="p-8 h-20 flex items-center gap-3">
             <i class="fas fa-recycle text-emerald-500 text-2xl"></i>
             <span class="font-bold text-white tracking-tight">Upcycle<span class="text-emerald-500">Connect</span></span>
        </div>
        <nav class="flex-1 overflow-y-auto py-6">
            <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 bg-slate-50 dark:bg-[#020617]">
        <header class="h-20 flex items-center justify-between px-10 border-b border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-[#0f172a]/50 backdrop-blur-md">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Dashboard</h2>
            <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="btn btn-ghost btn-circle btn-sm">
                <i class="fas fa-sun dark:hidden text-orange-400"></i>
                <i class="fas fa-moon hidden dark:inline text-blue-400"></i>
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-10 no-scrollbar">
            <?php echo $content; ?>
        </main>
    </div>
</body>
</html>