<div class="mb-6">
    <a href="/admin/utilisateurs/<?= (int)$id_utilisateur ?>" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back', 'Retour') ?>
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-6"><?= t('adm_users_message_title', 'Envoyer un message') ?></h3>
    <?php if (!empty($error)): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="/admin/utilisateurs/<?= (int)$id_utilisateur ?>/message/envoyer" class="space-y-4">
        <?= csrf_field() ?>
        <textarea name="contenu" required maxlength="1000" rows="5" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="<?= t('adm_users_message_ph', 'Votre message...') ?>"></textarea>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-paper-plane mr-2"></i><?= t('adm_users_message_submit', 'Envoyer') ?>
        </button>
    </form>
</div>
