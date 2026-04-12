<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Modifier le service</h2>
        <p class="text-gray-600">Mise à jour du service #<?= $service['id'] ?? '' ?></p>
    </div>
    <a href="/admin/services" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Retour
    </a>
</div>

<div class="bg-white rounded-lg shadow p-6 max-w-lg">
    <form method="POST" action="/admin/services/<?= $service['id'] ?? '' ?>/update">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Titre</label>
                <input type="text" name="titre" required value="<?= htmlspecialchars($service['titre'] ?? '') ?>"
                    class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border rounded-lg px-4 py-2"><?= htmlspecialchars($service['description'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Prix (€)</label>
                    <input type="number" step="0.01" name="prix" required
                        value="<?= htmlspecialchars($service['prix'] ?? '') ?>"
                        class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Durée (h)</label>
                    <input type="number" name="duree" required
                        value="<?= htmlspecialchars($service['duree'] ?? '') ?>"
                        class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Catégorie</label>
                    <input type="text" name="categorie"
                        value="<?= htmlspecialchars($service['categorie'] ?? '') ?>"
                        class="w-full border rounded-lg px-4 py-2">
                </div>
            </div>
        </div>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">Enregistrer</button>
            <a href="/admin/services" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</a>
        </div>
    </form>
</div>