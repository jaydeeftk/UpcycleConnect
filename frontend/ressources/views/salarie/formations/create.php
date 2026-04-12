<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Créer une formation</h2>
        <p class="text-gray-600">La formation sera soumise à validation admin avant publication</p>
    </div>
    <a href="/salarie" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Retour
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="/salarie/formations/store">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="titre" required
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="4"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Prix (€) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="prix" required
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Durée (h) <span class="text-red-500">*</span></label>
                <input type="number" name="duree" required
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date & heure</label>
                <input type="datetime-local" name="date_formation"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Places totales</label>
                <input type="number" name="places_total" value="20"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Lieu</label>
                <input type="text" name="localisation"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none"
                    placeholder="Ex: Salle A, Atelier Upcycle">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Catégorie</label>
                <input type="text" name="categorie"
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none"
                    placeholder="Ex: Upcycling, Couture">
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-4">
            <i class="fas fa-info-circle mr-1"></i>
            Statut automatique : <strong>en_attente</strong> — visible après validation admin.
        </p>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                <i class="fas fa-paper-plane mr-2"></i>Soumettre
            </button>
            <a href="/salarie" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</a>
        </div>
    </form>
</div>
