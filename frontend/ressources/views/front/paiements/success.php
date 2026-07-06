<?php
$cmd  = $commande ?? null;
$paye = is_array($cmd) && ($cmd['trouve'] ?? false) && in_array($cmd['statut'] ?? '', ['paye', 'payee'], true);
$type = $cmd['type'] ?? '';
$retourUrl = '/paiements';
$retourLabel = t('paysucc_view_payments', 'Voir mes paiements');
if (in_array($type, ['devis_prestation', 'prestation_catalogue'], true)) {
    $retourUrl = '/mes-prestations';
    $retourLabel = t('paysucc_view_services', 'Voir mes prestations réservées');
} elseif ($type === 'annonce') {
    $retourUrl = '/mes-annonces';
    $retourLabel = t('paysucc_view_annonces', 'Voir mes annonces');
}
?>
<section class="max-w-lg mx-auto px-6 py-16 text-center">
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-10">
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
</section>
