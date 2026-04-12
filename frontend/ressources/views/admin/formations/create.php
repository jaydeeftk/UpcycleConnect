<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Créer une formation</h2>
        <p class="text-gray-600">Nouvelle formation visible dans le catalogue</p>
    </div>
    <a href="/admin/formations" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Retour
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="/admin/formations/store">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Titre</label>
                <input type="text" name="titre" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full border rounded-lg px-4 py-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Prix (€)</label>
                <input type="number" step="0.01" name="prix" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Durée (h)</label>
                <input type="number" name="duree" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date de début</label>
                <input type="datetime-local" name="date_debut" class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Places disponibles</label>
                <input type="number" name="places" class="w-full border rounded-lg px-4 py-2" placeholder="Ex: 15">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Statut</label>
                <select name="statut" class="w-full border rounded-lg px-4 py-2">
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Lieu</label>
                <input type="text" name="lieu" class="w-full border rounded-lg px-4 py-2" placeholder="Ex: Salle A">
            </div>
        </div>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                <i class="fas fa-plus mr-2"></i>Créer la formation
            </button>
            <a href="/admin/formations" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</a>
        </div>
    </form>
</div>