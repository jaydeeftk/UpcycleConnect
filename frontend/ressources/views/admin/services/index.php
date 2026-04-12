<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Prestations & Services</h2>
        <p class="text-slate-500">Catalogue des services d'upcycling</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors shadow-sm font-medium">
        <i class="fas fa-plus mr-2"></i>Nouveau Service
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-wider">
                <th class="p-4 font-semibold">Titre & Description</th>
                <th class="p-4 font-semibold">Catégorie</th>
                <th class="p-4 font-semibold">Prix</th>
                <th class="p-4 font-semibold">Durée</th>
                <th class="p-4 font-semibold text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (empty($services)) { ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400 italic">Aucun service disponible pour le moment.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($services as $service) { ?>
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="p-4">
                        <p class="font-bold text-slate-800"><?= htmlspecialchars($service['titre'] ?? 'Sans titre') ?></p>
                        <p class="text-sm text-slate-500 truncate max-w-xs"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs font-medium border border-blue-100">
                            <?= htmlspecialchars(ucfirst($service['categorie'] ?? 'Général')) ?>
                        </span>
                    </td>
                    <td class="p-4 font-semibold text-emerald-600">
                        <?= number_format($service['prix'] ?? 0, 2, ',', ' ') ?> €
                    </td>
                    <td class="p-4 text-slate-600">
                        <i class="far fa-clock mr-1 text-slate-400"></i><?= intval($service['duree'] ?? 0) ?> jours
                    </td>
                    <td class="p-4 text-right">
                        <a href="/admin/services/<?= $service['id'] ?>/delete" 
                           onclick="return confirm('Supprimer ce service définitivement ?')" 
                           class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<div id="addModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Ajouter un service</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="/admin/services/store" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Titre de la prestation</label>
                    <input type="text" name="titre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Prix (€)</label>
                        <input type="number" step="0.01" name="prix" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1">Durée (jours)</label>
                        <input type="number" name="duree" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Catégorie</label>
                    <select name="categorie" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="restauration">Restauration de meubles</option>
                        <option value="reparation">Réparation électronique</option>
                        <option value="couture">Couture / Textile</option>
                        <option value="creation">Création sur mesure</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Annuler</button>
                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-600 transition-colors shadow-sm">Créer le service</button>
            </div>
        </form>
    </div>
</div>