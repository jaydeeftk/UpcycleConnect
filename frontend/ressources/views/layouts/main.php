<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = { darkMode: 'class' };
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
    <style> * { transition: background-color 0.3s ease; } </style>
</head>
<body class="bg-slate-50 dark:bg-[#020617] text-slate-900 dark:text-white min-h-screen flex flex-col">
    <?php include __DIR__ . '/../components/front/navbar.php'; ?>
    <main class="flex-1"><?php echo $content; ?></main>
    <?php include __DIR__ . '/../components/front/footer.php'; ?>
</body>
</html>