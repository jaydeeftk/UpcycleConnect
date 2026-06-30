<?php
$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? ($error ?? null);
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold"><?= t('sal_remb_title', 'Remboursements') ?></h2>
    <p class="text-gray-600"><?= t('sal_remb_subtitle', 'Traitez les demandes de remboursement des particuliers') ?></p>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!  Remboursement direct  >
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="font-semibold mb-3"><?= t('sal_remb_direct_title', 'Remboursement direct') ?></h3>
    <form method="POST" action="/salaries/remboursements/direct" class="flex flex-wrap items-end gap-3"
          onsubmit="return ucConfirm(this, '<?= t('sal_remb_direct_confirm', 'Rembourser ce paiement ?') ?>')">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_remb_id_paiement', 'N° de paiement') ?></label>
            <input type="number" name="id_paiement" min="1" required
                   class="border border-gray-300 rounded-lg px-3 py-2 w-40 focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('sal_remb_motif', 'Motif') ?></label>
            <input type="text" name="motif" placeholder="<?= t('sal_remb_motif_ph', 'Motif du remboursement') ?>"
                   class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold">
            <i class="fas fa-rotate-left mr-2"></i><?= t('sal_remb_direct_btn', 'Rembourser') ?>
        </button>
    </form>
</div>

<!  Liste des demandes  >
<div class="bg-white rounded-lg shadow overflow-hidden">
    <?php
    $demandesEnAttente = array_filter($demandes ?? [], fn($d) => ($d['statut'] ?? '') === 'en_attente');
    ?>
    <?php if (!empty($demandes) && empty($demandesEnAttente)): ?>
    <div class="px-6 py-4 bg-green-50 border-b border-green-100 flex items-center gap-2 text-green-700 text-sm font-medium">
        <i class="fas fa-check-circle"></i>
        <?= t('sal_remb_none_pending', 'Aucun remboursement en attente — tout est traité.') ?>
    </div>
    <?php endif; ?>
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_remb_col_demandeur', 'Demandeur') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_remb_col_montant', 'Montant') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_remb_col_motif', 'Motif') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_date', 'Date') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_statut', 'Statut') ?></th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= t('sal_col_actions', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($demandes)): ?>
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                    <i class="fas fa-rotate-left text-4xl mb-3 text-gray-300 block"></i>
                    <?= t('sal_remb_empty', 'Aucune demande de remboursement.') ?>
                </td>
            </tr>
            <?php else: foreach ($demandes as $d): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars(trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? '')) ?: '—') ?></td>
                <td class="px-6 py-4 text-sm font-semibold"><?= htmlspecialchars(formatPrix($d['montant'] ?? 0)) ?></td>
                <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['motif'] ?? '—') ?: '—' ?></td>
                <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"><?= formatDate($d['date_demande'] ?? '', true) ?></td>
                <td class="px-6 py-4">
                    <?php
                    $st = $d['statut'] ?? '';
                    $cls = match ($st) {
                        'remboursee' => 'bg-green-100 text-green-700',
                        'en_attente' => 'bg-yellow-100 text-yellow-700',
                        'approuvee'  => 'bg-blue-100 text-blue-700',
                        'refusee'    => 'bg-gray-100 text-gray-600',
                        'echouee'    => 'bg-red-100 text-red-700',
                        default      => 'bg-gray-100 text-gray-600',
                    };
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $cls ?>"><?= htmlspecialchars(formatStatut($st)) ?></span>
                </td>
                <td class="px-6 py-4">
                    <?php if (($d['statut'] ?? '') === 'en_attente'): ?>
                    <div class="flex gap-2">
                        <form method="POST" action="/salaries/remboursements/<?= (int)($d['id'] ?? 0) ?>/approuver" class="inline"
                              onsubmit="return ucConfirm(this, '<?= t('sal_remb_approve_confirm', 'Approuver et rembourser ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-semibold"><?= t('sal_remb_approve', 'Approuver') ?></button>
                        </form>
                        <form method="POST" action="/salaries/remboursements/<?= (int)($d['id'] ?? 0) ?>/refuser" class="inline"
                              onsubmit="return ucConfirm(this, '<?= t('sal_remb_refuse_confirm', 'Refuser cette demande ?') ?>')">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold"><?= t('sal_remb_refuse', 'Refuser') ?></button>
                        </form>
                    </div>
                    <?php else: ?>
                        <span class="text-gray-400 text-sm">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
