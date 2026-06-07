<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold mb-3">Notifications</h1>
            <p class="text-lg text-base-content/70">Retrouvez les informations importantes liées à votre compte.</p>
        </div>
        <?php if (($nonLues ?? 0) > 0): ?>
            <span class="badge badge-error badge-lg text-white font-semibold"><?= (int)$nonLues ?> non lue<?= $nonLues > 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if (!isset($_SESSION['user'])): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-8 text-center">
            <p class="text-base-content/70 mb-4">Vous devez être connecté pour voir vos notifications.</p>
            <a href="/login" class="inline-block bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">Se connecter</a>
        </div>
    <?php elseif (empty($notifications)): ?>
        <div class="bg-base-100 rounded-2xl border border-base-300 p-10 text-center">
            <i class="fas fa-bell-slash text-4xl text-base-content/30 mb-3 block"></i>
            <p class="text-base-content/70">Aucune notification pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($notifications as $notif): $lu = !empty($notif['lu']); ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border <?= $lu ? 'border-base-300 opacity-70' : 'border-emerald-400/60' ?> p-5 flex items-start gap-4">
                    <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0 <?= $lu ? 'bg-base-300' : 'bg-emerald-500' ?>"></span>
                    <div class="flex-1 min-w-0">
                        <p class="<?= $lu ? '' : 'font-semibold' ?> text-base-content"><?= htmlspecialchars($notif['contenu'] ?? '') ?></p>
                        <p class="text-sm text-base-content/50 mt-1"><?= formatDate($notif['date_envoi'] ?? '') ?></p>
                    </div>
                    <?php if (!$lu): ?>
                        <form method="POST" action="/notifications/<?= $notif['id'] ?? '' ?>/lu">
                            <button type="submit" class="btn btn-sm btn-ghost text-emerald-600 gap-2" title="Marquer comme lue">
                                <i class="fas fa-check"></i><span class="hidden sm:inline">Marquer comme lue</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>
