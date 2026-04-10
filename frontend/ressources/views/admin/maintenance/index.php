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
    <button onclick="window.location.href='/UpcycleConnect-PA2526/frontend/public/admin/maintenance/cache'" class="mb-3 flex items-center gap-2 text-sm text-gray-600 hover:text-black">
        <i class="fas fa-broom"></i> Vider le cache
    </button>
    <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/maintenance/toggle">
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