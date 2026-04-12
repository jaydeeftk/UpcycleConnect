<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: { 950: '#020617', 900: '#0f172a', 800: '#1e293b' }
                    },
                    borderRadius: { '3xl': '1.5rem', '4xl': '2rem' }
                }
            }
        };
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .dark .glass { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
        * { transition: background-color 0.4s ease, border-color 0.4s ease; }
    </style>
</head>
<body class="h-full bg-[#f8fafc] dark:bg-dark-950 text-slate-900 dark:text-slate-100 flex flex-col">
    <div class="fixed inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-[0.03] pointer-events-none"></div>
    
    <header class="sticky top-0 z-50 glass border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php include __DIR__ . '/../components/front/navbar.php'; ?>
        </div>
    </header>

    <main class="flex-1 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php echo $content; ?>
        </div>
    </main>

    <footer class="glass border-t border-slate-200 dark:border-slate-800 mt-auto">
        <?php include __DIR__ . '/../components/front/footer.php'; ?>
    </footer>

    <script>
        function themeToggle() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>