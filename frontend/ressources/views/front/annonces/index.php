<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-bullhorn text-green-600"></i>
            </div>
            <span class="text-sm font-medium text-green-600 uppercase tracking-wide">Marketplace</span>
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold"><?= t('annidx_title', 'Toutes les annonces') ?></h1>
                <p class="text-base-content/60 mt-2"><?= t('annidx_subtitle', 'Trouvez des objets à récupérer ou à acheter près de chez vous.') ?></p>
            </div>
            <?php if (isset($_SESSION['user'])): ?>
                <div class="flex items-center gap-2">
                    <a href="/mes-annonces" class="btn btn-ghost">
                        <i class="fas fa-list mr-2"></i> <?= t('annidx_my_ads', 'Mes annonces') ?>
                    </a>
                    <a href="/annonces/create" class="btn btn-neutral">
                        <i class="fas fa-plus mr-2"></i> <?= t('annidx_post_ad', 'Déposer une annonce') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-8">
        <a href="?type=tous" class="btn btn-sm <?= ($_GET['type'] ?? 'tous') === 'tous' ? 'btn-neutral' : 'btn-ghost' ?>">
            <?= t('annidx_filter_all', 'Tout voir') ?>
        </a>
        <a href="?type=don" class="btn btn-sm <?= ($_GET['type'] ?? '') === 'don' ? 'btn-neutral' : 'btn-ghost' ?>">
            <i class="fas fa-heart mr-2 text-green-500"></i> <?= t('annidx_filter_don', 'Dons') ?>
        </a>
        <a href="?type=vente" class="btn btn-sm <?= ($_GET['type'] ?? '') === 'vente' ? 'btn-neutral' : 'btn-ghost' ?>">
            <i class="fas fa-tag mr-2 text-blue-500"></i> <?= t('annidx_filter_vente', 'Ventes') ?>
        </a>
    </div>

    <?php
    $type = $_GET['type'] ?? 'tous';
    $annoncesFiltered = $annonces ?? [];
    if ($type === 'don') {
        $annoncesFiltered = array_filter($annonces, fn($a) => ($a['type_annonce'] ?? '') === 'don');
    }
    if ($type === 'vente') {
        $annoncesFiltered = array_filter($annonces, fn($a) => ($a['type_annonce'] ?? '') === 'vente');
    }
    ?>

    <?php if (empty($annoncesFiltered)): ?>
        <div class="text-center py-20 text-base-content/40">
            <i class="fas fa-box-open text-5xl mb-4 block"></i>
            <p class="text-lg"><?= t('annidx_empty', 'Aucune annonce disponible pour le moment.') ?></p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($annoncesFiltered as $annonce): ?>
                <a href="/annonces/<?= $annonce['id'] ?>" class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6 hover:shadow-md transition flex flex-col gap-4 block">
                    <div class="h-40 -mx-6 -mt-6 mb-0 overflow-hidden rounded-t-2xl">
                        <img src="<?= uc_image('objet', $annonce['id'] ?? ($annonce['titre'] ?? '')) ?>"
                             alt="<?= htmlspecialchars($annonce['titre'] ?? '') ?>"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if (($annonce['type_annonce'] ?? '') === 'vente'): ?>
                            <span class="badge badge-ghost badge-sm gap-1">
                                <i class="fas fa-tag text-blue-500"></i> <?= t('annidx_badge_vente', 'Vente') ?>
                            </span>
                            <span class="font-semibold text-blue-500"><?= $annonce['prix'] ?? 0 ?>€</span>
                        <?php else: ?>
                            <span class="badge badge-ghost badge-sm gap-1">
                                <i class="fas fa-heart text-green-500"></i> <?= t('annidx_badge_don', 'Don gratuit') ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($annonce['categorie'] ?? '') ?></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($annonce['titre'] ?? '') ?></h3>
                        <p class="text-sm text-base-content/60 line-clamp-2"><?= htmlspecialchars($annonce['description'] ?? '') ?></p>
                    </div>
                    <div class="flex items-center justify-between text-xs text-base-content/50 mt-auto pt-3 border-t border-base-300">
                        <span><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($annonce['ville'] ?? '') ?></span>
                        <span><i class="fas fa-box mr-1"></i><?= t('annidx_state_label', 'État :') ?> <?= htmlspecialchars($annonce['etat'] ?? '') ?></span>
                    </div>
                    <div class="text-xs text-base-content/50">
                        <i class="fas fa-user mr-1"></i><?= htmlspecialchars($annonce['auteur'] ?? '') ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>