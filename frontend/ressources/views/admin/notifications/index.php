<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Notifications</h2>
        <p class="text-gray-600">Envoi de notifications aux utilisateurs</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Envoyer une notification</h3>
        <form method="POST" action="/admin/notifications/store" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Contenu</label>
                <textarea name="contenu" rows="4" placeholder="Votre message..."
                    class="w-full border rounded-lg px-4 py-2 text-sm" required></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">ID Utilisateur (0 = tous)</label>
                <input type="number" name="id_utilisateurs" value="0"
                    class="w-full border rounded-lg px-4 py-2 text-sm">
            </div>
            <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 w-full">
                <i class="fas fa-paper-plane mr-2"></i>Envoyer
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Dernières notifications</h3>
        <?php if (empty($notifications)): ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-bell-slash text-3xl mb-2"></i>
                <p>Aucune notification envoyée.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($notifications as $n): ?>
                <div class="border rounded-lg p-3 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium"><?= htmlspecialchars($n['titre'] ?? 'Notification') ?></p>
                        <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($n['message'] ?? $n['contenu'] ?? '') ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= $n['date'] ?? $n['date_envoi'] ?? '' ?></p>
                    </div>
                    <a href="/admin/notifications/<?= $n['id'] ?>/delete"
                       onclick="return confirm('Supprimer ?')"
                       class="text-red-500 hover:text-red-700 ml-2">
                        <i class="fas fa-trash text-xs"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>