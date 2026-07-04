<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800"><?= t('adm_conteneurs_title', 'Conteneurs de Collecte') ?></h2>
        <p class="text-slate-500"><?= t('adm_conteneurs_subtitle', 'Supervision du réseau de box UpcycleConnect') ?></p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors shadow-sm font-medium">
        <i class="fas fa-plus mr-2"></i><?= t('adm_conteneurs_new', 'Nouveau Conteneur') ?>
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($conteneurs)) { ?>
        <div class="col-span-full bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
            <p class="text-slate-400 italic"><?= t('adm_conteneurs_empty', 'Aucun conteneur n\'est actuellement déployé.') ?></p>
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
                    <?= formatStatut($box['statut']) ?>
                </span>
            </div>

            <div class="mb-4">
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-slate-500"><?= t('adm_conteneurs_fill_rate', 'Taux de remplissage') ?></span>
                    <span class="text-slate-700"><?= $fillRate ?>%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?= $fillRate ?>%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1 text-right"><?= t('adm_conteneurs_max_capacity_kg', 'Capacité max :') ?> <?= htmlspecialchars($box['capacite']) ?> kg</p>
                <?php if (!empty($box['hauteur']) || !empty($box['largeur']) || !empty($box['longueur'])): ?>
                    <p class="text-xs text-slate-400 mt-1 text-right">
                        <?= t('adm_conteneurs_dimensions', 'Dimensions') ?> :
                        <?= htmlspecialchars((string)($box['hauteur'] ?? 0)) ?> ×
                        <?= htmlspecialchars((string)($box['largeur'] ?? 0)) ?> ×
                        <?= htmlspecialchars((string)($box['longueur'] ?? 0)) ?> cm
                    </p>
                <?php endif; ?>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                <a href="/admin/conteneurs/<?= $box['id'] ?>" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium transition-colors">
                    <i class="fas fa-eye mr-1"></i><?= t('adm_conteneurs_see_deposits', 'Voir les dépôts') ?>
                </a>
                <div class="flex items-center gap-4">
                    <button type="button" onclick='openEditBox(<?= htmlspecialchars(json_encode($box), ENT_QUOTES) ?>)' class="text-slate-500 hover:text-emerald-600 text-sm font-medium transition-colors">
                        <i class="fas fa-pen mr-1"></i><?= t('adm_btn_edit', 'Modifier') ?>
                    </button>
                    <form method="POST" action="/admin/conteneurs/<?= $box['id'] ?>/delete" class="inline"
                       onsubmit="return ucConfirm(this, '<?= t('adm_conteneurs_confirm_delete', 'Supprimer définitivement ce conteneur ?') ?>')">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm font-medium transition-colors">
                            <i class="fas fa-trash mr-1"></i><?= t('adm_conteneurs_remove', 'Retirer') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
    <?php } ?>
</div>

<div id="addModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800"><?= t('adm_conteneurs_add_title', 'Ajouter un conteneur') ?></h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="/admin/conteneurs/store" class="p-6">
        <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_localisation', 'Localisation (Adresse ou Ville)') ?></label>
                    <input type="text" name="localisation" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_capacity_max', 'Capacité maximale (kg)') ?></label>
                    <input type="number" name="capacite" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_status_init', 'Statut initial') ?></label>
                    <select name="statut" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="disponible"><?= t('adm_conteneurs_status_disponible', 'Disponible') ?></option>
                        <option value="maintenance"><?= t('adm_conteneurs_status_maintenance', 'En maintenance') ?></option>
                        <option value="plein"><?= t('adm_conteneurs_status_plein', 'Plein') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_dimensions', 'Dimensions (cm)') ?></label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" name="hauteur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_hauteur', 'Hauteur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="number" name="largeur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_largeur', 'Largeur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="number" name="longueur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_longueur', 'Longueur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg"><?= t('adm_btn_cancel', 'Annuler') ?></button>
                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-600 shadow-sm"><?= t('adm_btn_create', 'Créer') ?></button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800"><?= t('adm_conteneurs_edit_title', 'Modifier le conteneur') ?></h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form id="editForm" method="POST" class="p-6">
        <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_localisation', 'Localisation (Adresse ou Ville)') ?></label>
                    <input type="text" name="localisation" id="edit-localisation" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_capacity_max', 'Capacité maximale (kg)') ?></label>
                    <input type="number" name="capacite" id="edit-capacite" min="1" required class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_status', 'Statut') ?></label>
                    <select name="statut" id="edit-statut" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="disponible"><?= t('adm_conteneurs_status_disponible', 'Disponible') ?></option>
                        <option value="maintenance"><?= t('adm_conteneurs_status_maintenance', 'En maintenance') ?></option>
                        <option value="plein"><?= t('adm_conteneurs_status_plein', 'Plein') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1"><?= t('adm_conteneurs_label_dimensions', 'Dimensions (cm)') ?></label>
                    <div class="grid grid-cols-3 gap-2">
                        <input type="number" name="hauteur" id="edit-hauteur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_hauteur', 'Hauteur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="number" name="largeur" id="edit-largeur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_largeur', 'Largeur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <input type="number" name="longueur" id="edit-longueur" min="0" step="0.1" placeholder="<?= t('adm_conteneurs_longueur', 'Longueur') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg"><?= t('adm_btn_cancel', 'Annuler') ?></button>
                <button type="submit" class="bg-emerald-500 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-600 shadow-sm"><?= t('adm_btn_save', 'Enregistrer') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditBox(box) {
    document.getElementById('editForm').action = '/admin/conteneurs/' + box.id + '/update';
    document.getElementById('edit-localisation').value = box.localisation || '';
    document.getElementById('edit-capacite').value = box.capacite || '';
    document.getElementById('edit-statut').value = box.statut || 'disponible';
    document.getElementById('edit-hauteur').value = box.hauteur || '';
    document.getElementById('edit-largeur').value = box.largeur || '';
    document.getElementById('edit-longueur').value = box.longueur || '';
    document.getElementById('editModal').classList.remove('hidden');
}
</script>