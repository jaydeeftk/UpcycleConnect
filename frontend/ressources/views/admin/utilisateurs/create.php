<div class="mb-6">
    <a href="/admin/utilisateurs" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back_list', 'Retour à la liste') ?>
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-6"><?= t('adm_users_create_title', 'Créer un utilisateur') ?></h3>
    <form method="POST" action="/admin/utilisateurs/store" class="space-y-4">
    <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_nom', 'Nom *') ?></label>
                <input type="text" name="nom" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_prenom', 'Prénom *') ?></label>
                <input type="text" name="prenom" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_email', 'Email *') ?></label>
            <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_password', 'Mot de passe *') ?></label>
            <input type="password" name="mot_de_passe" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_role', 'Rôle') ?></label>
            <select name="role" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="particulier"><?= t('adm_role_particulier', 'Particulier') ?></option>
                <option value="professionnel"><?= t('adm_role_pro_artisan', 'Professionnel/Artisan') ?></option>
            </select>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-user-plus mr-2"></i><?= t('adm_users_create_submit', 'Créer l\'utilisateur') ?>
        </button>
    </form>
</div>