<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { 
            darkMode: 'class',
            theme: { extend: { colors: { primary: '#10b981' } } }
        };
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        /* Ce bloc permet a tes cartes blanches de devenir sombres sans changer ton code HTML */
        .dark body { background-color: #020617; color: white; }
        .dark .bg-base-100, .dark .bg-white { background-color: #0f172a !important; color: #f1f5f9 !important; }
        .dark .text-base-content, .dark .text-slate-900 { color: #f1f5f9 !important; }
        .dark .border-base-300, .dark .border-slate-200 { border-color: #1e293b !important; }
        .dark .dropdown-content { background-color: #1e293b !important; }
        * { transition: background-color 0.2s ease; }
    </style>
</head>
<body class="min-h-screen">
    <?php include __DIR__ . '/../components/front/navbar.php'; ?>
    <?php echo $content; ?>
    <?php include __DIR__ . '/../components/front/footer.php'; ?>
</body>
</html>