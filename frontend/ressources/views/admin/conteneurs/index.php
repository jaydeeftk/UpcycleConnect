<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Conteneurs de Collecte</h2>
        <p class="text-slate-500">Supervision du réseau de box UpcycleConnect</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors shadow-sm font-medium">
        <i class="fas fa-plus mr-2"></i>Nouveau Conteneur
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($conteneurs)) { ?>
        <div class="col-span-full bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
            <p class="text-slate-400 italic">Aucun conteneur n'est actuellement déployé.</p>
        </div>
    <?php } else { ?>
        <?php foreach ($conteneurs as $box) {
            $fillRate = $box['fill_rate'] ?? 0;
            $statusColor = $box['statut'] === 'disponible' ? 'emerald' : ($box['statut'] === 'maintenance' ? 'amber' : 'rose');
        ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Box #<?= htmlspecialchars($box['id']) ?></h3>
                    <p class="text-sm text-slate-500"><i class="fas fa-map-marker-alt text-slate-400 mr-1"></i><?= htmlspecialchars($box['localisation']) ?></p>
                </div>
                <span class="px-2 py-1 bg-<?= $statusColor ?>-50 text-<?= $statusColor ?>-600 border border-<?= $statusColor ?>-200 rounded text-xs font-bold uppercase">
                    <?= htmlspecialchars($box['statut']) ?>
                </span>
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-slate-500">Taux de remplissage</span>
                    <span class="text-slate-700"><?= $fillRate ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $fillRate ?>%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1 text-right">Capacité max : <?= htmlspecialchars($box['capacite']) ?> kg</p>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-end">
                <a href="/admin/conteneurs/<?= $box['id'] ?>/delete" 
                   onclick="return confirm('Supprimer définitivement ce conteneur ?')" 
                   class="text-rose-500 hover:text-rose-700 text-sm font-medium transition-colors">
                    <i class="fas fa-trash mr-1"></i>Retirer
                </a>
            </div>
        </div>
        <?php } ?>
    <?php } ?>
</div>

<div id="addModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Ajouter un conteneur</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="/admin/conteneurs/store" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Localisation (Adresse ou Ville)</label>
                    <input type="text" name="localisation" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Capacité maximale (kg)</label>
                    <input type="number" name="capacite" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Statut initial</label>
                    <select name="statut" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="disponible">Disponible</option>
                        <option value="maintenance">En maintenance</option>
                        <option value="plein">Plein</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg">Annuler</button>
                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-600 shadow-sm">Créer</button>
            </div>
        </form>
    </div>
</div>