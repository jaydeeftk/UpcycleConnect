<section class="max-w-7xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= t('mesprest_title', 'Mes prestations réservées') ?></h1>
            <p class="text-lg text-base-content/70 max-w-2xl">
                <?= t('mesprest_subtitle2', 'Retrouvez ici vos prestations achetées et vos demandes sur mesure.') ?>
            </p>
        </div>
        <a href="/demande-prestation" class="bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition whitespace-nowrap">
            <i class="fas fa-plus mr-2"></i><?= t('mesprest_new', 'Nouvelle demande sur mesure') ?>
        </a>
    </div>

    <div class="flex flex-wrap gap-2 mb-8">
        <button type="button" class="filtre-presta-btn btn btn-sm" data-filtre="toutes"><?= t('mesprest_filter_all', 'Toutes') ?></button>
        <button type="button" class="filtre-presta-btn btn btn-sm btn-ghost" data-filtre="achetees"><?= t('mesprest_filter_bought', 'Achetées directement') ?></button>
        <button type="button" class="filtre-presta-btn btn btn-sm btn-ghost" data-filtre="demande"><?= t('mesprest_filter_custom', 'Sur demande') ?></button>
    </div>

    <?php if (isset($_GET['envoye'])): ?>
        <div class="alert alert-success mb-6"><i class="fas fa-check-circle"></i><span><?= t('mesprest_sent', 'Votre demande a bien été envoyée. Un prestataire pourra vous contacter.') ?></span></div>
    <?php elseif (isset($_GET['erreur'])): ?>
        <div class="alert alert-error mb-6"><i class="fas fa-exclamation-circle"></i><span><?= t('mesprest_error', 'Une erreur est survenue lors de l\'envoi de votre demande.') ?></span></div>
    <?php endif; ?>

    <?php if (empty($prestations) && empty($commandes_catalogue)): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-12 text-center">
            <i class="fas fa-clipboard-list text-5xl text-base-content/30 mb-4"></i>
            <h2 class="text-xl font-semibold mb-2"><?= t('mesprest_empty_title', 'Aucune prestation pour le moment') ?></h2>
            <p class="text-base-content/60 mb-6 max-w-md mx-auto"><?= t('mesprest_empty_text', 'Achetez une prestation du catalogue ou décrivez votre besoin sur mesure.') ?></p>
            <div class="flex gap-3 justify-center">
                <a href="/catalogue/services" class="btn btn-outline"><?= t('mesprest_empty_cta_catalog', 'Voir le catalogue') ?></a>
                <a href="/demande-prestation" class="btn btn-neutral"><?= t('mesprest_empty_cta', 'Faire une demande') ?></a>
            </div>
        </div>
    <?php else: ?>

        <div class="section-achetees space-y-6 mb-10">
            <?php if (!empty($commandes_catalogue)): ?>
                <h2 class="text-xl font-bold"><?= t('mesprest_bought_title', 'Prestations achetées') ?> (<?= count($commandes_catalogue) ?>)</h2>
                <?php foreach ($commandes_catalogue as $c):
                    $statutC = $c['statut'] ?? 'payee';
                    $badgeC = [
                        'payee'    => 'bg-blue-100 text-blue-800',
                        'en_cours' => 'bg-yellow-100 text-yellow-800',
                        'terminee' => 'bg-green-100 text-green-800',
                    ][$statutC] ?? 'bg-blue-100 text-blue-800';
                ?>
                    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-3">
                            <div>
                                <div class="text-sm text-base-content/60 mb-1"><?= htmlspecialchars($c['titre_service'] ?? '') ?> · <?= htmlspecialchars($c['nom_client'] ?? '') ?></div>
                                <h3 class="text-xl font-semibold"><?= htmlspecialchars($c['nom_objet'] ?? '') ?></h3>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badgeC ?>"><?= htmlspecialchars(formatStatut($statutC)) ?></span>
                        </div>
                        <?php if (!empty($c['description_objet'])): ?>
                            <p class="text-base-content/70 mb-3"><?= htmlspecialchars($c['description_objet']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($c['photo_url'])): ?>
                            <a href="<?= htmlspecialchars($c['photo_url']) ?>" target="_blank" class="text-sm link link-primary mb-3 inline-block"><i class="fas fa-image mr-1"></i><?= t('mesprest_view_photo', 'Voir la photo') ?></a>
                        <?php endif; ?>
                        <div class="flex items-center justify-between pt-3 border-t border-base-300">
                            <span class="font-semibold"><?= number_format((float)($c['prix'] ?? 0), 2) ?> €</span>
                            <?php if (in_array($statutC, ['payee', 'en_cours', 'terminee'], true)): ?>
                                <button type="button" class="btn btn-outline btn-sm voir-etapes-commande-btn" data-commande-id="<?= (int)($c['id'] ?? 0) ?>">
                                    <i class="fas fa-list-check mr-1"></i><?= t('mesprest_view_steps', 'Voir les étapes') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-demande space-y-6">
            <?php if (!empty($prestations)): ?>
            <h2 class="text-xl font-bold"><?= t('mesprest_custom_title', 'Demandes sur mesure') ?> (<?= count($prestations) ?>)</h2>
            <?php foreach ($prestations as $p):
                $statut = $p['statut'] ?? 'ouverte';
                $badge = [
                    'ouverte'  => 'bg-blue-100 text-blue-800',
                    'en_cours' => 'bg-yellow-100 text-yellow-800',
                    'traitee'  => 'bg-green-100 text-green-800',
                    'annulee'  => 'bg-gray-100 text-gray-700',
                ][$statut] ?? 'bg-blue-100 text-blue-800';
                $statutLabel = [
                    'ouverte'  => t('mesprest_status_open', 'Ouverte'),
                    'en_cours' => t('mesprest_status_inprogress', 'En cours'),
                    'traitee'  => t('mesprest_status_done', 'Traitée'),
                    'annulee'  => t('mesprest_status_cancelled', 'Annulée'),
                ][$statut] ?? $statut;
            ?>
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-300 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div>
                            <div class="text-sm text-base-content/60 mb-1">
                                <?= htmlspecialchars($p['categorie'] ?? '') ?><?= !empty($p['type_objet']) ? ' • ' . htmlspecialchars($p['type_objet']) : '' ?>
                            </div>
                            <h2 class="text-2xl font-semibold"><?= htmlspecialchars($p['nom_objet'] ?? '') ?></h2>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $badge ?>"><?= htmlspecialchars($statutLabel) ?></span>
                    </div>
                    <?php if (!empty($p['description'])): ?>
                        <p class="text-base-content/70 mb-4"><?= htmlspecialchars($p['description']) ?></p>
                    <?php endif; ?>
                    <div class="grid md:grid-cols-3 gap-4 text-sm text-base-content/70">
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_place', 'Lieu :') ?></span> <?= htmlspecialchars(($p['localisation'] ?? '') ?: '—') ?></div>
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_budget', 'Budget :') ?></span> <?= htmlspecialchars(($p['budget'] ?? '') ?: '—') ?></div>
                        <div><span class="font-medium text-base-content"><?= t('mesprest_label_date', 'Date :') ?></span> <?= htmlspecialchars(($p['date'] ?? '') ?: '—') ?></div>
                    </div>

                    <?php if ($statut === 'ouverte'): ?>
                        <div class="mt-5 pt-5 border-t border-base-300">
                            <?php if (empty($p['devis'])): ?>
                                <p class="text-sm text-base-content/50 mb-3"><?= t('mesprest_no_quotes', "Aucun devis reçu pour l'instant.") ?></p>
                            <?php else: ?>
                                <p class="text-sm font-medium mb-3"><?= t('mesprest_quotes_title', 'Devis reçus') ?> (<?= count($p['devis']) ?>)</p>
                                <div class="space-y-3 mb-4">
                                    <?php foreach ($p['devis'] as $dv): ?>
                                        <?php if (($dv['statut'] ?? '') !== 'propose') continue; ?>
                                        <div class="flex items-center justify-between bg-base-200 rounded-xl p-4">
                                            <div>
                                                <div class="font-semibold"><?= htmlspecialchars($dv['nom_pro'] ?? '') ?> — <?= number_format((float)($dv['prix'] ?? 0), 2) ?> €</div>
                                                <div class="text-sm text-base-content/60"><?= htmlspecialchars($dv['message'] ?? '') ?></div>
                                            </div>
                                            <button type="button" class="btn btn-success btn-sm accept-devis-btn" data-devis-id="<?= (int)($dv['id'] ?? 0) ?>">
                                                <i class="fas fa-check mr-1"></i><?= t('mesprest_accept_quote', 'Accepter') ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST" action="/mes-prestations/<?= (int)($p['id'] ?? 0) ?>/annuler"
                                  onsubmit="return confirm('<?= t('mesprest_confirm_cancel', 'Annuler cette demande ?') ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">
                                    <i class="fas fa-times mr-1"></i><?= t('mesprest_cancel_request', 'Annuler ma demande') ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <?php if (in_array($statut, ['en_cours', 'traitee'], true)): ?>
                        <div class="mt-4 pt-4 border-t border-base-300">
                            <button type="button" class="btn btn-outline btn-sm voir-etapes-btn" data-demande-id="<?= (int)($p['id'] ?? 0) ?>">
                                <i class="fas fa-list-check mr-1"></i><?= t('mesprest_view_steps', 'Voir les étapes') ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</section>

