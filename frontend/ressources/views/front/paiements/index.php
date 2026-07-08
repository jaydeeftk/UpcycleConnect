<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('payidx_title', 'Paiements') ?></h1>
        <p class="text-lg text-base-content/70 max-w-2xl">
            <?= t('payidx_subtitle', 'Consultez vos paiements à venir et l\'historique de vos règlements.') ?>
        </p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4"><?= t('payidx_login_required', 'Vous devez être connecté pour voir vos paiements.') ?></p>
            <a href="/login"
                class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                <?= t('payidx_login_btn', 'Se connecter') ?>
            </a>
        </div>
    <?php else: ?>
        <?php $filtre = $filtre ?? 'tous'; ?>
        <form method="GET" action="/paiements" class="mb-8 flex items-center gap-3">
            <label for="type" class="text-sm font-medium text-base-content/70"><?= t('payidx_filter_label', 'Filtrer par type') ?></label>
            <select name="type" id="type" onchange="this.form.submit()"
                    class="border border-base-300 rounded-lg px-3 py-2 text-sm bg-base-100">
                <option value="tous" <?= ($filtre === 'tous' || $filtre === '') ? 'selected' : '' ?>><?= t('payidx_filter_all', 'Tous') ?></option>
                <option value="formation" <?= $filtre === 'formation' ? 'selected' : '' ?>><?= t('payidx_filter_formation', 'Formations') ?></option>
                <option value="evenement" <?= $filtre === 'evenement' ? 'selected' : '' ?>><?= t('payidx_filter_evenement', 'Événements') ?></option>
                <option value="annonce" <?= $filtre === 'annonce' ? 'selected' : '' ?>><?= t('payidx_filter_annonce', 'Achats annonces') ?></option>
                <option value="prestation_catalogue" <?= $filtre === 'prestation_catalogue' ? 'selected' : '' ?>><?= t('payidx_filter_prestation', 'Prestations') ?></option>
                <option value="devis_prestation" <?= $filtre === 'devis_prestation' ? 'selected' : '' ?>><?= t('payidx_filter_devis', 'Prestations sur devis') ?></option>
            </select>
        </form>

        <?php if (empty($paiements)): ?>
            <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
                <p class="text-base-content/70"><?= t('payidx_empty', 'Aucun paiement pour le moment.') ?></p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($paiements as $paiement): ?>
                    <?php
                        $st                  = $paiement['statut'] ?? '';
                        $statutPaye          = in_array($st, ['paye', 'payé', 'success', '1', 'completed']);
                        $statutRembourse     = $st === 'rembourse';
                        $rembEnCours         = !empty($paiement['remboursement_en_cours']) || in_array($st, ['en_attente_remboursement', 'remboursement_en_attente', 'en_attente']);
                        $remboursable        = !empty($paiement['remboursable']);
                        $sourceLabel         = $paiement['source_label'] ?? '';
                    ?>
                    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-2xl font-semibold"><?= htmlspecialchars(formatPrix($paiement['montant'] ?? 0)) ?></h2>
                                <div class="text-sm text-base-content/60"><?= formatDate($paiement['date'] ?? '') ?></div>
                                <?php if ($sourceLabel !== ''): ?>
                                    <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-base-200 text-base-content/70">
                                        <?= htmlspecialchars($sourceLabel) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($statutRembourse): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-check-circle mr-1"></i> <?= t('payidx_status_refunded', 'Remboursé') ?>
                                </span>
                            <?php elseif ($rembEnCours): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-clock mr-1"></i> <?= t('payidx_status_refund_pending', 'Remboursement en attente') ?>
                                </span>
                            <?php elseif ($statutPaye): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <?= t('payidx_status_paid', 'Payé') ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <?= t('payidx_status_unpaid', 'À payer') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!$statutPaye && !$statutRembourse && !$rembEnCours): ?>
                            <a href="/payer"
                                class="inline-block bg-black text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-neutral-800 transition">
                                <?= t('payidx_pay_now', 'Régler maintenant') ?>
                            </a>
                        <?php else: ?>
                            <div class="flex flex-wrap items-center gap-4">
                                <?php if (!empty($paiement['id_facture'])): ?>
                                    <a href="/factures/<?= (int)$paiement['id_facture'] ?>/pdf" target="_blank"
                                        class="inline-flex items-center gap-2 text-sm font-medium text-black hover:underline">
                                        <i class="fas fa-file-pdf"></i> <?= t('payidx_download_invoice', 'Télécharger la facture') ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($remboursable): ?>
                                    <form method="POST" action="/remboursements/demande" class="inline"
                                          onsubmit="return ucConfirm(this, '<?= t('payidx_refund_confirm', 'Demander le remboursement de ce paiement ?') ?>')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_paiement" value="<?= (int)($paiement['id'] ?? 0) ?>">
                                        <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium text-red-600 hover:underline">
                                            <i class="fas fa-rotate-left"></i> <?= t('payidx_request_refund', 'Demander un remboursement') ?>
                                        </button>
                                    </form>
                                <?php elseif ($rembEnCours): ?>
                                    <span class="inline-flex items-center gap-2 text-sm text-orange-600">
                                        <i class="fas fa-hourglass-half"></i> <?= t('payidx_refund_processing', 'Votre demande est en cours de traitement') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</section>
