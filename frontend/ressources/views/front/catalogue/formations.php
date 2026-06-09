<?php /* illustrations : helper uc_image() (pools varies, choix deterministe par id) */ ?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span><?= t('catfor_inscription_success', 'Vous êtes bien inscrit à cette formation !') ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600"></i>
            </div>
            <span class="text-sm font-medium text-purple-600 uppercase tracking-wide"><?= t('catfor_breadcrumb', 'Catalogue') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('catfor_title', 'Formations') ?></h1>
        <p class="text-base-content/60 mt-2"><?= t('catfor_subtitle', 'Apprenez les techniques d\'upcycling et de développement durable avec nos formateurs experts.') ?></p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catfor_filter_category', 'Catégorie') ?></label>
                <select name="categorie" class="select select-bordered w-full select-sm">
                    <option value=""><?= t('catfor_filter_category_all', 'Toutes') ?></option>
                    <?php foreach ($categories ?? [] as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($_GET['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catfor_filter_price_max', 'Prix max (€)') ?></label>
                <input type="number" name="prix_max" min="0" placeholder="<?= t('catfor_filter_price_placeholder', 'Ex : 50') ?>" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catfor_filter_date', 'Date') ?></label>
                <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catfor_filter_places', 'Places dispo') ?></label>
                <select name="places" class="select select-bordered w-full select-sm">
                    <option value=""><?= t('catfor_filter_places_any', 'Peu importe') ?></option>
                    <option value="1" <?= ($_GET['places'] ?? '') === '1' ? 'selected' : '' ?>><?= t('catfor_filter_places_1', 'Au moins 1 place') ?></option>
                    <option value="5" <?= ($_GET['places'] ?? '') === '5' ? 'selected' : '' ?>><?= t('catfor_filter_places_5', 'Au moins 5 places') ?></option>
                    <option value="10" <?= ($_GET['places'] ?? '') === '10' ? 'selected' : '' ?>><?= t('catfor_filter_places_10', 'Au moins 10 places') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catfor_filter_sort', 'Trier par') ?></label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="date" <?= ($_GET['tri'] ?? 'date') === 'date' ? 'selected' : '' ?>><?= t('catfor_filter_date', 'Date') ?></option>
                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>><?= t('catfor_sort_price_asc', 'Prix croissant') ?></option>
                    <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>><?= t('catfor_sort_price_desc', 'Prix décroissant') ?></option>
                    <option value="places" <?= ($_GET['tri'] ?? '') === 'places' ? 'selected' : '' ?>><?= t('catfor_sort_places', 'Places restantes') ?></option>
                </select>
            </div>
            <div class="md:col-span-5 flex justify-end gap-3">
                <a href="/catalogue/formations" class="btn btn-ghost btn-sm"><?= t('catfor_reset', 'Réinitialiser') ?></a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i><?= t('catfor_filter_btn', 'Filtrer') ?>
                </button>
            </div>
        </form>
    </div>

    <?php
    // Masque les formations déjà passées (la date est dépassée).
    $formations = array_values(array_filter($formations ?? [], function ($f) {
        $d = $f['date'] ?? '';
        if ($d === '') return true;
        $ts = strtotime($d);
        return $ts === false || $ts >= time();
    }));
    ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($formations) ?> <?= t('catfor_results_count', 'formation(s) trouvée(s)') ?></p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($formations as $formation):
            $complet  = ($formation['places_dispo'] ?? 0) === 0;
            $presque  = ($formation['places_dispo'] ?? 0) > 0 && ($formation['places_dispo'] ?? 0) <= 3;
            $cat      = strtolower($formation['categorie'] ?? '');
            $imgUrl   = uc_image('formation', $formation['id'] ?? ($formation['titre'] ?? $cat));
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition <?= $complet ? 'opacity-70' : '' ?>">
                <div class="w-full h-48 relative overflow-hidden">
                    <img src="<?= $imgUrl ?>"
                         alt="<?= htmlspecialchars($formation['titre'] ?? '') ?>"
                         class="w-full h-full object-cover <?= $complet ? 'grayscale' : '' ?> transition-transform duration-500 hover:scale-105">
                    <?php if ($complet): ?>
                        <div class="absolute inset-0 bg-base-300/70 flex items-center justify-center">
                            <span class="badge badge-error badge-lg"><?= t('catfor_full', 'Complet') ?></span>
                        </div>
                    <?php elseif ($presque): ?>
                        <div class="absolute top-3 right-3">
                            <span class="badge badge-warning badge-sm"><?= t('catfor_almost_full_before', 'Plus que') ?> <?= $formation['places_dispo'] ?> <?= t('catfor_almost_full_after', 'place(s) !') ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/40 to-transparent"></div>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($formation['categorie'] ?? '') ?></span>
                        <span class="text-xs text-base-content/40"><i class="fas fa-clock mr-1"></i><?= ($formation['duree'] ?? '') ?>h</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($formation['titre'] ?? '') ?></h3>
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($formation['description'] ?? '') ?></p>
                    <div class="space-y-2 mb-4 text-xs text-base-content/50">
                        <div><i class="fas fa-calendar-alt mr-2"></i><?= formatDate($formation['date'] ?? '') ?></div>
                        <div><i class="fas fa-map-marker-alt mr-2"></i><?= $formation['localisation'] ?? '' ?></div>
                        <div>
                            <i class="fas fa-users mr-2"></i>
                            <?php if ($complet): ?>
                                <span class="text-red-500 font-medium"><?= t('catfor_full', 'Complet') ?></span>
                            <?php else: ?>
                                <span class="<?= $presque ? 'text-orange-500 font-medium' : '' ?>"><?= $formation['places_dispo'] ?? 0 ?> / <?= $formation['places_total'] ?? 0 ?> <?= t('catfor_places_remaining', 'places restantes') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-full bg-base-200 rounded-full h-1.5 mb-4">
                        <?php
                        $total = $formation['places_total'] ?? 1;
                        $dispo = $formation['places_dispo'] ?? 0;
                        $pct   = $total > 0 ? round(($total - $dispo) / $total * 100) : 0;
                        ?>
                        <div class="h-1.5 rounded-full <?= $complet ? 'bg-red-400' : ($presque ? 'bg-orange-400' : 'bg-purple-400') ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold"><?= htmlspecialchars(formatPrix($formation['prix'] ?? 0)) ?></span>
                        <?php if ($complet): ?>
                            <button class="btn btn-disabled btn-sm" disabled><?= t('catfor_full', 'Complet') ?></button>
                        <?php else: ?>
                            <a href="/formations/<?= $formation['id'] ?>" class="btn btn-neutral btn-sm"><?= t('catfor_view_btn', 'Voir la formation') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</section>