<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('pro_pub_page_title', 'Campagnes publicitaires') ?> - UpcycleConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?= t('pro_pub_heading', 'Campagnes publicitaires') ?></h2>
                <p class="text-gray-600 text-sm"><?= t('pro_pub_subtitle', 'Mettez en avant vos projets et annonces') ?></p>
            </div>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                    class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-2"></i><?= t('pro_pub_add', 'Nouvelle campagne') ?>
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
        <div class="max-w-4xl mx-auto space-y-6">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (empty($publicites)): ?>
                <div class="bg-white rounded-lg shadow text-center py-16 text-gray-400">
                    <i class="fas fa-ad text-4xl mb-3 block"></i>
                    <p><?= t('pro_pub_empty', "Aucune campagne pour l'instant.") ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($publicites as $pub): ?>
                        <?php
                        $statutColors = [
                            'active'   => 'bg-green-100 text-green-800',
                            'terminee' => 'bg-gray-100 text-gray-800',
                            'annulee'  => 'bg-red-100 text-red-800',
                        ];
                        $color = $statutColors[$pub['statut'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($pub['type'] ?? '') ?></h3>
                                    <?php if (!empty($pub['description'])): ?>
                                        <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($pub['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $color ?>">
                                    <?= htmlspecialchars(formatStatut($pub['statut'] ?? '')) ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?= htmlspecialchars(formatDate($pub['date_debut'] ?? '')) ?>
                                    <?php if (!empty($pub['date_fin'])): ?>
                                        &rarr; <?= htmlspecialchars(formatDate($pub['date_fin'])) ?>
                                    <?php endif; ?>
                                    <span class="mx-2">·</span>
                                    <?= number_format($pub['prix'] ?? 0, 2) ?> €
                                </div>
                                <?php if (($pub['statut'] ?? '') === 'active'): ?>
                                    <form method="POST" action="/professionnel/publicites/<?= htmlspecialchars($pub['id'] ?? '') ?>/annuler"
                                          onsubmit="return confirm('<?= t('pro_pub_confirm_cancel', 'Annuler cette campagne ?') ?>')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            <i class="fas fa-times mr-1"></i><?= t('pro_pub_cancel_btn', 'Annuler') ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
        </main>
    </div>
</div>

<!-- Modal Nouvelle campagne -->
<div id="modal-add" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('pro_pub_add', 'Nouvelle campagne') ?></h3>
            <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="form-publicite" onsubmit="return creerCampagne(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_pub_field_type', 'Type de campagne') ?> *</label>
                <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="mise_en_avant"><?= t('pro_pub_type_mise_avant', 'Mise en avant de projet') ?></option>
                    <option value="partenariat"><?= t('pro_pub_type_partenariat', 'Partenariat marque éco-responsable') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_pub_field_desc', 'Description') ?></label>
                <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_pub_field_debut', 'Date de début') ?> *</label>
                    <input type="date" name="date_debut" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_pub_field_fin', 'Date de fin') ?></label>
                    <input type="date" name="date_fin" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('pro_pub_field_prix', 'Budget (€)') ?> *</label>
                <input type="number" name="prix" min="1" step="0.01" required placeholder="100.00"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1"><?= t('pro_pub_field_prix_hint', 'Indicatif : de 100 à 500 € selon la portée souhaitée') ?></p>
            </div>
            <div id="pub-erreur" class="text-red-600 text-sm hidden"></div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('sal_cancel', 'Annuler') ?></button>
                <button type="submit" id="btn-creer-pub" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 font-semibold">
                    <i class="fas fa-lock mr-2"></i><?= t('pro_pub_create_btn', 'Payer et créer la campagne') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const TOKEN = <?= json_encode($token ?? '') ?>;

document.getElementById('modal-add').addEventListener('click', function(e) {
    if (e.target === this) this.classList.add('hidden');
});

async function creerCampagne(event) {
    event.preventDefault();
    const form = document.getElementById('form-publicite');
    const btn = document.getElementById('btn-creer-pub');
    const err = document.getElementById('pub-erreur');
    err.classList.add('hidden');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <?= t('pro_pub_processing', 'Redirection vers Stripe...') ?>';

    const data = new FormData(form);
    try {
        const res = await fetch('/api/professionnels/publicites/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
            body: JSON.stringify({
                type: data.get('type'),
                description: data.get('description'),
                date_debut: data.get('date_debut'),
                date_fin: data.get('date_fin'),
                prix: parseFloat(data.get('prix')) || 0,
            })
        });
        const json = await res.json();
        if (json.success && json.data && json.data.checkout_url) {
            window.location.href = json.data.checkout_url;
            return false;
        }
        err.textContent = (json && json.error) || <?= json_encode(t('pro_pub_checkout_error', 'Erreur lors de la création de la session de paiement.')) ?>;
        err.classList.remove('hidden');
    } catch (e) {
        err.textContent = <?= json_encode(t('pro_pub_checkout_error', 'Erreur lors de la création de la session de paiement.')) ?>;
        err.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-lock mr-2"></i> <?= t('pro_pub_create_btn', 'Payer et créer la campagne') ?>';
    return false;
}
</script>

</body>
</html>
