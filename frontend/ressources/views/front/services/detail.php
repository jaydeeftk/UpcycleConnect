<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <a href="/catalogue/services" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-8">
        <i class="fas fa-arrow-left"></i> <?= t('svcdet_back', 'Retour aux services') ?>
    </a>

    <div class="grid lg:grid-cols-2 gap-12 items-start">

        <div class="bg-base-100 rounded-3xl shadow-sm overflow-hidden">
            <img src="<?= uc_image('service', $service['id'] ?? ($service['titre'] ?? '')) ?>"
                 alt="<?= htmlspecialchars($service['titre'] ?? '') ?>"
                 class="w-full h-full object-cover min-h-[360px]">
        </div>

        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="badge badge-ghost"><?= htmlspecialchars($service['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($service['titre'] ?? '') ?></h1>
            <p class="text-base-content/70 leading-relaxed mb-8"><?= htmlspecialchars($service['description'] ?? '') ?></p>

            <div class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-3 mb-8">
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('svcdet_duration', 'Durée estimée') ?></span>
                    <span class="text-base-content/70"><?= (int)($service['duree'] ?? 0) ?> <?= t('unit_days','jour(s)') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium"><?= t('svcdet_price', 'Tarif') ?></span>
                    <span class="text-2xl font-bold text-orange-500"><?= htmlspecialchars(formatPrix($service['prix'] ?? 0)) ?></span>
                </div>
            </div>

            <?php if (($service['type_auteur'] ?? '') === 'pro'): ?>
                <?php if (!empty($service['nom_auteur'])): ?>
                    <p class="text-sm text-base-content/50 mb-4">
                        <i class="fas fa-user-tie mr-1"></i><?= t('svcdet_by', 'Proposé par') ?> <span class="font-medium"><?= htmlspecialchars($service['nom_auteur']) ?></span>
                    </p>
                <?php endif; ?>
                <div id="commande-erreur" class="hidden bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-4 text-sm"></div>
                <form id="form-commande-service" class="bg-base-100 rounded-2xl border border-base-300 p-6 space-y-4 mb-4">
                    <h3 class="font-semibold"><?= t('svcdet_order_title', "Précisez l'objet concerné") ?></h3>
                    <div>
                        <label class="block text-sm font-medium mb-1"><?= t('svcdet_order_object', "Nom de l'objet") ?> <span class="text-red-500">*</span></label>
                        <input type="text" name="nom_objet" required class="input input-bordered w-full" placeholder="<?= t('svcdet_order_object_ph', 'Ex : Vélo de ville, commode en bois...') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"><?= t('svcdet_order_photo', "Photo de l'objet") ?></label>
                        <input type="url" name="photo_url" class="input input-bordered w-full" placeholder="<?= t('svcdet_order_photo_ph', 'https://... (lien vers une photo)') ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"><?= t('svcdet_order_desc', 'Précisions') ?></label>
                        <textarea name="description_objet" rows="3" class="textarea textarea-bordered w-full" placeholder="<?= t('svcdet_order_desc_ph', "Décrivez l'état de l'objet et ce que vous attendez.") ?>"></textarea>
                    </div>
                    <button type="submit" id="btn-commande-service" class="w-full btn btn-neutral">
                        <?= t('svcdet_order_pay', 'Payer et commander —') ?> <?= formatPrix($service['prix'] ?? 0) ?>
                    </button>
                </form>
                <script>
                (function () {
                    const form = document.getElementById('form-commande-service');
                    const btn = document.getElementById('btn-commande-service');
                    const errBox = document.getElementById('commande-erreur');
                    const TOKEN = <?= json_encode($_SESSION['user']['token'] ?? '') ?>;
                    const IS_LOGGED_IN = <?= json_encode(isset($_SESSION['user'])) ?>;
                    const ID_SERVICE = <?= (int)($service['id'] ?? 0) ?>;

                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        if (!IS_LOGGED_IN) {
                            window.location.href = '/login';
                            return;
                        }
                        errBox.classList.add('hidden');
                        btn.disabled = true;
                        const original = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                        fetch('/api/services/commander', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
                            body: JSON.stringify({
                                id_service: ID_SERVICE,
                                nom_objet: form.nom_objet.value,
                                photo_url: form.photo_url.value,
                                description_objet: form.description_objet.value,
                            })
                        })
                            .then(function (r) { return r.json(); })
                            .then(function (json) {
                                if (!json.success || !json.data || !json.data.id_commande) {
                                    throw new Error((json && json.error) || 'Erreur');
                                }
                                return fetch('/api/services/commandes/' + json.data.id_commande + '/checkout', {
                                    method: 'POST',
                                    headers: { 'Authorization': 'Bearer ' + TOKEN }
                                });
                            })
                            .then(function (r) { return r.json(); })
                            .then(function (json) {
                                if (json.success && json.data && json.data.checkout_url) {
                                    window.location.href = json.data.checkout_url;
                                    return;
                                }
                                throw new Error((json && json.error) || 'Erreur lors de la création du paiement');
                            })
                            .catch(function (e) {
                                errBox.textContent = e.message || 'Une erreur est survenue.';
                                errBox.classList.remove('hidden');
                                btn.disabled = false;
                                btn.innerHTML = original;
                            });
                    });
                })();
                </script>
            <?php elseif (isset($_SESSION['user'])): ?>
                <a href="mailto:contact@upcycleconnect.fr?subject=Demande de service : <?= htmlspecialchars($service['titre'] ?? '') ?>"
                   class="btn btn-neutral w-full">
                    <i class="fas fa-envelope mr-2"></i> <?= t('svcdet_request', 'Demander ce service') ?>
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-neutral w-full">
                    <i class="fas fa-sign-in-alt mr-2"></i> <?= t('svcdet_login_request', 'Connectez-vous pour demander ce service') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
