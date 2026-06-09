<?php
// La formation est-elle déjà passée ? (date dépassée)
$dateFormation = $formation['date'] ?? '';
$tsFormation   = $dateFormation !== '' ? strtotime($dateFormation) : false;
$estPasse      = $tsFormation !== false && $tsFormation < time();
?>
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">
    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
            <img src="<?= uc_image('formation', $formation['id'] ?? ($formation['titre'] ?? '')) ?>"
                 alt="<?= htmlspecialchars($formation['titre'] ?? '') ?>"
                 class="w-full h-full object-cover min-h-[420px]">
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="badge badge-ghost"><?= htmlspecialchars($formation['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-4xl font-bold mb-4"><?= htmlspecialchars($formation['titre'] ?? 'Formation') ?></h1>
            <p class="text-base-content/70 text-lg leading-relaxed mb-8">
                <?= htmlspecialchars($formation['description'] ?? '') ?>
            </p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('fordet_date', 'Date') ?></span>
                    <span class="text-base-content/70"><?= formatDate($formation['date'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('fordet_lieu', 'Lieu') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars($formation['localisation'] ?? '') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('fordet_duree', 'Durée') ?></span>
                    <span class="text-base-content/70"><?= htmlspecialchars((string)($formation['duree'] ?? '')) ?>h</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('fordet_places_dispo', 'Places disponibles') ?></span>
                    <span class="text-base-content/70"><?= $formation['places_dispo'] ?? 0 ?> / <?= $formation['places_total'] ?? 0 ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('fordet_prix', 'Prix') ?></span>
                    <span class="text-2xl font-bold text-purple-500"><?= htmlspecialchars(formatPrix($formation['prix'] ?? 0)) ?></span>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mb-6">
                    <i class="fas fa-check-circle"></i>
                    <span><?= t('fordet_success', 'Vous êtes bien inscrit à cette formation !') ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($_GET['error']) ?></span>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4">
                <?php if ($estPasse): ?>
                    <button disabled class="bg-base-300 text-base-content/50 px-8 py-3 rounded-xl font-medium cursor-not-allowed">
                        <i class="fas fa-calendar-times mr-2"></i><?= t('fordet_past', 'Formation terminée') ?>
                    </button>
                <?php elseif (isset($_SESSION['user'])): ?>
                    <?php if ($formation['est_inscrit'] ?? false): ?>
                        <form method="POST" action="/formations/<?= $formation['id'] ?>/desinscrire">
                        <?= csrf_field() ?>
                            <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-xl font-medium hover:bg-red-700 transition">
                                <i class="fas fa-times mr-2"></i><?= t('fordet_unsubscribe', 'Se désinscrire') ?>
                            </button>
                        </form>
                    <?php elseif (($formation['places_dispo'] ?? 0) > 0): ?>
                        <?php if (($formation['prix'] ?? 0) > 0): ?>
                            <a href="/payer?type=formation&id_item=<?= $formation['id'] ?>&montant=<?= $formation['prix'] ?>&titre=<?= urlencode($formation['titre'] ?? '') ?>"
                               class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                                <i class="fas fa-credit-card mr-2"></i> <?= t('fordet_pay_subscribe', 'Payer et s\'inscrire') ?> (<?= htmlspecialchars(formatPrix($formation['prix'] ?? 0)) ?>)
                            </a>
                        <?php else: ?>
                            <form method="POST" action="/formations/<?= $formation['id'] ?>/inscrire">
                            <?= csrf_field() ?>
                                <button type="submit" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                                    <?= t('fordet_subscribe_free', 'S\'inscrire gratuitement') ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <button disabled class="bg-base-300 text-base-content/50 px-8 py-3 rounded-xl font-medium cursor-not-allowed">
                            <?= t('fordet_full', 'Formation complète') ?>
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="bg-black text-white px-8 py-3 rounded-xl font-medium hover:bg-neutral-800 transition text-center">
                        <?= t('fordet_login_to_subscribe', 'Connectez-vous pour s\'inscrire') ?>
                    </a>
                <?php endif; ?>
                <a href="/catalogue/formations" class="bg-base-200 border border-base-300 px-8 py-3 rounded-xl font-medium hover:bg-base-300 transition text-center">
                    <?= t('fordet_back', 'Retour aux formations') ?>
                </a>
            </div>
        </div>

    </div>
</section>