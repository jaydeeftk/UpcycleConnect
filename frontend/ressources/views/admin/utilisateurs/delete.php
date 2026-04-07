<div class="max-w-lg mx-auto mt-20">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">

        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-trash text-red-500 text-2xl"></i>
        </div>

        <h1 class="text-2xl font-bold mb-2">Supprimer l'utilisateur</h1>
        <p class="text-gray-500 mb-2">
            Vous êtes sur le point de supprimer
            <span class="font-semibold text-gray-800">
                <?= htmlspecialchars(($utilisateur['prenom'] ?? '') . ' ' . ($utilisateur['nom'] ?? '')) ?>
            </span>
        </p>
        <p class="text-sm text-red-500 mb-8">Cette action est irréversible.</p>

        <div class="flex gap-4 justify-center">
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/<?= $utilisateur['id'] ?>/delete/confirm"
                class="bg-red-500 text-white px-6 py-3 rounded-xl font-medium hover:bg-red-600 transition">
                Oui, supprimer
            </a>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs"
                class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition">
                Annuler
            </a>
        </div>

    </div>
</div>