<div class="mb-6">
    <h2 class="text-2xl font-bold">Créer un événement</h2>
    <p class="text-gray-600">Remplissez les informations de l'événement</p>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/evenements/store">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                <input type="text" name="titre" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lieu</label>
                <input type="text" name="lieu" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="datetime-local" name="date" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Capacité</label>
                <input type="number" name="capacite" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="4" class="w-full border rounded-lg px-4 py-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="statut" class="w-full border rounded-lg px-4 py-2">
                    <option value="à venir">À venir</option>
                    <option value="en cours">En cours</option>
                    <option value="terminé">Terminé</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ID Salarié</label>
                <input type="number" name="id_salaries" value="1" required class="w-full border rounded-lg px-4 py-2">
            </div>
        </div>
        <div class="mt-6 flex gap-4">
            <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                <i class="fas fa-save mr-2"></i>Créer l'événement
            </button>
            <a href="/UpcycleConnect-PA2526/frontend/public/admin/evenements"
                class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300">
                Annuler
            </a>
        </div>
    </form>
</div>