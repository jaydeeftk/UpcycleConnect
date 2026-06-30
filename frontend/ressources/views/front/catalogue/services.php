<?php /* illustrations : helper uc_image() (pools varies, choix deterministe par id) */ ?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                <i class="fas fa-tools text-orange-600"></i>
            </div>
            <span class="text-sm font-medium text-orange-600 uppercase tracking-wide"><?= t('catsvc_breadcrumb', 'Catalogue') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('catsvc_title', 'Services') ?></h1>
        <p class="text-base-content/60 mt-2"><?= t('catsvc_subtitle', 'Trouvez un professionnel pour réparer, transformer ou recycler vos objets.') ?></p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catsvc_filter_category', 'Catégorie') ?></label>
                <select name="categorie" class="select select-bordered w-full select-sm">
                    <option value=""><?= t('catsvc_filter_all', 'Toutes') ?></option>
                    <option value="reparation" <?= ($_GET['categorie'] ?? '') === 'reparation' ? 'selected' : '' ?>><?= t('catsvc_cat_reparation', 'Réparation') ?></option>
                    <option value="transformation" <?= ($_GET['categorie'] ?? '') === 'transformation' ? 'selected' : '' ?>><?= t('catsvc_cat_transformation', 'Transformation') ?></option>
                    <option value="recyclage" <?= ($_GET['categorie'] ?? '') === 'recyclage' ? 'selected' : '' ?>><?= t('catsvc_cat_recyclage', 'Recyclage') ?></option>
                    <option value="upcycling" <?= ($_GET['categorie'] ?? '') === 'upcycling' ? 'selected' : '' ?>><?= t('catsvc_cat_upcycling', 'Upcycling créatif') ?></option>
                    <option value="nettoyage" <?= ($_GET['categorie'] ?? '') === 'nettoyage' ? 'selected' : '' ?>><?= t('catsvc_cat_nettoyage', 'Nettoyage') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catsvc_filter_price_max', 'Prix max (€)') ?></label>
                <input type="number" name="prix_max" min="0" placeholder="<?= t('catsvc_filter_price_ph', 'Ex : 100') ?>" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catsvc_filter_sort', 'Trier par') ?></label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="pertinence"><?= t('catsvc_sort_relevance', 'Pertinence') ?></option>
                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>><?= t('catsvc_sort_price_asc', 'Prix croissant') ?></option>
                    <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>><?= t('catsvc_sort_price_desc', 'Prix décroissant') ?></option>
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end gap-3">
                <a href="/catalogue/services" class="btn btn-ghost btn-sm"><?= t('catsvc_reset_btn', 'Réinitialiser') ?></a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i><?= t('catsvc_filter_btn', 'Filtrer') ?>
                </button>
            </div>
        </form>
    </div>

    <?php $services = $services ?? []; ?>

    <div class="flex items-center justify-between mb-6">
        <?php $n = count($services); ?>
        <p class="text-sm text-base-content/50"><?= $n ?> <?= t('catsvc_results_count', 'service') ?><?= $n > 1 ? 's' : '' ?> <?= t('catsvc_results_found', 'trouvé') ?></p>
    </div>

    <?php if (empty($services)): ?>
        <div class="text-center py-20 text-base-content/40">
            <i class="fas fa-tools text-5xl mb-4 block"></i>
            <p class="text-lg"><?= t('catsvc_empty', 'Aucun service disponible pour le moment.') ?></p>
        </div>
    <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($services as $service):
            $cat    = strtolower($service['categorie'] ?? '');
            $imgUrl = uc_image('service', $service['id'] ?? ($service['titre'] ?? $cat));
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="w-full h-48 relative overflow-hidden">
                    <img src="<?= $imgUrl ?>"
                         alt="<?= htmlspecialchars($service['titre']) ?>"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-sm bg-white/90 text-gray-800 border-0" style="color:#1f2937!important"><?= htmlspecialchars($service['categorie'] ?? '') ?></span>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($service['titre']) ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($service['description']) ?></p>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xl font-bold"><?= t('catsvc_price_from', 'À partir de') ?> <?= htmlspecialchars(formatPrix($service['prix'] ?? 0)) ?></span>
                            <div class="text-xs text-base-content/40 mt-0.5"><i class="fas fa-clock mr-1"></i><?= (int)($service['duree'] ?? 0) ?> <?= t('unit_days','jour(s)') ?></div>
                        </div>
                        <a href="/services/<?= $service['id'] ?>" class="btn btn-neutral btn-sm"><?= t('catsvc_view_btn', 'Voir') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>