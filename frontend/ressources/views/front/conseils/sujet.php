<section class="max-w-4xl mx-auto px-6 lg:px-10 py-16">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success']) ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-error mb-6">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error']) ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="mb-8">
        <a href="/conseils?onglet=forum" class="flex items-center gap-2 text-sm text-base-content/50 hover:text-base-content transition mb-6">
            <i class="fas fa-arrow-left"></i> <?= t('conssuj_back_forum', 'Retour au forum') ?>
        </a>

        <div class="bg-base-100 rounded-2xl shadow-sm p-8">
            <div class="flex items-center gap-2 mb-4">
                <?php if ($sujet['resolu'] ?? false): ?>
                    <span class="badge badge-success gap-1"><i class="fas fa-check"></i> <?= t('conssuj_status_resolved', 'Résolu') ?></span>
                <?php else: ?>
                    <span class="badge badge-ghost"><?= t('conssuj_status_open', 'Ouvert') ?></span>
                <?php endif; ?>
                <span class="badge badge-ghost"><?= htmlspecialchars($sujet['categorie'] ?? '') ?></span>
            </div>

            <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($sujet['titre'] ?? '') ?></h1>

            <p class="text-base-content/70 leading-relaxed mb-6"><?= nl2br(htmlspecialchars($sujet['contenu'] ?? '')) ?></p>

            <div class="flex items-center justify-between pt-4 border-t border-base-300">
                <div class="flex items-center gap-2 text-sm text-base-content/50">
                    <i class="fas fa-user-circle text-lg"></i>
                    <span class="font-medium text-base-content/70"><?= htmlspecialchars($sujet['auteur'] ?? '') ?></span>
                    <span>·</span>
                    <span><?= formatDate($sujet['date'] ?? '') ?></span>
                </div>
                <div class="flex items-center gap-4 text-sm text-base-content/50">
                    <span><i class="fas fa-eye mr-1"></i><?= $sujet['vues'] ?? 0 ?> <?= t('conssuj_views', 'vues') ?></span>
                    <span><i class="fas fa-comments mr-1"></i><?= count($sujet['reponses'] ?? []) ?> <?= t('conssuj_replies', 'réponses') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div id="reponses-wrapper" class="<?= empty($sujet['reponses']) ? 'hidden' : '' ?>">
        <div class="space-y-4 mb-10">
            <h2 class="text-lg font-semibold"><span id="reponses-count-n"><?= count($sujet['reponses'] ?? []) ?></span> <?= t('conssuj_reply', 'réponse') ?><span id="reponses-count-s"><?= count($sujet['reponses'] ?? []) > 1 ? 's' : '' ?></span></h2>

            <div id="reponses-list" class="space-y-4">
            <?php foreach ($sujet['reponses'] ?? [] as $reponse): ?>
                <div class="bg-base-100 rounded-2xl shadow-sm p-6 <?= ($reponse['est_solution'] ?? false) ? 'border-2 border-success' : '' ?>">
                    <?php if ($reponse['est_solution'] ?? false): ?>
                        <div class="flex items-center gap-2 text-success text-sm font-semibold mb-3">
                            <i class="fas fa-check-circle"></i> <?= t('conssuj_best_answer', 'Meilleure réponse') ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-base-content/80 leading-relaxed mb-4"><?= nl2br(htmlspecialchars($reponse['contenu'] ?? '')) ?></p>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-base-content/50">
                            <i class="fas fa-user-circle text-lg"></i>
                            <span class="font-medium text-base-content/70"><?= htmlspecialchars($reponse['auteur'] ?? '') ?></span>
                            <span class="badge badge-ghost badge-sm"><?= htmlspecialchars($reponse['auteur_statut'] ?? '') ?></span>
                            <span>· <?= formatDate($reponse['date'] ?? '') ?></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] ?? 0) == ($sujet['auteur_id'] ?? -1) && !($sujet['resolu'] ?? false)): ?>
                                <form method="POST" action="/conseils/forum/<?= $sujet['id'] ?>/solution/<?= $reponse['id'] ?>">
                                <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-success btn-xs gap-1">
                                        <i class="fas fa-check"></i> <?= t('conssuj_mark_solution', 'Marquer comme solution') ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['id'] ?? 0) == ($reponse['auteur_id'] ?? -1)): ?>
                                <form method="POST" action="/conseils/forum/reponses/<?= $reponse['id'] ?>/supprimer" onsubmit="return confirm('<?= t('conssuj_confirm_delete', 'Supprimer définitivement ce message ?') ?>');">
                                <?= csrf_field() ?>
                                    <input type="hidden" name="id_sujet" value="<?= $sujet['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-xs gap-1 text-error">
                                        <i class="fas fa-trash"></i> <?= t('conssuj_delete', 'Supprimer') ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div id="reponses-empty" class="text-center py-10 text-base-content/40 mb-10 <?= empty($sujet['reponses']) ? '' : 'hidden' ?>">
        <i class="fas fa-comments text-4xl mb-3 block"></i>
        <p><?= t('conssuj_empty', 'Aucune réponse pour l\'instant. Soyez le premier à répondre !') ?></p>
    </div>

    <?php
    $sujetStatut = $sujet['statut'] ?? 'ouvert';
    $sujetFerme  = $sujetStatut === 'ferme';
    $sujetResolu = $sujetStatut === 'resolu' || ($sujet['resolu'] ?? false);
    ?>
    <?php if (isset($_SESSION['user']) && !$sujetResolu && !$sujetFerme): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8">
            <h2 class="text-lg font-semibold mb-6"><?= t('conssuj_your_answer', 'Votre réponse') ?></h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/conseils/forum/<?= $sujet['id'] ?>/repondre">
            <?= csrf_field() ?>
                <div class="mb-4">
                    <textarea
                        name="contenu"
                        rows="5"
                        placeholder="<?= t('conssuj_textarea_ph', 'Partagez votre expérience ou votre conseil...') ?>"
                        class="textarea textarea-bordered w-full resize-none"
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-neutral">
                        <i class="fas fa-paper-plane mr-2"></i> <?= t('conssuj_publish', 'Publier ma réponse') ?>
                    </button>
                </div>
            </form>
        </div>
    <?php elseif ($sujetFerme): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8 text-center">
            <div class="text-base-content/60 mb-2"><i class="fas fa-lock text-2xl"></i></div>
            <p class="text-base-content/60"><?= t('conssuj_closed_locked', 'Ce sujet est fermé. Il n\'est plus possible d\'y répondre.') ?></p>
        </div>
    <?php elseif ($sujetResolu): ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8 text-center">
            <div class="text-success mb-2"><i class="fas fa-check-circle text-2xl"></i></div>
            <p class="text-base-content/60"><?= t('conssuj_resolved_locked', 'Ce sujet est marqué comme résolu. Il n\'est plus possible d\'y répondre.') ?></p>
        </div>
    <?php else: ?>
        <div class="bg-base-100 rounded-2xl shadow-sm p-8 text-center">
            <p class="text-base-content/60 mb-4"><?= t('conssuj_login_prompt', 'Connectez-vous pour répondre à ce sujet.') ?></p>
            <a href="/login" class="btn btn-neutral">
                <i class="fas fa-sign-in-alt mr-2"></i> <?= t('conssuj_login_btn', 'Se connecter') ?>
            </a>
        </div>
    <?php endif; ?>

