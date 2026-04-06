<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Catégories</h2>
        <p class="text-gray-600">Gérez les catégories de prestations</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="mb-6 bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-4">Ajouter une catégorie</h3>
    <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/categories/store" class="flex gap-4">
        <input type="text" name="description" placeholder="Description" required
            class="flex-1 border rounded-lg px-4 py-2">
        <input type="text" name="illustration" placeholder="URL illustration"
            class="flex-1 border rounded-lg px-4 py-2">
        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
            <i class="fas fa-plus mr-2"></i>Ajouter
        </button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Illustration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Aucune catégorie</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td class="px-6 py-4 text-gray-500">#<?= $c['id'] ?></td>
                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($c['description']) ?></td>
                    <td class="px-6 py-4 text-gray-600 text-sm"><?= htmlspecialchars($c['illustration'] ?? '-') ?></td>
                    <td class="px-6 py-4">
                        <a href="/UpcycleConnect-PA2526/frontend/public/admin/categories/<?= $c['id'] ?>/delete"
                            onclick="return confirm('Supprimer cette catégorie ?')"
                            class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>