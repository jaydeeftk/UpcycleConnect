<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professionnel - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 bg-gray-800 text-white flex flex-col flex-shrink-0">
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-xl font-bold text-green-400">UpcycleConnect</h1>
            <p class="text-xs text-gray-400 mt-1">Espace Professionnel</p>
        </div>
        <nav class="flex-1 p-4">
            <ul class="space-y-1">
                <li>
                    <a href="/professionnel" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-gray-700 text-white">
                        <i class="fas fa-tachometer-alt w-5"></i><span>Tableau de bord</span>
                    </a>
                </li>
                <li>
                    <a href="/professionnel/projets/create" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-project-diagram w-5"></i><span>Nouveau projet</span>
                    </a>
                </li>
                <li>
                    <a href="/annonces" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-bullhorn w-5"></i><span>Annonces</span>
                    </a>
                </li>
                <li>
                    <a href="/catalogue/services" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-tools w-5"></i><span>Services</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <a href="/logout" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                <i class="fas fa-sign-out-alt w-5"></i><span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Tableau de bord</h2>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($profil['nom_entreprise'] ?? ($_SESSION['user']['prenom'] ?? '')) ?></p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($_SESSION['user']['prenom'] ?? 'P', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? '')) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($profil['type'] ?? 'Professionnel') ?></p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-project-diagram text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Projets</p>
                        <p class="text-lg font-bold"><?= count($projets ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heart text-2xl text-pink-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Favoris</p>
                        <p class="text-lg font-bold"><?= count($favoris ?? []) ?></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-contract text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contrats</p>
                        <p class="text-lg font-bold"><?= count($contrats ?? []) ?></p>
                    </div>
                </div>
            </div>

            <!-- Projets -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Mes projets upcycling</h3>
                    <a href="/professionnel/projets/create" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition text-sm">
                        <i class="fas fa-plus mr-2"></i>Nouveau projet
                    </a>
                </div>
                <?php if (empty($projets)): ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-project-diagram text-4xl mb-3 block"></i>
                        <p>Aucun projet pour l'instant.</p>
                        <a href="/professionnel/projets/create" class="text-blue-500 hover:underline text-sm mt-2 inline-block">Créer votre premier projet</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($projets as $projet): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($projet['titre'] ?? '') ?></h4>
                                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars(substr($projet['description'] ?? '', 0, 80)) ?><?= strlen($projet['description'] ?? '') > 80 ? '...' : '' ?></p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <?php
                                            $statutColors = ['en_cours' => 'bg-blue-100 text-blue-700', 'termine' => 'bg-green-100 text-green-700', 'pause' => 'bg-yellow-100 text-yellow-700'];
                                            $sc = $statutColors[$projet['statut'] ?? ''] ?? 'bg-gray-100 text-gray-600';
                                            ?>
                                            <span class="text-xs px-2 py-1 rounded-full <?= $sc ?>"><?= htmlspecialchars($projet['statut'] ?? '') ?></span>
                                            <span class="text-xs text-gray-400"><?= $projet['nb_etapes'] ?? 0 ?> étape(s)</span>
                                        </div>
                                    </div>
                                    <form method="POST" action="/professionnel/projets/<?= $projet['id'] ?>/delete" onsubmit="return confirm('Supprimer ce projet ?')">
                                        <button type="submit" class="text-red-400 hover:text-red-600 ml-2">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Favoris -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Mes annonces favorites</h3>
                <?php if (empty($favoris)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-heart text-3xl mb-2 block"></i>
                        <p class="text-sm">Aucune annonce en favoris.</p>
                        <a href="/annonces" class="text-blue-500 hover:underline text-sm mt-1 inline-block">Parcourir les annonces</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($favoris as $favori): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <a href="/annonces/<?= $favori['id'] ?>" class="font-medium text-gray-800 hover:text-blue-600"><?= htmlspecialchars($favori['titre'] ?? '') ?></a>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($favori['date'] ?? '') ?></p>
                                </div>
                                <form method="POST" action="/professionnel/favoris/<?= $favori['id'] ?>/remove">
                                    <button type="submit" class="text-pink-400 hover:text-pink-600">
                                        <i class="fas fa-heart-broken text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contrats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold mb-4">Mes contrats</h3>
                <?php if (empty($contrats)): ?>
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-file-contract text-3xl mb-2 block"></i>
                        <p class="text-sm">Aucun contrat pour l'instant.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b">
                                    <th class="pb-3">Type</th>
                                    <th class="pb-3">Statut</th>
                                    <th class="pb-3">Début</th>
                                    <th class="pb-3">Fin</th>
                                    <th class="pb-3">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrats as $contrat): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3"><?= htmlspecialchars($contrat['type'] ?? '') ?></td>
                                        <td class="py-3"><span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700"><?= htmlspecialchars($contrat['statut'] ?? '') ?></span></td>
                                        <td class="py-3"><?= htmlspecialchars($contrat['date_debut'] ?? '') ?></td>
                                        <td class="py-3"><?= htmlspecialchars($contrat['date_fin'] ?? '') ?></td>
                                        <td class="py-3 font-medium"><?= number_format($contrat['montant'] ?? 0, 2) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>
</body>
</html>
