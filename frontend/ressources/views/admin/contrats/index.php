<div class="mb-6">
    <h2 class="text-2xl font-bold">Contrats</h2>
    <p class="text-gray-600">Gestion des contrats entre utilisateurs et prestataires</p>
</div>

<?php if (empty($contrats)): ?>
    <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        <i class="fas fa-file-contract text-4xl mb-3"></i>
        <p>Aucun contrat enregistré.</p>
    </div>
<?php else: ?>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Référence</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Montant</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Statut</th>
                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($contrats as $c): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm">#<?= $c['id'] ?? '-' ?></td>
                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($c['reference'] ?? '-') ?></td>
                <td class="px-6 py-4 text-sm"><?= number_format($c['montant'] ?? 0, 2) ?> €</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?= $c['statut'] ?? '-' ?></span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500"><?= $c['date'] ?? '-' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>