<!DOCTYPE html>
<html class="transition-colors duration-500" lang="fr" data-theme="light">
<head>
<script>
const themeToggle = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
};
if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'UpcycleConnect - Donnez une seconde vie à vos objets' ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

<style>
    * { transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; }
    .dark { color-scheme: dark; }
</style>
</head>
<body class="bg-base-200 text-base-content min-h-screen">

    <?php include __DIR__ . '/../components/front/navbar.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/../components/front/footer.php'; ?>
    <?php include __DIR__ . '/../components/front/tutoriel.php'; ?>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
    (function() {
        try {
            fetch('/api/visites', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({page: window.location.pathname}),
                keepalive: true
            });
        } catch(e) {}
    })();
    </script>
</body>
</html>