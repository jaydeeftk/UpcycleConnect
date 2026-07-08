<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_formations_create_title', 'Créer une formation') ?></h2>
        <p class="text-gray-600"><?= t('adm_formations_create_subtitle', 'Nouvelle formation visible dans le catalogue') ?></p>
    </div>
    <a href="/admin/formations" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i><?= t('adm_btn_back', 'Retour') ?>
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
    <form method="POST" action="/admin/formations/store">
    <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_titre', 'Titre') ?></label>
                <input type="text" name="titre" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_description', 'Description') ?></label>
                <textarea name="description" rows="4" class="w-full border rounded-lg px-4 py-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_price', 'Prix (€)') ?></label>
                <input type="number" step="0.01" name="prix" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_duration', 'Durée (h)') ?></label>
                <input type="number" name="duree" required class="w-full border rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_places', 'Places disponibles') ?></label>
                <input type="number" name="places" class="w-full border rounded-lg px-4 py-2" placeholder="<?= t('adm_formations_places_ph', 'Ex: 15') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_col_status', 'Statut') ?></label>
                <select name="statut" class="w-full border rounded-lg px-4 py-2">
                    <option value="actif"><?= t('adm_status_actif', 'Actif') ?></option>
                    <option value="inactif"><?= t('adm_status_inactif', 'Inactif') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_lieu', 'Lieu') ?></label>
                <input type="text" name="lieu" class="w-full border rounded-lg px-4 py-2" placeholder="<?= t('adm_formations_lieu_ph', 'Ex: Salle A') ?>">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1"><?= t('adm_formations_label_dates', 'Dates *') ?></label>
                <div id="dates-container">
                    <div class="date-row flex gap-2 mb-2">
                        <input type="datetime-local" name="dates[]" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>" class="w-full border rounded-lg px-4 py-2">
                        <button type="button" class="remove-date-btn hidden text-red-500 px-2">&times;</button>
                    </div>
                </div>
                <button type="button" id="add-date-btn" class="text-sm text-emerald-600 hover:underline">
                    <?= t('adm_formations_add_date', '+ Ajouter une autre date') ?>
                </button>
            </div>
        </div>
        <div class="flex gap-4 mt-6">
            <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                <i class="fas fa-plus mr-2"></i><?= t('adm_formations_create_submit', 'Créer la formation') ?>
            </button>
            <a href="/admin/formations" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300"><?= t('adm_btn_cancel', 'Annuler') ?></a>
        </div>
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