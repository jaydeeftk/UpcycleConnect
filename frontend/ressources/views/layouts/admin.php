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
            theme: {
                extend: {
                    colors: { primary: '#10b981' }
                }
            }
        };

        function applyTheme(theme) {
            const html = document.documentElement;
            html.setAttribute('data-theme', theme);
            if (theme === 'dark') { html.classList.add('dark'); }
            else { html.classList.remove('dark'); }
        }

        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .admin-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .admin-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }
        
        /* Personnalisation Dark Mode pour l'Admin */
        .dark .bg-white { background-color: #1e293b !important; color: white !important; }
        .dark .text-slate-900 { color: #f1f5f9 !important; }
        .dark body { background-color: #0f172a; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 transition-colors duration-300">
    <div class="flex h-full">
        <main class="flex-1 overflow-y-auto">
            <?php echo $content; ?>
        </main>
    </div>
</body>
</html>