</section>

<script>
(function () {
    var SUJET_ID = <?= (int)($sujet['id'] ?? 0) ?>;
    var TOKEN = <?= json_encode($token ?? '') ?>;
    var USER_ID = <?= (int)($_SESSION['user']['id'] ?? 0) ?>;
    var lastCount = <?= count($sujet['reponses'] ?? []) ?>;

    if (!SUJET_ID) return;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function carteReponse(r) {
        var suppr = '';
        if (USER_ID && USER_ID === r.auteur_id) {
            suppr = '<form method="POST" action="/conseils/forum/reponses/' + r.id + '/supprimer" ' +
                'onsubmit="return confirm(\'<?= t('conssuj_confirm_delete', 'Supprimer définitivement ce message ?') ?>\');">' +
                '<?= csrf_field() ?>' +
                '<input type="hidden" name="id_sujet" value="' + SUJET_ID + '">' +
                '<button type="submit" class="btn btn-ghost btn-xs gap-1 text-error">' +
                '<i class="fas fa-trash"></i> <?= t('conssuj_delete', 'Supprimer') ?></button></form>';
        }
        return '<div class="bg-base-100 rounded-2xl shadow-sm p-6 uc-reponse-live" data-id="' + r.id + '">' +
            '<p class="text-base-content/80 leading-relaxed mb-4">' + escapeHtml(r.contenu).replace(/\n/g, '<br>') + '</p>' +
            '<div class="flex items-center justify-between">' +
            '<div class="flex items-center gap-2 text-sm text-base-content/50">' +
            '<i class="fas fa-user-circle text-lg"></i>' +
            '<span class="font-medium text-base-content/70">' + escapeHtml(r.auteur) + '</span>' +
            '<span class="badge badge-ghost badge-sm">' + escapeHtml(r.auteur_statut) + '</span>' +
            '<span>· ' + escapeHtml(r.date) + '</span></div>' +
            '<div class="flex items-center gap-2">' + suppr + '</div>' +
            '</div></div>';
    }

    function poll() {
        fetch('/api/forum/sujets/' + SUJET_ID + '/reponses', {
            headers: TOKEN ? { 'Authorization': 'Bearer ' + TOKEN } : {}
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (json) {
                var reponses = (json && json.data) || json;
                if (!Array.isArray(reponses) || reponses.length <= lastCount) return;

                var nouvelles = reponses.slice(lastCount);
                var liste = document.getElementById('reponses-list');
                nouvelles.forEach(function (r) {
                    if (liste.querySelector('[data-id="' + r.id + '"]')) return;
                    liste.insertAdjacentHTML('beforeend', carteReponse(r));
                });

                lastCount = reponses.length;
                document.getElementById('reponses-wrapper').classList.remove('hidden');
                document.getElementById('reponses-empty').classList.add('hidden');
                document.getElementById('reponses-count-n').textContent = lastCount;
                document.getElementById('reponses-count-s').textContent = lastCount > 1 ? 's' : '';
            })
            .catch(function () {});
    }

    setInterval(poll, 5000);
})();
</script>
