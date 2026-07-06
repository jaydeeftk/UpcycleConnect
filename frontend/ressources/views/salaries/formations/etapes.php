<?php
$success = $_SESSION['success'] ?? null;
$error_session = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">
            <?= t('sal_etapes_title', 'Étapes de la formation') ?>
            <?php if ($formation): ?>
                <span class="text-gray-400 font-normal">— <?= htmlspecialchars($formation['titre'] ?? '') ?></span>
            <?php endif; ?>
        </h2>
        <p class="text-gray-600"><?= t('sal_etapes_subtitle', 'Le programme sera visible sur la fiche publique de la formation') ?></p>
    </div>
    <a href="/salaries/formations" class="text-gray-600 hover:text-gray-900">
        <i class="fas fa-arrow-left mr-2"></i><?= t('sal_back', 'Retour') ?>
    </a>
</div>

<?php if ($success): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<?php if ($error_session): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
    <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error_session) ?>
</div>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <?php if (empty($etapes)): ?>
        <div class="px-6 py-10 text-center text-gray-500">
            <i class="fas fa-list-ol text-4xl mb-3 text-gray-300"></i>
            <p><?= t('sal_etapes_empty', "Aucune étape pour le moment.") ?></p>
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-200">
            <?php foreach ($etapes as $etape): ?>
            <li class="px-6 py-4 flex items-start justify-between gap-4">
                <div class="flex gap-4">
                    <span class="w-8 h-8 shrink-0 rounded-full bg-gray-800 text-white flex items-center justify-center text-sm font-bold">
                        <?= (int)($etape['ordre'] ?? 0) ?>
                    </span>
                    <div>
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($etape['titre'] ?? '') ?></p>
                        <?php if (!empty($etape['description'])): ?>
                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($etape['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="POST" action="/salaries/formations/<?= (int)$formation['id'] ?>/etapes/<?= (int)$etape['id'] ?>/delete"
                      onsubmit="return ucConfirm(this, '<?= t('sal_etapes_delete_confirm', 'Supprimer cette étape ?') ?>')">
                    <?= csrf_field() ?>
                    <button type="submit" class="text-red-600 hover:text-red-800" title="<?= t('sal_action_delete', 'Supprimer') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-lg shadow p-6 h-fit">
        <h3 class="text-lg font-bold mb-4"><?= t('sal_etapes_add', 'Ajouter une étape') ?></h3>
        <form method="POST" action="/salaries/formations/<?= (int)($formation['id'] ?? 0) ?>/etapes/store">
        <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= t('sal_field_titre', 'Titre') ?> *</label>
                <input type="text" name="titre" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="<?= t('sal_ph_etape_titre', 'Ex: Introduction aux outils') ?>">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= t('sal_field_description', 'Description') ?></label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= t('sal_field_ordre', 'Ordre') ?></label>
                <input type="number" name="ordre" min="0" value="<?= count($etapes) ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                <i class="fas fa-plus mr-2"></i><?= t('sal_create', 'Ajouter') ?>
            </button>
        </form>
    </div>
</div>
