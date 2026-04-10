<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin-slow { animation: spin 4s linear infinite; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center">
    <div class="text-center max-w-md px-6">
        <i class="fas fa-cog text-gray-300 text-8xl mb-6 spin-slow"></i>
        <h1 class="text-3xl font-bold text-gray-800">Site en maintenance</h1>
        <p class="text-gray-500 mt-3">Nous préparons du nouveau pour <strong>UpcycleConnect</strong>.<br>Le site sera de nouveau accessible très bientôt.</p>
        <a href="/UpcycleConnect-PA2526/frontend/public/maintenance-login"
           class="mt-10 inline-block text-xs text-gray-300 hover:text-gray-400 transition">
            ACCÈS RESTREINT
        </a>
    </div>
</body>
</html>