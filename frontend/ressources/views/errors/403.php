<?php
$isAdmin = false;
$layout = 'main';
ob_start();
?>
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-8xl font-bold text-red-500">403</h1>
        <p class="text-xl mt-4 font-semibold">Accès Non Autorisé</p>
        <p class="text-gray-500 mt-2">Vous n'avez pas les permissions pour accéder à l'administration.</p>
        <a href="/UpcycleConnect-PA2526/frontend/public/" class="mt-6 inline-block text-green-600 hover:underline">Retour à l'accueil</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';