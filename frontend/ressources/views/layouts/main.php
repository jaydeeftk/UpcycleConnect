<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
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
</head>
<body class="bg-base-200 text-base-content min-h-screen">

    <?php include __DIR__ . '/../components/front/navbar.php'; ?>

    <main>
        <?= $content ?? '' ?>
    </main>

    <?php include __DIR__ . '/../components/front/footer.php'; ?>
    <?php include __DIR__ . '/../components/front/tutoriel.php'; ?>

</body>
</html>