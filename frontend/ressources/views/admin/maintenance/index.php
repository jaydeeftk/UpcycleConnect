<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-xl font-bold mb-6">Maintenance</h3>
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500">Version PHP</div>
            <div class="text-xl font-bold"><?= phpversion() ?></div>
        </div>
        <div class="border rounded-lg p-4">
            <div class="text-sm text-gray-500">Environnement</div>
            <div class="text-xl font-bold">Docker</div>
        </div>
    </div>
    <button onclick="window.location.href='/admin/maintenance/cache'" class="mb-3 flex items-center gap-2 text-sm text-gray-600 hover:text-black">
        <i class="fas fa-broom"></i> Vider le cache
    </button>
    <form method="POST" action="/admin/maintenance/toggle">
        <?php $active = file_exists(__DIR__ . '/../../../../../.maintenance'); ?>
        <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-lg font-medium text-white <?= $active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600' ?>">
            <?php if ($active): ?>
                <i class="fas fa-times-circle"></i> Désactiver le mode maintenance (ACTIF)
            <?php else: ?>
                <i class="fas fa-wrench"></i> Activer le mode maintenance
            <?php endif; ?>
        </button>
    </form>
</div>
<div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 text-center px-4">
    <div class="mb-8 text-gray-400">
        <i class="fa-solid fa-gear text-9xl animate-spin-slow"></i>
    </div>

    <h1 class="text-4xl font-bold text-gray-800">Site en maintenance</h1>
    <p class="text-gray-600 mt-4 max-w-md">
        Nous effectuons actuellement des mises à jour. <br>
        L'accès sera rétabli d'ici quelques instants.
    </p>

    <div class="mt-12">
        <a href="/admin-portal-access" 
           class="text-[10px] text-gray-300 hover:text-gray-500 transition-colors uppercase tracking-widest">
            Accès restreint
        </a>
    </div>
</div>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    display: inline-block;
    animation: spin-slow 8s linear infinite;
}
</style>
