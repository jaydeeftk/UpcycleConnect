<section class="max-w-5xl mx-auto px-6 lg:px-10 py-16">

    <div class="flex items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold">Mes annonces</h1>
            <p class="text-base-content/60 mt-2">Gérez vos dépôts : retirez une annonce ou marquez-la vendue.</p>
        </div>
        <a href="/annonces/create" class="btn btn-neutral"><i class="fas fa-plus mr-2"></i>Déposer</a>
    </div>

    <?php if (empty($annonces)): ?>
        <div class="text-center py-16 text-base-content/40">
            <i class="fas fa-bullhorn text-4xl mb-3 block"></i>
            <p>Vous n'avez pas encore déposé d'annonce.</p>
            <a href="/annonces/create" class="text-emerald-600 hover:underline text-sm mt-2 inline-block">Déposer votre première annonce</a>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($annonces as $a): ?>
                <?php
                // Statut = affichage. Les ACTIONS viennent du serveur (allowed_actions) :
                // la vue n'applique aucune règle d'état.
                $aa = $a['allowed_actions'] ?? [];
                $badge = [
                    'en_attente' => 'badge-warning', 'validee' => 'badge-success',
                    'refusee' => 'badge-error', 'retiree' => 'badge-ghost', 'vendue' => 'badge-info',
                ];
                $sb = $badge[$a['statut'] ?? ''] ?? 'badge-ghost';
                $id = htmlspecialchars($a['id'] ?? '');
                ?>
                <div class="bg-base-100 rounded-2xl shadow-sm p-5 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold truncate"><?= htmlspecialchars($a['titre'] ?? '(sans titre)') ?></h2>
                            <span class="badge <?= $sb ?> badge-sm"><?= htmlspecialchars($a['statut'] ?? '') ?></span>
                        </div>
                        <p class="text-sm text-base-content/50 truncate">
                            <?= htmlspecialchars($a['categorie'] ?? '') ?> · <?= htmlspecialchars($a['ville'] ?? '') ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="/annonces/<?= $id ?>" class="btn btn-ghost btn-sm">Voir</a>
                        <?php if (in_array('vendre', $aa, true)): ?>
                            <form method="POST" action="/annonces/<?= $id ?>/vendre">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check mr-1"></i>Vendue</button>
                            </form>
                        <?php endif; ?>
                        <?php if (in_array('retirer', $aa, true)): ?>
                            <form method="POST" action="/annonces/<?= $id ?>/annuler" onsubmit="return confirm('Retirer cette annonce ?')">
                                <button type="submit" class="btn btn-outline btn-error btn-sm"><i class="fas fa-times mr-1"></i>Retirer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>
