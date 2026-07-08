<?php
$cmd  = $commande ?? null;
$paye = is_array($cmd) && ($cmd['trouve'] ?? false) && in_array($cmd['statut'] ?? '', ['paye', 'payee'], true);
$type = $cmd['type'] ?? '';
$estService = in_array($type, ['devis_prestation', 'prestation_catalogue'], true);
$retourUrl = '/paiements';
$retourLabel = t('paysucc_view_payments', 'Voir mes paiements');
if ($estService) {
    $retourUrl = '/mes-prestations';
    $retourLabel = t('paysucc_view_services', 'Voir mes prestations réservées');
} elseif ($type === 'annonce') {
    $retourUrl = '/mes-annonces';
    $retourLabel = t('paysucc_view_annonces', 'Voir mes annonces');
}
?>
<section class="max-w-lg mx-auto px-6 py-16">
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-10 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-4xl text-green-500"></i>
        </div>
        <h1 class="text-2xl font-bold mb-3"><?= t('paysucc_title', 'Paiement confirmé !') ?></h1>

        <?php if ($paye): ?>
            <p class="text-base-content/60 mb-6"><?= t('paysucc_message', 'Votre commande a bien été enregistrée.') ?></p>
            <div class="bg-base-200 rounded-xl p-4 mb-8 text-sm text-left space-y-1">
                <div class="flex justify-between"><span class="text-base-content/60"><?= t('paysucc_amount', 'Montant') ?></span><span class="font-semibold"><?= htmlspecialchars(formatPrix($cmd['montant'] ?? 0)) ?></span></div>
                <div class="flex justify-between"><span class="text-base-content/60"><?= t('paysucc_invoice', 'Facture') ?></span><span class="font-mono"><?= htmlspecialchars($cmd['numero_facture'] ?? '') ?></span></div>
                <div class="flex justify-between"><span class="text-base-content/60"><?= t('paysucc_status', 'Statut') ?></span><span class="text-green-600 font-semibold"><?= t('payidx_status_paid', 'Payé') ?></span></div>
            </div>
            <?php if (!empty($cmd['id_facture'])): ?>
                <a href="/factures/<?= (int)$cmd['id_facture'] ?>/pdf" target="_blank" class="btn btn-outline mb-3 w-full">
                    <i class="fas fa-file-pdf mr-2"></i> <?= t('paysucc_download_invoice', 'Télécharger la facture') ?>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-base-content/60 mb-8"><?= t('paysucc_pending', 'Votre paiement est en cours de traitement ; votre commande apparaîtra dans vos paiements dans un instant.') ?></p>
        <?php endif; ?>

        <a href="<?= htmlspecialchars($retourUrl) ?>" class="btn btn-neutral w-full">
            <i class="fas fa-receipt mr-2"></i> <?= $retourLabel ?>
        </a>
    </div>

    <?php if ($estService): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8 mt-6">
            <h2 class="font-semibold text-lg mb-6 flex items-center gap-2">
                <i class="fas fa-route text-green-500"></i> <?= t('paysucc_next_title', 'Que se passe-t-il ensuite ?') ?>
            </h2>
            <ol class="space-y-5">
                <li class="flex items-start gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">1</span>
                    <div>
                        <p class="font-medium"><?= t('paysucc_step1_title', 'Le prestataire est notifié') ?></p>
                        <p class="text-sm text-base-content/60"><?= t('paysucc_step1_text', 'Il reçoit votre commande et la photo de l\'objet pour préparer son intervention.') ?></p>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">2</span>
                    <div>
                        <p class="font-medium"><?= t('paysucc_step2_title', 'Un projet de suivi est créé') ?></p>
                        <p class="text-sm text-base-content/60"><?= t('paysucc_step2_text', 'Vous suivrez chaque étape de la transformation, photos à l\'appui, depuis vos prestations réservées.') ?></p>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold">3</span>
                    <div>
                        <p class="font-medium"><?= t('paysucc_step3_title', 'Échangez avec le prestataire') ?></p>
                        <p class="text-sm text-base-content/60"><?= t('paysucc_step3_text', 'Une conversation est ouverte pour convenir de la remise de l\'objet et répondre à vos questions.') ?></p>
                    </div>
                </li>
            </ol>
            <a href="/messages" class="btn btn-outline btn-sm mt-6 w-full">
                <i class="fas fa-comments mr-2"></i> <?= t('paysucc_open_messaging', 'Ouvrir la messagerie') ?>
            </a>
        </div>
    <?php endif; ?>
</section>
