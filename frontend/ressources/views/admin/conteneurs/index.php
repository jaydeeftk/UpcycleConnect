<div class="mb-6">
    <h2 class="text-2xl font-bold">Dépôts d'objets</h2>
    <p class="text-gray-600">Demandes de dépôt dans les conteneurs</p>
</div>

<?php if (empty($demandes)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <i class="fas fa-box-open text-4xl mb-3"></i>
        <p>Aucune demande de dépôt en attente.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Objet</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">État</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Destination</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($demandes as $d): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">#<?= $d['id'] ?? '-' ?></td>
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($d['type_objet'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars($d['description'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($d['etat_usure'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm"><?= htmlspecialchars($d['destination'] ?? '-') ?></td>
                <td class="px-6 py-4">
                    <?php
                    $statut = $d['statut'] ?? 'en_attente';
                    $colors = ['en_attente' => 'bg-yellow-100 text-yellow-800', 'accepte' => 'bg-green-100 text-green-800', 'refuse' => 'bg-red-100 text-red-800'];
                    ?>
                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $colors[$statut] ?? 'bg-gray-100 text-gray-800' ?>">
                        <?= ucfirst(str_replace('_', ' ', $statut)) ?>
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex gap-2">
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/conteneurs/<?= $d['id'] ?>/accept"
                           class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                            <i class="fas fa-check"></i>
                        </a>
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/conteneurs/<?= $d['id'] ?>/refuse"
                           class="bg-orange-500 text-white px-3 py-1 rounded text-xs hover:bg-orange-600">
                            <i class="fas fa-times"></i>
                        </a>
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/conteneurs/<?= $d['id'] ?>/delete"
                           onclick="return confirm('Supprimer cette demande ?')"
                           class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>