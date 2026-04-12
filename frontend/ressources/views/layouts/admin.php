<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { darkMode: 'class' };
        function themeToggle() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="flex h-screen overflow-hidden">
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-900 border-b dark:border-slate-800 flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <button onclick="themeToggle()" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:text-emerald-500 transition-all">
                        <i class="fas fa-sun dark:hidden"></i><i class="fas fa-moon hidden dark:inline"></i>
                    </button>
                    <h1 class="text-lg font-semibold text-emerald-600">UpcycleConnect Admin</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm"><?php echo $_SESSION['admin_user'] ?? 'Administrateur'; ?></span>
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs">A</div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <?php echo $content; ?>
            </main>
        </div>
    </div>
</body>
</html>