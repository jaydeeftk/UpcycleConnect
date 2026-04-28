<div class="mb-6">
    <a href="/admin/evenements" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white dark:bg-slate-800 rounded-xl shadow p-6">
    <h3 class="text-lg font-bold mb-6">Créer un événement</h3>
    <form method="POST" action="/admin/evenements/store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Titre *</label>
            <input type="text" name="titre" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">
                Image <span class="text-gray-400 font-normal">— URL d'une image (ex: Unsplash)</span>
            </label>
            <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            <div id="img-preview-container" class="mt-2 hidden">
                <img id="img-preview" src="" alt="Aperçu"
                     class="w-full h-40 object-cover rounded-lg border border-gray-200">
            </div>
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
            <div>
                <label class="block text-sm font-medium mb-1">Prix (€) <span class="text-gray-400 font-normal">— 0 = gratuit</span></label>
                <input type="number" step="0.01" min="0" name="prix" value="0" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Capacité</label>
                <input type="number" name="capacite" value="50" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-calendar-plus mr-2"></i>Créer l'événement
        </button>
    </form>
</div>

<script>
(function() {
    const input = document.querySelector('input[name="image_url"]');
    const preview = document.getElementById('img-preview');
    const container = document.getElementById('img-preview-container');
    let timer;
    input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const url = input.value.trim();
            if (!url) { container.classList.add('hidden'); return; }
            preview.src = url;
            preview.onload = () => container.classList.remove('hidden');
            preview.onerror = () => container.classList.add('hidden');
        }, 600);
    });
})();
</script>