<section class="max-w-2xl mx-auto px-4 py-10">
    <a href="/messages" class="link link-hover text-sm text-base-content/50 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> <?= t('msghist_back', 'Retour à mes messages') ?>
    </a>
    <h1 class="text-2xl font-bold mb-6"><?= t('msghist_title', 'Historique de mes tickets') ?></h1>

    <?php if (empty($tickets)): ?>
        <div class="text-center py-16 text-base-content/40">
            <i class="fas fa-inbox text-4xl mb-3 block"></i>
            <p><?= t('msghist_empty', 'Aucun ticket pour le moment.') ?></p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($tickets as $t): ?>
                <?php
                $statutColors = [
                    'en_attente' => 'bg-amber-100 text-amber-800',
                    'en_cours'   => 'bg-blue-100 text-blue-800',
                    'ferme'      => 'bg-base-300 text-base-content/60',
                ];
                $color = $statutColors[$t['statut'] ?? ''] ?? 'bg-base-300 text-base-content/60';
                ?>
                <a href="/messages/historique/<?= (int)($t['id'] ?? 0) ?>"
                   class="flex items-center justify-between bg-base-100 rounded-2xl shadow-sm p-5 hover:shadow-md transition">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold"><?= t('msghist_ticket', 'Ticket') ?> #<?= (int)($t['id'] ?? 0) ?></span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $color ?>"><?= htmlspecialchars(formatStatut($t['statut'] ?? '')) ?></span>
                            <?php if (($t['origine'] ?? 'client') === 'admin'): ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800"><?= t('msghist_badge_admin', "Message de l'équipe") ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($t['dernier_message'])): ?>
                            <div class="text-sm text-base-content/60 truncate"><?= htmlspecialchars($t['dernier_message']) ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-base-content/40 flex-shrink-0 ml-4"><?= htmlspecialchars(formatDate($t['date_creation'] ?? '', true)) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
