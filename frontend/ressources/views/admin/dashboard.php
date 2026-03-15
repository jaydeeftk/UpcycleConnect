<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
    <div>
        <p class="text-sm text-gray-500">Tableau de bord</p>
        <h1 class="text-4xl font-bold text-gray-900">Bienvenue sur l'espace admin</h1>
    </div>
    <div class="flex items-center gap-3">
        <button class="bg-white border border-gray-200 px-4 py-3 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            Exporter
        </button>
        <button class="bg-gray-900 text-white px-5 py-3 rounded-xl text-sm font-medium hover:bg-gray-800 transition">
            Nouvelle action
        </button>
    </div>
</div>

<section class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Utilisateurs</p>
        <h3 class="text-3xl font-bold mt-3"><?= number_format($stats['total_utilisateurs'] ?? 0) ?></h3>
        <p class="text-sm text-emerald-600 mt-2">Total inscrits</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Annonces</p>
        <h3 class="text-3xl font-bold mt-3"><?= number_format($stats['total_annonces'] ?? 0) ?></h3>
        <p class="text-sm text-emerald-600 mt-2">Total annonces</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Événements</p>
        <h3 class="text-3xl font-bold mt-3"><?= number_format($stats['total_evenements'] ?? 0) ?></h3>
        <p class="text-sm text-gray-500 mt-2">Total événements</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-6">
        <p class="text-sm text-gray-500">Messages</p>
        <h3 class="text-3xl font-bold mt-3"><?= number_format($stats['total_messages'] ?? 0) ?></h3>
        <p class="text-sm text-amber-600 mt-2">Total messages</p>
    </div>
</section>

<section class="grid xl:grid-cols-3 gap-6 mb-8">
    <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold">Activité générale</h3>
                <p class="text-sm text-gray-500 mt-1">Vue d'ensemble de la plateforme</p>
            </div>
            <button class="text-sm text-gray-600 hover:text-black">Voir plus</button>
        </div>
        <div class="h-72 rounded-2xl bg-gray-100 flex items-end gap-4 p-6">
            <div class="flex-1 bg-gray-950 rounded-t-xl h-20"></div>
            <div class="flex-1 bg-gray-900 rounded-t-xl h-36"></div>
            <div class="flex-1 bg-gray-800 rounded-t-xl h-52"></div>
            <div class="flex-1 bg-gray-900 rounded-t-xl h-28"></div>
            <div class="flex-1 bg-gray-950 rounded-t-xl h-44"></div>
            <div class="flex-1 bg-gray-800 rounded-t-xl h-60"></div>
            <div class="flex-1 bg-gray-900 rounded-t-xl h-32"></div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-xl font-bold">Statistiques</h3>
            <p class="text-sm text-gray-500 mt-1">Résumé de l'activité</p>
        </div>
        <div class="space-y-5">
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Utilisateurs inscrits</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_utilisateurs'] ?? 0) ?> comptes</p>
            </div>
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Annonces publiées</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_annonces'] ?? 0) ?> annonces</p>
            </div>
            <div class="border-b border-gray-100 pb-4">
                <p class="font-medium">Événements créés</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_evenements'] ?? 0) ?> événements</p>
            </div>
            <div>
                <p class="font-medium">Messages échangés</p>
                <p class="text-sm text-gray-500 mt-1"><?= number_format($stats['total_messages'] ?? 0) ?> messages</p>
            </div>
        </div>
    </div>
</section>

<section class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold">Prestations récentes</h3>
            <a href="/admin/prestations" class="text-sm text-gray-600 hover:text-black">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-sm text-gray-500 border-b border-gray-100">
                        <th class="pb-3 font-medium">Titre</th>
                        <th class="pb-3 font-medium">Catégorie</th>
                        <th class="pb-3 font-medium">Prix</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (!empty($prestations)): ?>
                        <?php foreach ($prestations as $p): ?>
                        <tr class="border-b border-gray-100">
                            <td class="py-4"><?= htmlspecialchars($p['titre'] ?? '') ?></td>
                            <td class="py-4"><?= htmlspecialchars($p['categorie'] ?? '') ?></td>
                            <td class="py-4"><?= htmlspecialchars($p['prix'] ?? '') ?>€</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="py-4 text-gray-400 text-center">Aucune prestation</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold">Actions rapides</h3>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <a href="/admin/utilisateurs" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Ajouter un utilisateur</h4>
                <p class="text-sm text-gray-500 mt-2">Créer un nouveau compte dans la plateforme.</p>
            </a>
            <a href="/admin/evenements" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Créer un événement</h4>
                <p class="text-sm text-gray-500 mt-2">Planifier un atelier ou une rencontre.</p>
            </a>
            <a href="/admin/categories" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Ajouter une catégorie</h4>
                <p class="text-sm text-gray-500 mt-2">Structurer les prestations proposées.</p>
            </a>
            <a href="/admin/annonces" class="rounded-2xl border border-gray-200 p-5 hover:bg-gray-50 transition">
                <h4 class="font-semibold">Voir les demandes</h4>
                <p class="text-sm text-gray-500 mt-2">Consulter les validations en attente.</p>
            </a>
        </div>
    </div>
</section>