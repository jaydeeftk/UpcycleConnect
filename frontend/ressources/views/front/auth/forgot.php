<section class="min-h-[70vh] flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-md bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8">
        <a href="/login" class="text-sm text-base-content/50 hover:text-base-content transition">
            <i class="fas fa-arrow-left mr-2"></i><?= t('auth_back_login', 'Retour à la connexion') ?>
        </a>
        <h1 class="text-2xl font-bold mt-4 mb-2"><?= t('auth_forgot_title', 'Mot de passe oublié') ?></h1>
        <p class="text-base-content/60 mb-6"><?= t('auth_forgot_subtitle', 'Saisissez votre adresse email : si un compte existe, vous recevrez un lien pour réinitialiser votre mot de passe.') ?></p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success mb-4 text-sm"><i class="fas fa-check-circle"></i><span><?= htmlspecialchars($success) ?></span></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-4 text-sm"><i class="fas fa-exclamation-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
        <?php endif; ?>

        <form method="POST" action="/mot-de-passe-oublie" class="space-y-4">
        <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium mb-2"><?= t('auth_label_email', 'Adresse email') ?></label>
                <input type="email" name="email" required placeholder="<?= t('auth_placeholder_email', 'votre@email.com') ?>"
                    class="w-full px-4 py-3 rounded-xl border border-base-300 bg-base-100 focus:outline-none focus:ring-2 focus:ring-black" />
            </div>
            <button type="submit" class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                <?= t('auth_forgot_submit', 'Envoyer le lien') ?>
            </button>
        </form>
    </div>
</section>
