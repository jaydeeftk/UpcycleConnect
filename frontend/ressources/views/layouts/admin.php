<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Admin - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="flex h-full overflow-hidden">
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white dark:bg-slate-900 border-b dark:border-slate-800 flex items-center justify-between px-8">
                <div class="flex items-center gap-4">
                    <h1 class="text-emerald-500 font-bold tracking-tight">UpcycleConnect <span class="text-slate-400 font-medium">Admin</span></h1>
                    <button onclick="themeToggle()" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 hover:text-emerald-500 transition-all">
                        <i class="fas fa-adjust"></i>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium opacity-70"><?php echo $_SESSION['admin_user'] ?? 'Admin'; ?></span>
                    <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold">A</div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6"><?php echo $content; ?></main>
        </div>
    </div>
    <script>function themeToggle() { const isDark = document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', isDark ? 'dark' : 'light'); }</script>
</body>
</html>