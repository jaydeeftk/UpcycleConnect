<section class="min-h-[70vh] flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8">
        <h1 class="text-2xl font-bold mb-2"><?= t('auth_reset_title', 'Nouveau mot de passe') ?></h1>
        <p class="text-base-content/60 mb-6"><?= t('auth_reset_subtitle', 'Choisissez un nouveau mot de passe pour votre compte.') ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-4 text-sm"><i class="fas fa-exclamation-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <?php if (empty($token)): ?>
            <div class="alert alert-warning text-sm"><i class="fas fa-exclamation-triangle"></i><span><?= t('auth_reset_no_token', 'Lien invalide. Veuillez refaire une demande.') ?></span></div>
            <a href="/mot-de-passe-oublie" class="btn btn-neutral btn-sm mt-4"><?= t('auth_forgot_title', 'Mot de passe oublié') ?></a>
        <?php else: ?>
        <form method="POST" action="/reset-password" class="space-y-4">
        <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
            <div>
                <label class="block text-sm font-medium mb-2"><?= t('auth_reset_new_password', 'Nouveau mot de passe') ?></label>
                <input type="password" name="password" required minlength="8" title="<?= t('auth_password_hint', '8 caractères minimum, avec au moins une lettre et un chiffre') ?>" placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
                <p class="text-xs text-base-content/50 mt-1"><?= t('auth_password_hint', '8 caractères minimum, avec au moins une lettre et un chiffre') ?></p>
            </div>
            <button type="submit" class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                <?= t('auth_reset_submit', 'Réinitialiser mon mot de passe') ?>
            </button>
        </form>
        <?php endif; ?>
    </div>
</section>
