<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10">
        <a href="/score" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-6">
            <i class="fas fa-arrow-left"></i> <?= t('classement_back', 'Retour à mon score') ?>
        </a>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-trophy text-emerald-600"></i>
            </div>
            <span class="text-sm font-medium text-emerald-600 uppercase tracking-wide"><?= t('classement_eyebrow', 'Communauté') ?></span>
        </div>
        <h1 class="text-3xl font-bold"><?= t('classement_title', 'Classement Upcycling Score') ?></h1>
        <p class="text-base-content/60 mt-2"><?= t('classement_subtitle', 'Les particuliers les plus actifs de la communauté UpcycleConnect.') ?></p>
    </div>

    <?php if (($mon_rang ?? 0) > 0): ?>
        <div class="bg-emerald-500 text-white rounded-2xl p-6 mb-8 flex items-center justify-between">
            <div>
                <p class="text-sm text-white/80"><?= t('classement_your_rank', 'Votre position') ?></p>
                <p class="text-2xl font-bold">#<?= (int)$mon_rang ?> <span class="text-white/70 text-base font-normal">/ <?= (int)($total ?? 0) ?></span></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-white/80"><?= t('classement_your_score', 'Votre score') ?></p>
                <p class="text-2xl font-bold"><?= (int)$mon_score ?> pts</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($top)): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-12 text-center text-base-content/60">
            <?= t('classement_empty', 'Aucun classement disponible pour le moment.') ?>
        </div>
    <?php else: ?>
        <div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">
            <?php foreach ($top as $entree): ?>
                <?php
                $estMoi = $entree['moi_meme'] ?? false;
                $rang = (int)($entree['rang'] ?? 0);
                $medaille = ['🥇', '🥈', '🥉'][$rang - 1] ?? null;
                ?>
                <div class="flex items-center gap-4 px-6 py-4 border-b border-base-300 last:border-0 <?= $estMoi ? 'bg-emerald-50' : '' ?>">
                    <div class="w-10 text-center font-bold text-lg <?= $rang <= 3 ? '' : 'text-base-content/40' ?>">
                        <?= $medaille ?: '#' . $rang ?>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold"><?= htmlspecialchars($entree['nom'] ?? '') ?><?= $estMoi ? ' <span class="text-emerald-600 text-xs font-normal">(' . t('classement_you', 'vous') . ')</span>' : '' ?></div>
                        <div class="text-xs text-base-content/50"><?= $entree['badge_icon'] ?? '' ?> <?= htmlspecialchars($entree['badge_label'] ?? '') ?></div>
                    </div>
                    <div class="text-right font-bold text-emerald-600"><?= (int)($entree['score'] ?? 0) ?> pts</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>
