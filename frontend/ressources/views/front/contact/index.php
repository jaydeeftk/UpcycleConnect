<section class="max-w-2xl mx-auto px-6 lg:px-10 py-16">
    <div class="mb-10">
        <h1 class="text-3xl font-bold mb-2">Nous contacter</h1>
        <p class="text-base-content/60">Envoyez-nous un message, nous vous répondrons rapidement.</p>
    </div>

    <?php if (!empty($_SESSION['contact_success'])): ?>
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span>Votre message a bien été envoyé !</span>
        </div>
        <?php unset($_SESSION['contact_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['contact_error'])): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['contact_error']) ?></span>
        </div>
        <?php unset($_SESSION['contact_error']); ?>
    <?php endif; ?>

    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-8">
        <form method="POST" action="/contact/send" class="space-y-5">
            <div>
                <label class="label"><span class="label-text font-medium">Votre message</span></label>
                <textarea name="contenu" rows="6" required placeholder="Décrivez votre demande..."
                    class="textarea textarea-bordered w-full"></textarea>
            </div>
            <button type="submit" class="btn btn-neutral w-full">
                <i class="fas fa-paper-plane mr-2"></i>Envoyer le message
            </button>
        </form>

        <?php if (!isset($_SESSION['user'])): ?>
            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                <i class="fas fa-info-circle mr-2"></i>
                Vous devez être <a href="/login" class="font-semibold underline">connecté</a> pour envoyer un message.
            </div>
        <?php endif; ?>
    </div>
</section>
