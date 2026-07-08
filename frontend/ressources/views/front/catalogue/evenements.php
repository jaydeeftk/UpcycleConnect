<?php
$typeInfo = [
    'marche'        => ['label' => t('catevt_type_marche', 'Marché'),        'class' => 'bg-green-100 text-green-700'],
    'atelier'       => ['label' => t('catevt_type_atelier', 'Atelier'),       'class' => 'bg-purple-100 text-purple-700'],
    'conference'    => ['label' => t('catevt_type_conference', 'Conférence'), 'class' => 'bg-blue-100 text-blue-700'],
    'exposition'    => ['label' => t('catevt_type_exposition', 'Exposition'), 'class' => 'bg-pink-100 text-pink-700'],
    'communautaire' => ['label' => t('catevt_type_communautaire', 'Communautaire'), 'class' => 'bg-orange-100 text-orange-700'],
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
                    <option value="" <?= ($_GET['type'] ?? '') === '' ? 'selected' : '' ?>><?= t('catevt_filter_all', 'Tous') ?></option>
                    <option value="atelier" <?= ($_GET['type'] ?? '') === 'atelier' ? 'selected' : '' ?>><?= t('catevt_type_atelier', 'Atelier') ?></option>
                    <option value="marche" <?= ($_GET['type'] ?? '') === 'marche' ? 'selected' : '' ?>><?= t('catevt_type_marche', 'Marché') ?></option>
                    <option value="conference" <?= ($_GET['type'] ?? '') === 'conference' ? 'selected' : '' ?>><?= t('catevt_type_conference', 'Conférence') ?></option>
                    <option value="exposition" <?= ($_GET['type'] ?? '') === 'exposition' ? 'selected' : '' ?>><?= t('catevt_type_exposition', 'Exposition') ?></option>
                    <option value="communautaire" <?= ($_GET['type'] ?? '') === 'communautaire' ? 'selected' : '' ?>><?= t('catevt_type_communautaire', 'Communautaire') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_price', 'Tarif') ?></label>
                <select name="tarif" class="select select-bordered w-full select-sm">
                    <option value="" <?= ($_GET['tarif'] ?? '') === '' ? 'selected' : '' ?>><?= t('catevt_filter_all', 'Tous') ?></option>
                    <option value="gratuit" <?= ($_GET['tarif'] ?? '') === 'gratuit' ? 'selected' : '' ?>><?= t('catevt_price_free', 'Gratuit') ?></option>
                    <option value="payant" <?= ($_GET['tarif'] ?? '') === 'payant' ? 'selected' : '' ?>><?= t('catevt_price_paid', 'Payant') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_date', 'Date') ?></label>
                <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_filter_location', 'Localisation') ?></label>
                <input type="text" name="localisation" value="<?= htmlspecialchars($_GET['localisation'] ?? '') ?>" placeholder="<?= t('catevt_location_placeholder', 'Ville ou arrondissement') ?>" class="input input-bordered w-full input-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-base-content/50 mb-2 uppercase"><?= t('catevt_sort_by', 'Trier par') ?></label>
                <select name="tri" class="select select-bordered w-full select-sm">
                    <option value="date" <?= ($_GET['tri'] ?? 'date') === 'date' ? 'selected' : '' ?>><?= t('catevt_filter_date', 'Date') ?></option>
                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>><?= t('catevt_sort_price_asc', 'Prix croissant') ?></option>
                    <option value="popularite" <?= ($_GET['tri'] ?? '') === 'popularite' ? 'selected' : '' ?>><?= t('catevt_sort_popularity', 'Popularité') ?></option>
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
    
    $evenements = array_values(array_filter($evenements ?? [], function ($e) {
        $d = $e['date'] ?? '';
        if ($d === '') return true;
        $ts = strtotime($d);
        return $ts === false || $ts >= time();
    }));
    ?>

    <div class="flex items-center justify-between mb-6">
        <?php $ne = count($evenements); ?>
        <p class="text-sm text-base-content/50"><?= $ne ?> <?= t('catevt_results_count', 'événement') ?><?= $ne > 1 ? 's' : '' ?> <?= t('catevt_results_found', 'trouvé') ?></p>
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
            $categorie    = $ev['categorie']    ?? 'atelier';
            $titre        = $ev['titre']        ?? '';
            $description  = $ev['description']  ?? '';
            $prix         = isset($ev['prix'])   ? $ev['prix'] : null;
            $lieu         = $ev['lieu']         ?? '';
            $duree        = $ev['duree']        ?? 0;
            $participants = $ev['participants'] ?? 0;
            $capacite     = $ev['capacite'] ?? 0;
            $info         = $typeInfo[$categorie] ?? $typeInfo['atelier'];
            $imgUrl       = uc_image('evenement', $ev['id'] ?? $titre);
        ?>
            <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="w-full h-48 relative overflow-hidden">
                    <img src="<?= $imgUrl ?>"
                         alt="<?= htmlspecialchars($titre) ?>"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-sm <?= $info['class'] ?> border-0"><?= htmlspecialchars($info['label']) ?></span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <?php if ($prix === 0 || $prix === null): ?>
                            <span class="badge badge-success badge-sm"><?= t('catevt_price_free', 'Gratuit') ?></span>
                        <?php else: ?>
                            <span class="badge badge-ghost badge-sm bg-base-100/90 text-base-content border-0"><?= htmlspecialchars(formatPrix($prix)) ?></span>
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
                        <?php if ($duree > 0): ?>
                            <div><i class="fas fa-hourglass-half mr-2 w-3"></i><?= (int)$duree ?> <?= t('catevt_duration_hours', 'h') ?></div>
                        <?php endif; ?>
                        <div><i class="fas fa-users mr-2 w-3"></i><?= htmlspecialchars((string)$participants) ?>/<?= htmlspecialchars((string)$capacite) ?> <?= t('catevt_participants', 'participant') ?><?= ((int)$capacite) > 1 ? 's' : '' ?></div>
                    </div>
                    <div class="flex items-center justify-end">
                        <a href="/evenements/<?= $ev['id'] ?>" class="btn btn-neutral btn-sm"><?= t('catevt_participate', 'Participer') ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</section>