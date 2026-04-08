<section class="max-w-3xl mx-auto px-6 lg:px-10 py-16">

    <a href="/conseils" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-8">
        <i class="fas fa-arrow-left"></i> Retour aux conseils
    </a>

    <div class="bg-base-100 rounded-2xl shadow-sm p-8">

        <div class="flex items-center gap-2 mb-4">
            <span class="badge badge-ghost"><?= htmlspecialchars($conseil['categorie'] ?? '') ?></span>
        </div>

        <h1 class="text-3xl font-bold mb-6"><?= htmlspecialchars($conseil['titre'] ?? '') ?></h1>

        <?php if (!empty($conseil['tags'])): ?>
            <div class="flex flex-wrap gap-2 mb-6">
                <?php foreach (explode(',', $conseil['tags']) as $tag): ?>
                    <?php if (trim($tag)): ?>
                        <span class="badge badge-outline badge-sm">#<?= htmlspecialchars(trim($tag)) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="prose max-w-none text-base-content/80 leading-relaxed mb-8">
            <?= nl2br(htmlspecialchars($conseil['contenu'] ?? '')) ?>
        </div>

        <div class="flex items-center gap-3 pt-6 border-t border-base-300">
            <i class="fas fa-user-circle text-2xl text-base-content/30"></i>
            <div>
                <div class="font-medium"><?= htmlspecialchars($conseil['auteur'] ?? '') ?></div>
                <div class="text-sm text-base-content/50">
                    <?= htmlspecialchars($conseil['role'] ?? '') ?> · <?= htmlspecialchars($conseil['date'] ?? '') ?>
                </div>
            </div>
        </div>
    </div>

</section>
