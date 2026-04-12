<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Publier un conseil</h2>
        <p class="text-gray-600">L'article sera soumis à validation admin avant publication</p>
    </div>
    <a href="/salarie/dashboard" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
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
    <form method="POST" action="/salarie/conseils/store">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Titre <span class="text-red-500">*</span></label>
                <input type="text" name="titre" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Catégorie</label>
                <select name="categorie" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none">
                    <option value="upcycling">Upcycling</option>
                    <option value="tri">Tri & Recyclage</option>
                    <option value="bricolage">Bricolage</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Contenu <span class="text-red-500">*</span></label>
                <textarea name="contenu" rows="8" required
                    class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none"
                    placeholder="Rédigez votre article ici..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">URL de l'image (optionnel)</label>
                <input type="url" name="image_url" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-400 outline-none" placeholder="https://...">
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-4"><i class="fas fa-info-circle mr-1"></i>L'article sera en statut <strong>en_attente</strong> jusqu'à validation par l'administrateur.</p>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded-lg hover:bg-yellow-600 transition">
                <i class="fas fa-paper-plane mr-2"></i>Soumettre
            </button>
            <a href="/salarie/dashboard" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">Annuler</a>
        </div>
    </form>
</div>