<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100"><?= t('adm_tickets_title', 'Tickets support') ?></h1>
    <p class="text-sm text-slate-500 dark:text-slate-400"><?= t('adm_tickets_subtitle', 'Demandes des particuliers.') ?></p>
</div>

<?php if (empty($tickets)): ?>
    <div class="text-center py-16 text-slate-400">
        <i class="fas fa-headset text-4xl mb-3 block"></i>
        <p><?= t('adm_tickets_empty', 'Aucun ticket pour le moment.') ?></p>
    </div>
<?php else: ?>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left"><?= t('adm_tickets_col_particulier', 'Particulier') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_tickets_col_last', 'Dernier message') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_tickets_col_statut', 'Statut') ?></th>
                    <th class="px-4 py-3 text-left"><?= t('adm_tickets_col_admin', 'Pris par') ?></th>
                    <th class="px-4 py-3 text-right"><?= t('adm_tickets_col_actions', 'Actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php foreach ($tickets as $t): ?>
                    <?php
                    $statutColors = [
                        'en_attente' => 'bg-amber-100 text-amber-800',
                        'en_cours'   => 'bg-blue-100 text-blue-800',
                        'ferme'      => 'bg-slate-100 text-slate-500',
                    ];
                    $color = $statutColors[$t['statut'] ?? ''] ?? 'bg-slate-100 text-slate-500';
                    $estMoi = (int)($t['id_admin_assigne'] ?? 0) === (int)($user_id ?? 0);
                    ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200"><?= htmlspecialchars($t['nom_particulier'] ?? '') ?></td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400 max-w-xs truncate"><?= htmlspecialchars($t['dernier_message'] ?? '') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $color ?>"><?= htmlspecialchars(formatStatut($t['statut'] ?? '')) ?></span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['nom_admin'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-right">
                            <?php if (($t['statut'] ?? '') === 'en_attente'): ?>
                                <form method="POST" action="/admin/tickets/<?= (int)$t['id'] ?>/accepter" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs">
                                        <i class="fas fa-check mr-1"></i><?= t('adm_tickets_accept', 'Accepter') ?>
                                    </button>
                                </form>
                            <?php elseif ($estMoi): ?>
                                <a href="/admin/tickets/<?= (int)$t['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                    <i class="fas fa-comment-dots mr-1"></i><?= t('adm_tickets_open', 'Ouvrir') ?>
                                </a>
                            <?php elseif (($t['statut'] ?? '') === 'en_cours'): ?>
                                <span class="text-slate-400 text-xs"><?= t('adm_tickets_taken', 'Pris en charge') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
