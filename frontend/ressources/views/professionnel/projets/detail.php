<section class="max-w-5xl mx-auto px-6 lg:px-10 py-12 space-y-8">

    <div class="mb-4">
        <a href="/professionnel" class="text-sm text-gray-500 hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> <?= t('proprj_back', 'Retour au tableau de bord') ?>
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Edition projet -->
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($projet['titre'] ?? '') ?></h1>
        <div class="text-sm text-gray-500 mb-6">
            <?= t('proprj_status', 'Statut') ?> : <span class="font-semibold"><?= htmlspecialchars(formatStatut($projet['statut'] ?? '')) ?></span>
            <?php if (!empty($projet['date_debut'])): ?>
                · <?= t('proprj_since', 'Depuis le') ?> <?= htmlspecialchars(formatDate($projet['date_debut'])) ?>
            <?php endif; ?>
        </div>
        <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/update" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_field_title', 'Titre') ?> *</label>
                <input type="text" name="titre" required maxlength="150" value="<?= htmlspecialchars($projet['titre'] ?? '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_field_desc', 'Description') ?></label>
                <textarea name="description" rows="4" maxlength="2000"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($projet['description'] ?? '') ?></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm font-semibold">
                    <i class="fas fa-save mr-2"></i><?= t('proprj_save', 'Enregistrer les modifications') ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Etapes -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-4"><?= t('proprj_steps_title', 'Étapes du projet') ?></h2>

        <?php if (empty($etapes)): ?>
            <div class="text-center py-6 text-gray-400 mb-6">
                <i class="fas fa-images text-3xl mb-2 block"></i>
                <p class="text-sm"><?= t('proprj_steps_empty', 'Aucune étape pour l\'instant. Ajoutez la première avec ses photos avant / après.') ?></p>
            </div>
        <?php else: ?>
            <div class="space-y-6 mb-8">
                <?php foreach ($etapes as $etape): ?>
                    <?php
                    $photoAvant = null; $photoApres = null;
                    foreach (($etape['photos'] ?? []) as $ph) {
                        if (($ph['type_photo'] ?? '') === 'avant') $photoAvant = $ph;
                        if (($ph['type_photo'] ?? '') === 'apres') $photoApres = $ph;
                    }
                    ?>
                    <div class="border border-gray-200 rounded-xl p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold"><?= htmlspecialchars($etape['nom'] ?? '—') ?></h3>
                                <?php if (!empty($etape['description'])): ?>
                                    <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($etape['description']) ?></p>
                                <?php endif; ?>
                            </div>
                            <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/etapes/<?= (int)$etape['id'] ?>/delete"
                                  onsubmit="return ucConfirm(this, '<?= t('proprj_step_confirm_delete', 'Supprimer cette étape ?') ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide"><?= t('proprj_before', 'Avant') ?></div>
                                <?php if ($photoAvant): ?>
                                    <img src="<?= htmlspecialchars($photoAvant['url']) ?>" alt="Avant" class="w-full aspect-video object-cover rounded-lg">
                                <?php else: ?>
                                    <div class="w-full aspect-video bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs"><?= t('proprj_no_photo', 'Pas de photo') ?></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1 uppercase tracking-wide"><?= t('proprj_after', 'Après') ?></div>
                                <?php if ($photoApres): ?>
                                    <img src="<?= htmlspecialchars($photoApres['url']) ?>" alt="Après" class="w-full aspect-video object-cover rounded-lg">
                                <?php else: ?>
                                    <div class="w-full aspect-video bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs"><?= t('proprj_no_photo', 'Pas de photo') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form ajout étape -->
        <form method="POST" action="/professionnel/projets/<?= (int)$projet['id'] ?>/etapes" enctype="multipart/form-data" class="space-y-4 border-t border-gray-200 pt-6">
            <?= csrf_field() ?>
            <h3 class="font-semibold text-sm uppercase tracking-wide text-gray-500"><?= t('proprj_step_add', 'Ajouter une étape') ?></h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_name', 'Nom') ?> *</label>
                <input type="text" name="nom" required maxlength="100"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_desc', 'Description') ?></label>
                <textarea name="description" rows="2" maxlength="255"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_photo_before', 'Photo « avant »') ?></label>
                    <input type="file" name="photo_avant" accept="image/jpeg,image/png,image/webp,image/gif"
                           class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('proprj_step_photo_after', 'Photo « après »') ?></label>
                    <input type="file" name="photo_apres" accept="image/jpeg,image/png,image/webp,image/gif"
                           class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 text-sm font-semibold">
                    <i class="fas fa-plus mr-2"></i><?= t('proprj_step_add_btn', 'Ajouter l\'étape') ?>
                </button>
            </div>
        </form>
    </div>

</section>
