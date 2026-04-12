<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Mon planning</h2>
        <p class="text-gray-600">Vos formations et événements à venir</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center">
            <i class="fas fa-graduation-cap text-green-500 mr-3"></i>
            <h3 class="text-lg font-bold">Formations planifiées</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if (empty($formations)): ?>
                <p class="px-6 py-4 text-gray-500 text-sm">Aucune formation à venir</p>
            <?php else: ?>
                <?php foreach ($formations as $f): ?>
                <div class="px-6 py-4 flex items-start justify-between">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($f['titre'] ?? '') ?></p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-calendar mr-1"></i><?= htmlspecialchars(substr($f['date_debut'] ?? '', 0, 16)) ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($f['lieu'] ?? 'Non défini') ?>
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs rounded
                        <?= ($f['statut'] ?? '') === 'actif' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                        <?= htmlspecialchars($f['statut'] ?? 'en_attente') ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-3"></i>
            <h3 class="text-lg font-bold">Événements à venir</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if (empty($evenements)): ?>
                <p class="px-6 py-4 text-gray-500 text-sm">Aucun événement à venir</p>
            <?php else: ?>
                <?php foreach ($evenements as $e): ?>
                <div class="px-6 py-4">
                    <p class="font-medium"><?= htmlspecialchars($e['titre'] ?? '') ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar mr-1"></i><?= htmlspecialchars(substr($e['date_debut'] ?? '', 0, 16)) ?>
                    </p>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-users mr-1"></i><?= $e['nb_inscrits'] ?? 0 ?> inscrits
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>