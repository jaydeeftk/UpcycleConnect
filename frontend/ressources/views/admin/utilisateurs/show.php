<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Profil Utilisateur</h2>
        <p class="text-slate-500">Détails et historique d'activité</p>
    </div>
    <a href="/admin/utilisateurs" class="bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors shadow-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i>Retour
    </a>
</div>

<?php if (empty($utilisateur)) { ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">Utilisateur introuvable.</div>
<?php } else { ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col items-center border-b border-slate-100 pb-6 mb-6">
                <div class="w-24 h-24 bg-gradient-to-tr from-emerald-500 to-teal-400 rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-md shadow-emerald-500/20 mb-4">
                    <?= strtoupper(substr($utilisateur['prenom'] ?? 'U', 0, 1)) ?>
                </div>
                <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? '')) ?></h3>
                <p class="text-emerald-600 font-medium"><?= ucfirst(htmlspecialchars($utilisateur['role'] ?? 'Particulier')) ?></p>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Email</p>
                    <p class="text-slate-700"><?= htmlspecialchars($utilisateur['email'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Téléphone</p>
                    <p class="text-slate-700"><?= htmlspecialchars($utilisateur['telephone'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Adresse</p>
                    <p class="text-slate-700"><?= htmlspecialchars($utilisateur['adresse'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Statut d'accès</p>
                    <?php $st = $utilisateur['statut'] ?? ''; ?>
                    <span class="px-3 py-1 bg-<?= $st === 'actif' || $st === 'admin' ? 'emerald' : 'rose' ?>-50 border border-<?= $st === 'actif' || $st === 'admin' ? 'emerald' : 'rose' ?>-200 text-<?= $st === 'actif' || $st === 'admin' ? 'emerald' : 'rose' ?>-700 rounded-md text-sm font-medium">
                        <?= ucfirst(htmlspecialchars($st ?: 'Inconnu')) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h4 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Actions Administratives</h4>

            <form method="POST" action="/admin/utilisateurs/<?= $utilisateur['id'] ?>/role" class="mb-4">
                <label class="block text-xs text-slate-500 font-semibold uppercase mb-1">Rôle du compte</label>
                <div class="flex gap-2">
                    <select name="role" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500 flex-1">
                        <option value="particulier" <?= ($utilisateur['role'] ?? '') === 'particulier' ? 'selected' : '' ?>>Particulier</option>
                        <option value="professionnel" <?= ($utilisateur['role'] ?? '') === 'professionnel' ? 'selected' : '' ?>>Professionnel</option>
                        <option value="salarie" <?= ($utilisateur['role'] ?? '') === 'salarie' ? 'selected' : '' ?>>Salarié</option>
                        <option value="admin" <?= ($utilisateur['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                    <button type="submit" class="bg-slate-800 text-white px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors" title="Valider le rôle">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>

            <form method="POST" action="/admin/utilisateurs/<?= $utilisateur['id'] ?>/statut" class="mb-6">
                <label class="block text-xs text-slate-500 font-semibold uppercase mb-1">Statut d'accès</label>
                <div class="flex gap-2">
                    <select name="statut" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500 flex-1">
                        <option value="actif" <?= ($utilisateur['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= ($utilisateur['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        <option value="suspendu" <?= ($utilisateur['statut'] ?? '') === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                    <button type="submit" class="bg-slate-800 text-white px-3 py-2 rounded-lg hover:bg-slate-700 transition-colors" title="Valider le statut">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </form>

            <a href="/admin/utilisateurs/<?= $utilisateur['id'] ?>/delete"
                class="block text-center bg-rose-50 text-rose-600 border border-rose-200 px-4 py-2 rounded-lg hover:bg-rose-100 transition-colors text-sm font-medium"
                onclick="return confirm('Attention, cette action est irréversible. Supprimer cet utilisateur ?')">
                <i class="fas fa-trash mr-2"></i>Supprimer le compte
            </a>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-bullhorn text-emerald-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Annonces publiées</h3>
            </div>
            <div class="p-0">
                <?php if (empty($utilisateur['historique']['annonces'])) { ?>
                    <p class="p-6 text-slate-400 text-center italic">Aucune annonce publiée à ce jour.</p>
                <?php } else { ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($utilisateur['historique']['annonces'] as $annonce) { ?>
                        <li class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-center group">
                            <div>
                                <p class="font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors"><?= htmlspecialchars($annonce['titre']) ?></p>
                                <p class="text-xs text-slate-400 mt-1"><i class="far fa-clock mr-1"></i><?= htmlspecialchars($annonce['date'] ?? 'Date inconnue') ?></p>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium border border-slate-200">
                                <?= ucfirst(htmlspecialchars($annonce['statut'])) ?>
                            </span>
                        </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-box-open text-blue-600"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Dépôts en conteneur</h3>
            </div>
            <div class="p-0">
                <?php if (empty($utilisateur['historique']['demandes'])) { ?>
                    <p class="p-6 text-slate-400 text-center italic">Aucun dépôt en conteneur effectué.</p>
                <?php } else { ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($utilisateur['historique']['demandes'] as $demande) { ?>
                        <li class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-center group">
                            <div>
                                <p class="font-semibold text-slate-800 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($demande['type_objet']) ?></p>
                                <p class="text-xs text-slate-400 mt-1"><i class="far fa-clock mr-1"></i><?= htmlspecialchars($demande['date'] ?? 'Date inconnue') ?></p>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium border border-slate-200">
                                <?= ucfirst(htmlspecialchars($demande['statut'])) ?>
                            </span>
                        </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-calendar-check text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Planning personnel</h3>
                </div>
                <span class="text-xs text-slate-400"><?= count($utilisateur['planning'] ?? []) ?> entrée(s)</span>
            </div>
            <div class="p-0">
                <?php if (empty($utilisateur['planning'])): ?>
                    <p class="p-6 text-slate-400 text-center italic">Aucun événement ou formation inscrit.</p>
                <?php else: ?>
                    <ul class="divide-y divide-slate-100">
                        <?php foreach ($utilisateur['planning'] as $item): ?>
                        <li class="p-4 hover:bg-slate-50 transition-colors flex justify-between items-start gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <span class="mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase shrink-0 <?= ($item['type'] ?? '') === 'formation' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                    <?= ($item['type'] ?? '') === 'formation' ? 'Formation' : 'Événement' ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate"><?= htmlspecialchars($item['titre'] ?? '') ?></p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        <i class="fas fa-calendar-alt mr-1"></i><?= htmlspecialchars(substr($item['date'] ?? '', 0, 10)) ?>
                                        <?php if (!empty($item['lieu'])): ?>
                                        &nbsp;·&nbsp;<i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($item['lieu']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-medium border border-slate-200">
                                    <?= ucfirst(htmlspecialchars($item['statut'] ?? '—')) ?>
                                </span>
                                <button onclick="removePlanning(<?= $utilisateur['id'] ?>, '<?= $item['type'] ?? 'evenement' ?>', <?= $item['id'] ?>)"
                                    class="w-7 h-7 flex items-center justify-center bg-rose-50 text-rose-500 rounded-lg hover:bg-rose-500 hover:text-white transition-colors" title="Désinscrire">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<script>
function removePlanning(userId, type, itemId) {
    if (!confirm('Désinscrire cet utilisateur de cet élément ?')) return;
    fetch('/api/admin/utilisateurs/' + userId + '/planning/' + type + '/' + itemId, {
        method: 'DELETE',
        headers: {'Authorization': 'Bearer <?= $_SESSION['user']['token'] ?? '' ?>'}
    }).then(r => r.json()).then(data => {
        if (data.data || data.success) {
            window.location.reload();
        } else {
            alert('Erreur : ' + (data.error || 'Impossible de désinscrire'));
        }
    }).catch(() => alert('Erreur réseau'));
}
</script>
<?php } ?>