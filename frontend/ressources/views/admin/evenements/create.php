<div class="mb-6">
    <a href="/UpcycleConnect-PA2526/frontend/public/admin/evenements" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-6">Créer un événement</h3>
    <form method="POST" action="/UpcycleConnect-PA2526/frontend/public/admin/evenements/store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Titre *</label>
            <input type="text" name="titre" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Lieu</label>
                <input type="text" name="lieu" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date</label>
                <input type="datetime-local" name="date_evenement" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-calendar-plus mr-2"></i>Créer l'événement
        </button>
    </form>
</div>