<div id="modal-etapes" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-base-100 rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] overflow-y-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('mesprest_steps_title', 'Suivi des étapes') ?></h3>
            <button type="button" onclick="document.getElementById('modal-etapes').classList.add('hidden')" class="text-base-content/40 hover:text-base-content">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="modal-etapes-content" class="space-y-4"></div>
    </div>
</div>

<script>
const TOKEN_PRESTA = <?= json_encode($token ?? '') ?>;

document.querySelectorAll('.filtre-presta-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filtre-presta-btn').forEach(function (b) {
            b.classList.remove('btn-neutral');
            b.classList.add('btn-ghost');
        });
        btn.classList.remove('btn-ghost');
        btn.classList.add('btn-neutral');
        const f = btn.getAttribute('data-filtre');
        const achetees = document.querySelector('.section-achetees');
        const demande = document.querySelector('.section-demande');
        achetees.style.display = (f === 'toutes' || f === 'achetees') ? '' : 'none';
        demande.style.display = (f === 'toutes' || f === 'demande') ? '' : 'none';
    });
});

document.querySelectorAll('.accept-devis-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const idDevis = btn.getAttribute('data-devis-id');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        fetch('/api/prestations/devis/' + idDevis + '/checkout', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + TOKEN_PRESTA }
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (json.success && json.data && json.data.checkout_url) {
                    window.location.href = json.data.checkout_url;
                    return;
                }
                alert((json && json.error) || <?= json_encode(t('mesprest_checkout_error', 'Erreur lors de la création du paiement.')) ?>);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i><?= t('mesprest_accept_quote', 'Accepter') ?>';
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i><?= t('mesprest_accept_quote', 'Accepter') ?>';
            });
    });
});

