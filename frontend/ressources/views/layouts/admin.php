<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: '#10b981' } } } };
        function applyTheme(t) {
            document.documentElement.setAttribute('data-theme', t);
            t === 'dark' ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', t);
        }
        applyTheme(localStorage.getItem('theme') || 'light');
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dark body { background-color: #020617; color: white; }
        .dark .bg-white, .dark .bg-base-100 { background-color: #0f172a !important; border-color: #1e293b !important; }
        .dark .table, .dark .table tr { background-color: #0f172a !important; color: #f1f5f9 !important; border-color: #1e293b !important; }
        .dark .table th { background-color: #1e293b !important; color: white !important; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-950">
    <aside class="w-72 bg-white dark:bg-[#0f172a] border-r border-slate-200 dark:border-slate-800 flex flex-col z-20">
        <div class="p-8">
            <a href="/" class="flex items-center gap-3 font-bold text-xl">
                <i class="fas fa-recycle text-primary"></i> Upcycle<span class="text-primary">Connect</span>
            </a>
        </div>
        <nav class="flex-1 overflow-y-auto px-4">
            <?php 
                // Test de plusieurs chemins possibles
                $paths = [
                    '/var/www/html/ressources/views/components/admin/sidebar.php',
                    __DIR__ . '/../components/Admin/sidebar.php',
                    __DIR__ . '/../../components/admin/sidebar.php'
                ];
                $found = false;
                foreach ($paths as $p) {
                    if (file_exists($p)) {
                        include $p;
                        $found = true;
                        break;
                    }
                }
                if (!$found) echo "<p class='p-4 text-xs text-red-500 bg-red-50 rounded-xl'>Sidebar introuvable</p>";
            ?>
        </nav>
        <div class="p-6 border-t border-slate-100 dark:border-slate-800">
            <a href="/logout" class="flex items-center gap-3 p-3 text-red-500 hover:bg-red-50 rounded-xl font-bold text-sm">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-20 bg-white/80 dark:bg-[#0f172a]/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-10">
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">Administration</h2>
            <div class="flex items-center gap-6">
                <button onclick="applyTheme(document.documentElement.classList.contains('dark') ? 'light' : 'dark')" class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:text-primary transition-all">
                    <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline"></i>
                </button>
                <div class="flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-800">
                    <div class="text-right"><p class="text-sm font-black"><?= $_SESSION['admin_user'] ?? 'Admin' ?></p><p class="text-[10px] text-primary font-bold uppercase">Super-Admin</p></div>
                    <div class="w-12 h-12 rounded-2xl bg-primary flex items-center justify-center text-white font-bold shadow-lg shadow-primary/20">A</div>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-10"><?php echo $content; ?></main>
    </div>
</body>
</html>