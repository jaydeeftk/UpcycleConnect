<?php
$type   = $evenement['type'] ?? $evenement['statut'] ?? '';
$imgUrl = !empty($evenement['image_url'])
    ? $evenement['image_url']
    : uc_image('evenement', $evenement['id'] ?? ($evenement['titre'] ?? ''));
$evtDate  = $evenement['date'] ?? '';
$estPasse = $evtDate !== '' && ($tsEvt = strtotime($evtDate)) !== false && $tsEvt < time();
?>

<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-8">
        <a href="/catalogue/evenements" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition">
            <i class="fas fa-arrow-left"></i> <?= t('evtdet_back', 'Retour aux événements') ?>
        </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="rounded-3xl overflow-hidden shadow-sm">
            <img src="<?= htmlspecialchars($imgUrl) ?>"
                 alt="<?= htmlspecialchars($evenement['titre'] ?? '') ?>"
                 class="w-full h-[420px] object-cover">
        </div>

        <div>
            <?php if ($type): ?>
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">
                    <?= htmlspecialchars(formatStatut($type)) ?>
                </span>
            <?php endif; ?>

            <div class="text-sm text-base-content/60 mb-2">
                <?= formatDate($evenement['date'] ?? '') ?> • <?= htmlspecialchars($evenement['lieu'] ?? '') ?>
            </div>

            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                <?= htmlspecialchars($evenement['titre'] ?? 'Événement') ?>
            </h1>

            <p class="text-base-content/70 text-lg leading-relaxed mb-8">
                <?= htmlspecialchars($evenement['description'] ?? '') ?>
            </p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500 w-4"></i> <?= t('evtdet_date', 'Date') ?>
                    </span>
                    <span class="text-base-content/70"><?= formatDate($evenement['date'] ?? '') ?></span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-red-500 w-4"></i> <?= t('evtdet_location', 'Lieu') ?>
                    </span>
                    <span class="text-base-content/70"><?= htmlspecialchars($evenement['lieu'] ?? '') ?></span>
                </div>
                <?php if (!empty($evenement['capacite'])): ?>
                <div class="flex justify-between items-center py-2 border-b border-base-200">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-users text-purple-500 w-4"></i> <?= t('evtdet_capacity', 'Capacité') ?>
                    </span>
                    <span class="text-base-content/70">
                        <?php
                            $places_restantes = max(0, (int)$evenement['capacite'] - (int)($evenement['participants'] ?? 0));
                        ?>
                        <?= $places_restantes ?> / <?= htmlspecialchars($evenement['capacite']) ?> <?= t('evtdet_seats_left', 'places restantes') ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center py-2">
                    <span class="font-medium flex items-center gap-2">
                        <i class="fas fa-tag text-green-500 w-4"></i> <?= t('evtdet_price', 'Prix') ?>
                    </span>
                    <span class="font-bold text-lg <?= ($evenement['prix'] ?? 0) > 0 ? 'text-green-600' : 'text-base-content/70' ?>">
                        <?= htmlspecialchars(formatPrix($evenement['prix'] ?? 0)) ?>
                    </span>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span><?= t('evtdet_success_register', 'Vous êtes bien inscrit à cet événement !') ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success_desinscription'])): ?>
                <div class="alert alert-info mb-6">
                    <i class="fas fa-info-circle"></i>
                    <span><?= t('evtdet_success_unregister', 'Vous avez été désinscrit de cet événement.') ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4">
                <?php if ($estPasse): ?>
                    <span class="inline-flex items-center gap-2 bg-base-200 text-base-content/60 px-8 py-3 rounded-xl font-medium">
                        <i class="fas fa-clock"></i><?= t('evtdet_passed', 'Événement terminé') ?>
                    </span>
                <?php elseif (isset($_SESSION['user'])): ?>
                    <?php if ($evenement['est_inscrit'] ?? false): ?>
                        <form method="POST" action="/evenements/<?= $evenement['id'] ?? '' ?>/desinscrire">
                        <?= csrf_field() ?>
                            <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl font-medium hover:bg-red-700 transition">
                                <i class="fas fa-times mr-2"></i><?= t('evtdet_btn_unregister', 'Se désinscrire') ?>
                            </button>
                        </form>
                    <?php elseif (($evenement['prix'] ?? 0) > 0): ?>
                        <a href="/payer?type=evenement&id_item=<?= $evenement['id'] ?? '' ?>&montant=<?= $evenement['prix'] ?? 0 ?>&titre=<?= urlencode($evenement['titre'] ?? '') ?>"
                           class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                            <i class="fas fa-credit-card mr-2"></i><?= t('evtdet_btn_register_paid', "S'inscrire — ") ?><?= htmlspecialchars(formatPrix($evenement['prix'])) ?>
                        </a>
                    <?php else: ?>
                        <form method="POST" action="/evenements/<?= $evenement['id'] ?? '' ?>/participer">
                        <?= csrf_field() ?>
                            <button type="submit" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                <i class="fas fa-check mr-2"></i><?= t('evtdet_btn_register', "S'inscrire à l'événement") ?>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                        <?= t('evtdet_login_to_register', "Connectez-vous pour s'inscrire") ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>