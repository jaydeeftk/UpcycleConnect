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
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
        function themeToggle() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }
    </script>
</head>
<body class="bg-white dark:bg-[#020617] text-slate-900 dark:text-white min-h-screen">
    <?php include __DIR__ . '/../components/front/navbar.php'; ?>
    <main>
        <?php echo $content; ?>
    </main>
    <?php include __DIR__ . '/../components/front/footer.php'; ?>
</body>
</html>