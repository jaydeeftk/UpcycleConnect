<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Notifications</h2>
        <p class="text-gray-600">Envoi de notifications aux utilisateurs</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold mb-4">Envoyer une notification</h3>
        <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/notifications/send" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Destinataires</label>
                <select name="cible" class="w-full border rounded-lg px-4 py-2">
                    <option value="all">Tous les utilisateurs</option>
                    <option value="particuliers">Particuliers uniquement</option>
                    <option value="professionnels">Professionnels uniquement</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Titre</label>
                <input type="text" name="titre" placeholder="Titre de la notification"
                    class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Message</label>
                <textarea name="message" rows="4" placeholder="Votre message..."
                    class="w-full border rounded-lg px-4 py-2"></textarea>
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
                <div class="border rounded-lg p-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-sm"><?= htmlspecialchars($n['titre'] ?? '') ?></span>
                        <span class="text-xs text-gray-400"><?= $n['date'] ?? '' ?></span>
                    </div>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($n['message'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>