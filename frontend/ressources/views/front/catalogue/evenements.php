<?php
$typeColors = [
    'Marché'        => 'bg-green-100 text-green-700',
    'Atelier'       => 'bg-purple-100 text-purple-700',
    'Conférence'    => 'bg-blue-100 text-blue-700',
    'Exposition'    => 'bg-pink-100 text-pink-700',
    'Communautaire' => 'bg-orange-100 text-orange-700',
];
?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-600"></i>
            </div>
            <span class="text-sm font-medium text-blue-600 uppercase tracking-wide"><?= t('catevt_breadcrumb', 'Catalogue') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('catevt_title', 'Événements') ?></h1>
        <p class="text-base-content/60 mt-2"><?= t('catevt_subtitle', 'Participez à des rencontres, expositions et marchés autour de l\'upcycling et du développement durable.') ?></p>
    </div>

    <div class="bg-base-100 rounded-2xl shadow-sm p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_type', 'Type') ?></label>
                <select name="type" class="select select-bordered w-full select-sm">
                    <option value=""><?= t('catevt_filter_all', 'Tous') ?></option>
                    <option value="atelier"><?= t('catevt_type_atelier', 'Atelier') ?></option>
                    <option value="marche"><?= t('catevt_type_marche', 'Marché') ?></option>
                    <option value="conference"><?= t('catevt_type_conference', 'Conférence') ?></option>
                    <option value="exposition"><?= t('catevt_type_exposition', 'Exposition') ?></option>
                    <option value="communautaire"><?= t('catevt_type_communautaire', 'Communautaire') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_price', 'Tarif') ?></label>
                <select name="tarif" class="select select-bordered w-full select-sm">
                    <option value=""><?= t('catevt_filter_all', 'Tous') ?></option>
                    <option value="gratuit"><?= t('catevt_price_free', 'Gratuit') ?></option>
                    <option value="payant"><?= t('catevt_price_paid', 'Payant') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_date', 'Date') ?></label>
                <input type="date" name="date" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_location', 'Localisation') ?></label>
                <input type="text" name="localisation" placeholder="<?= t('catevt_location_placeholder', 'Ville ou arrondissement') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_sort_by', 'Trier par') ?></label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="date"><?= t('catevt_filter_date', 'Date') ?></option>
                    <option value="prix_asc"><?= t('catevt_sort_price_asc', 'Prix croissant') ?></option>
                    <option value="popularite"><?= t('catevt_sort_popularity', 'Popularité') ?></option>
                </select>
            </div>
            <div class="md:col-span-5 flex justify-end gap-3">
                <a href="/catalogue/evenements" class="btn btn-ghost btn-sm"><?= t('catevt_reset', 'Réinitialiser') ?></a>
                <button type="submit" class="btn btn-neutral btn-sm">
                    <i class="fas fa-filter mr-2"></i><?= t('catevt_filter_btn', 'Filtrer') ?>
                </button>
            </div>
        </form>
    </div>

    <?php
    // On masque les evenements deja passes (aucune fausse donnee de demonstration).
    $evenements = array_values(array_filter($evenements ?? [], function ($e) {
        $d = $e['date'] ?? '';
        if ($d === '') return true;
        $ts = strtotime($d);
        return $ts === false || $ts >= time();
    }));
    ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-base-content/50"><?= count($evenements) ?> <?= t('catevt_results_found', 'événement(s) trouvé(s)') ?></p>
    </div>

    <?php if (empty($evenements)): ?>
        <div class="text-center py-20 text-base-content/40">
            <i class="fas fa-calendar-alt text-5xl mb-4 block"></i>
            <p class="text-lg"><?= t('catevt_empty', 'Aucun événement à venir pour le moment.') ?></p>
        </div>
    <?php else: ?>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($evenements as $ev):
            $date         = $ev['date']         ?? '';
            $type         = $ev['type']         ?? ($ev['statut'] ?? '');
            $titre        = $ev['titre']        ?? '';
            $description  = $ev['description']  ?? '';
            $prix         = isset($ev['prix'])   ? $ev['prix'] : null;
            $lieu         = $ev['lieu']         ?? '';
            $participants = $ev['participants'] ?? ($ev['capacite'] ?? '?');
            $colorClass   = $typeColors[$type]  ?? 'bg-blue-100 text-blue-700';
            $imgUrl       = uc_image('evenement', $ev['id'] ?? $titre);
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="w-full h-48 relative overflow-hidden">
                    <img src="<?= $imgUrl ?>"
                         alt="<?= htmlspecialchars($titre) ?>"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-sm <?= $colorClass ?> border-0"><?= htmlspecialchars(formatStatut($type)) ?></span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <?php if ($prix === 0 || $prix === null): ?>
                            <span class="badge badge-success badge-sm"><?= t('catevt_price_free', 'Gratuit') ?></span>
                        <?php else: ?>
                            <span class="badge badge-ghost badge-sm bg-white/90"><?= htmlspecialchars(formatPrix($prix)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="absolute bottom-3 left-4 right-4">
                        <p class="text-white font-semibold text-base leading-tight line-clamp-1"><?= htmlspecialchars($titre) ?></p>
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-sm text-base-content/60 mb-4 line-clamp-2"><?= htmlspecialchars($description) ?></p>
                    <div class="space-y-1.5 mb-4 text-xs text-base-content/50">
                        <div><i class="fas fa-calendar-alt mr-2 w-3"></i><?= htmlspecialchars(formatDate($date, true)) ?></div>
                        <div><i class="fas fa-map-marker-alt mr-2 w-3"></i><?= htmlspecialchars($lieu) ?></div>
                        <div><i class="fas fa-users mr-2 w-3"></i><?= htmlspecialchars((string)$participants) ?> <?= t('catevt_participants', 'participant(s)') ?></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold"><?= htmlspecialchars(formatPrix($prix)) ?></span>
                        <a href="/evenements/<?= $ev['id'] ?>" class="btn btn-neutral btn-sm"><?= t('catevt_participate', 'Participer') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>