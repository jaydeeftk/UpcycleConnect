<div class="mb-6">
    <a href="/admin/utilisateurs" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back_list', 'Retour à la liste') ?>
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-6"><?= t('adm_users_create_title', 'Créer un utilisateur') ?></h3>
    <?php if (!empty($error)): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="/admin/utilisateurs/store" class="space-y-4">
    <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_nom', 'Nom *') ?></label>
                <input type="text" name="nom" required value="<?= htmlspecialchars($old['nom'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_prenom', 'Prénom *') ?></label>
                <input type="text" name="prenom" required value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_email', 'Email *') ?></label>
            <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_password', 'Mot de passe *') ?></label>
            <input type="password" name="mot_de_passe" required class="w-full border rounded-lg px-3 py-2 text-sm">
            <p class="text-xs text-gray-400 mt-1">8 caractères minimum, avec au moins une lettre et un chiffre.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_role', 'Rôle') ?></label>
            <?php $oldRole = $old['role'] ?? 'particulier'; ?>
            <select name="role" id="role-select" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="particulier" <?= $oldRole === 'particulier' ? 'selected' : '' ?>><?= t('adm_role_particulier', 'Particulier') ?></option>
                <option value="professionnel" <?= $oldRole === 'professionnel' ? 'selected' : '' ?>><?= t('adm_role_pro_artisan', 'Professionnel/Artisan') ?></option>
                <option value="salarie" <?= $oldRole === 'salarie' ? 'selected' : '' ?>><?= t('adm_role_salarie', 'Salarié') ?></option>
                <option value="admin" <?= $oldRole === 'admin' ? 'selected' : '' ?>><?= t('adm_role_admin', 'Administrateur') ?></option>
            </select>
        </div>
        <div id="pro-fields" style="<?= $oldRole === 'professionnel' ? '' : 'display:none' ?>" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_siret', 'SIRET *') ?></label>
                <input type="text" name="siret" inputmode="numeric" maxlength="17" value="<?= htmlspecialchars($old['siret'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_nom_entreprise', "Nom de l'entreprise") ?></label>
                <input type="text" name="nom_entreprise" value="<?= htmlspecialchars($old['nom_entreprise'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Laisser vide pour utiliser le nom officiel trouvé via le SIRET.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_users_label_type', 'Type') ?></label>
                <?php $oldType = $old['type'] ?? 'artisan'; ?>
                <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="artisan" <?= $oldType === 'artisan' ? 'selected' : '' ?>><?= t('adm_type_artisan', 'Artisan') ?></option>
                    <option value="professionnel" <?= $oldType === 'professionnel' ? 'selected' : '' ?>><?= t('adm_type_pro', 'Professionnel') ?></option>
                    <option value="entreprise" <?= $oldType === 'entreprise' ? 'selected' : '' ?>><?= t('adm_type_entreprise', 'Entreprise') ?></option>
                </select>
            </div>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-user-plus mr-2"></i><?= t('adm_users_create_submit', 'Créer l\'utilisateur') ?>
        </button>
    </form>
</div>
<script>
document.getElementById('role-select').addEventListener('change', function() {
    document.getElementById('pro-fields').style.display = this.value === 'professionnel' ? '' : 'none';
});
</script>