function escapeHtmlPresta(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function chargerEtapesModal(url) {
    const modal = document.getElementById('modal-etapes');
    const content = document.getElementById('modal-etapes-content');
    content.innerHTML = '<p class="text-center text-base-content/40 py-6"><i class="fas fa-spinner fa-spin mr-2"></i><?= t('mesprest_steps_loading', 'Chargement...') ?></p>';
    modal.classList.remove('hidden');

    fetch(url, {
        headers: { 'Authorization': 'Bearer ' + TOKEN_PRESTA }
    })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            const etapes = (json && json.data) || [];
            if (!Array.isArray(etapes) || etapes.length === 0) {
                content.innerHTML = '<p class="text-center text-base-content/40 py-6"><i class="fas fa-hourglass-half text-3xl mb-3 block"></i>' + <?= json_encode(t('mesprest_steps_empty', "Aucune étape enregistrée pour l'instant.")) ?> + '</p>';
                return;
            }
            content.innerHTML = etapes.map(function (e, i) {
                let photosHtml = '';
                if (e.photos && e.photos.length) {
                    photosHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">' + e.photos.map(function (p) {
                        const label = p.type_photo === 'avant' ? <?= json_encode(t('mesprest_photo_before', 'Avant')) ?> : <?= json_encode(t('mesprest_photo_after', 'Après')) ?>;
                        return '<a href="' + escapeHtmlPresta(p.url) + '" target="_blank" class="block group">' +
                            '<img src="' + escapeHtmlPresta(p.url) + '" alt="' + label + '" loading="lazy" class="w-full h-24 object-cover rounded-lg border border-base-300 group-hover:opacity-90 transition">' +
                            '<span class="block text-[11px] text-base-content/50 mt-1 text-center">' + label + '</span>' +
                        '</a>';
                    }).join('') + '</div>';
                }
                return '<div class="border border-base-300 rounded-xl p-4">' +
                    '<div class="flex items-start gap-3">' +
                    '<span class="flex-shrink-0 w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">' + (i + 1) + '</span>' +
                    '<div>' +
                    '<h4 class="font-semibold">' + escapeHtmlPresta(e.nom) + '</h4>' +
                    (e.description ? '<p class="text-sm text-base-content/60 mt-1">' + escapeHtmlPresta(e.description) + '</p>' : '') +
                    photosHtml +
                    '</div></div></div>';
            }).join('');
        })
        .catch(function () {
            content.innerHTML = '<p class="text-center text-error py-6"><?= t('mesprest_steps_error', "Erreur lors du chargement des étapes.") ?></p>';
        });
}

document.querySelectorAll('.voir-etapes-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const idDemande = btn.getAttribute('data-demande-id');
        chargerEtapesModal('/api/prestations/demandes/' + idDemande + '/etapes');
    });
});

document.querySelectorAll('.voir-etapes-commande-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const idCommande = btn.getAttribute('data-commande-id');
        chargerEtapesModal('/api/services/commandes/' + idCommande + '/etapes');
    });
});

document.getElementById('modal-etapes').addEventListener('click', function (e) {
    if (e.target === this) this.classList.add('hidden');
});
</script>
