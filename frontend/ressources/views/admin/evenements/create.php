<div class="mb-6">
    <a href="/admin/evenements" class="text-gray-500 hover:text-black text-sm">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back_list', 'Retour à la liste') ?>
    </a>
</div>

<div class="max-w-2xl mx-auto bg-white dark:bg-slate-800 rounded-xl shadow p-6">
    <h3 class="text-lg font-bold mb-6"><?= t('adm_events_create_title', 'Créer un événement') ?></h3>
    <?php if (!empty($error)): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="/admin/evenements/store" class="space-y-4">
    <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_titre', 'Titre *') ?></label>
            <input type="text" name="titre" required class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_description', 'Description') ?></label>
            <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">
                <?= t('adm_events_label_image', 'Image') ?> <span class="text-gray-400 font-normal"><?= t('adm_events_label_image_hint', '— URL d\'une image (ex: Unsplash)') ?></span>
            </label>
            <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            <div id="img-preview-container" class="mt-2 hidden">
                <img id="img-preview" src="" alt="<?= t('adm_events_image_preview_alt', 'Aperçu') ?>"
                     class="w-full h-40 object-cover rounded-lg border border-gray-200">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_place', 'Lieu') ?></label>
                <input type="text" name="lieu" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_price', 'Prix (€)') ?> <span class="text-gray-400 font-normal"><?= t('adm_events_price_hint', '— 0 = gratuit') ?></span></label>
                <input type="number" step="0.01" min="0" name="prix" value="0" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_capacity', 'Capacité') ?></label>
                <input type="number" name="capacite" value="50" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1"><?= t('adm_events_label_dates', 'Dates *') ?></label>
            <div id="dates-container">
                <div class="date-row flex gap-2 mb-2">
                    <input type="datetime-local" name="dates[]" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <button type="button" class="remove-date-btn hidden text-red-500 px-2">&times;</button>
                </div>
            </div>
            <button type="button" id="add-date-btn" class="text-sm text-emerald-600 hover:underline">
                <?= t('adm_events_add_date', '+ Ajouter une autre date') ?>
            </button>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white px-4 py-3 rounded-lg hover:bg-green-600 font-medium">
            <i class="fas fa-calendar-plus mr-2"></i><?= t('adm_events_create_submit', 'Créer l\'événement') ?>
        </button>
    </form>
</div>

<script>
(function() {
    const container = document.getElementById('dates-container');
    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.date-row');
        rows.forEach(row => row.querySelector('.remove-date-btn').classList.toggle('hidden', rows.length === 1));
    }
    document.getElementById('add-date-btn').addEventListener('click', function() {
        const row = container.querySelector('.date-row').cloneNode(true);
        row.querySelector('input').value = '';
        container.appendChild(row);
        updateRemoveButtons();
    });
    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-date-btn')) {
            e.target.closest('.date-row').remove();
            updateRemoveButtons();
        }
    });
})();
</script>

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