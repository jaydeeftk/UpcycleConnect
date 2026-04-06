<div class="mb-6">
    <h2 class="text-2xl font-bold">Factures</h2>
    <p class="text-gray-600">Suivi financier de la plateforme</p>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($factures)): ?>
                <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune facture</td></tr>
            <?php else: ?>
                <?php foreach ($factures as $f): ?>
                <tr>
                    <td class="px-6 py-4 font-mono text-sm"><?= htmlspecialchars($f['numero']) ?></td>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?></td>
                    <td class="px-6 py-4 font-bold"><?= number_format($f['montant_ttc'], 2) ?>€</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm"><?= htmlspecialchars($f['type']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($f['date_emission']) ?></td>
                    <td class="px-6 py-4">
                        <?php $sc = $f['statut'] === 'payé' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>
                        <span class="px-3 py-1 rounded-full text-sm <?= $sc ?>"><?= htmlspecialchars($f['statut']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>