<section class="min-h-[60vh] flex items-center justify-center px-6 py-16">
    <div class="text-center max-w-md">
        <div class="text-6xl mb-4">🔒</div>
        <h1 class="text-2xl font-bold mb-2"><?= t('err419_title', 'Session expirée') ?></h1>
        <p class="text-base-content/60 mb-6">
            <?= t('err419_text', 'Votre session a expiré ou le formulaire n\'est plus valide (protection anti-CSRF). Revenez en arrière, rechargez la page et réessayez.') ?>
        </p>
        <a href="/" class="btn btn-neutral"><?= t('err419_home', 'Retour à l\'accueil') ?></a>
    </div>
</section>
