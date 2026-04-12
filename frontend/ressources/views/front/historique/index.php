<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-teal-100 flex items-center justify-center">
                <i class="fas fa-history text-teal-600"></i>
            </div>
            <span class="text-sm font-medium text-teal-600 uppercase tracking-wide">Mes dépôts</span>
        </div>
        <h1 class="text-3xl font-bold">Historique des dépôts</h1>
        <p class="text-base-content/60 mt-2">Retrouvez tous vos dépôts d'objets en conteneur.</p>
    </div>

    <?php if (empty($historique)): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-12 text-center">
            <i class="fas fa-box-open text-5xl text-base-content/20 mb-4 block"></i>
            <p class="text-base-content/60 mb-4">Aucun dépôt enregistré pour le moment.</p>
            <a href="/conteneurs/create" class="btn btn-neutral btn-sm">Déposer un objet</a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($historique as $item): ?>
                <?php
                $statutColor = match($item['statut'] ?? '') {
                    'valide', 'validé' => 'badge-success',
                    'refuse', 'refusé' => 'badge-error',
                    default => 'badge-warning',
                };
                ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-teal-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-recycle text-teal-500 text-lg"></i>
                        </div>
                        <div>
                            <div class="font-semibold">Dépôt du <?= htmlspecialchars(substr($item['date'] ?? '', 0, 10)) ?></div>
                            <?php if (!empty($item['observations'])): ?>
                                <p class="text-sm text-base-content/60 mt-0.5"><?= htmlspecialchars($item['observations']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge <?= $statutColor ?> flex-shrink-0"><?= htmlspecialchars($item['statut'] ?? 'en attente') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
