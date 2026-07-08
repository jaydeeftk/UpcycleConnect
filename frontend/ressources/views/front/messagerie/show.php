<?php $__pro = !empty($isPro); ?>
<?php if ($__pro): ?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('msgshow_title', 'Conversation') ?> - UpcycleConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.10.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>(function(){ if ((localStorage.getItem('theme') || 'light') === 'dark') { document.documentElement.classList.add('dark'); document.documentElement.setAttribute('data-theme','dark'); } })();</script>
    <?php include __DIR__ . '/../../components/pro/dark.php'; ?>
</head>
<body class="bg-gray-100">
<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../../components/pro/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-y-auto bg-gray-100">
<?php endif; ?>

<section class="max-w-2xl mx-auto px-4 py-10">
    <a href="/messagerie" class="link link-hover text-sm text-base-content/50 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> <?= t('msgshow_back', 'Retour à la messagerie') ?>
    </a>

    <div class="bg-base-100 rounded-2xl shadow-sm flex flex-col" style="height: 70vh;">
        <div id="conv-messages" class="flex-1 overflow-y-auto p-5 space-y-3"></div>
        <form id="conv-form" class="border-t border-base-300 p-4 flex gap-2">
            <input type="text" id="conv-input" maxlength="1000" autocomplete="off"
                   placeholder="<?= t('msgshow_ph', 'Écrivez votre message...') ?>"
                   class="input input-bordered flex-1">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</section>

<script>
(function () {
    const CONV_ID = <?= (int)$id_conversation ?>;
    const TOKEN = <?= json_encode($token ?? '') ?>;
    const USER_ID = <?= (int)$user_id ?>;
    let lastCount = 0;
    let idAnnonce = 0;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function bulle(m) {
        if (m.est_automatique) {
            return `<div class="flex justify-center">
                <div class="max-w-[85%] rounded-2xl px-4 py-3 bg-base-200 border border-base-300 text-center">
                    <div class="text-xs text-base-content/50 uppercase font-semibold mb-1">
                        <i class="fas fa-info-circle mr-1"></i><?= t('msgshow_system', 'Notification automatique') ?>
                    </div>
                    <div class="text-sm">${escapeHtml(m.contenu).replace(/\n/g, '<br>')}</div>
                    <div class="text-[10px] opacity-60 mt-1">${escapeHtml(m.date_envoi)}</div>
                    ${m.peut_deposer && idAnnonce > 0 ? '<a href="/conteneurs/create?id_annonce=' + idAnnonce + '" class="btn btn-success btn-xs mt-3"><i class="fas fa-box mr-1"></i>' + <?= json_encode(t('msgshow_deposit_cta', "Déposer l'objet")) ?> + '</a>' : ''}
                </div>
            </div>`;
        }
        const mine = m.est_moi;
        return `<div class="flex ${mine ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[75%] rounded-2xl px-4 py-2 ${mine ? 'bg-primary text-primary-content' : 'bg-base-200'}">
                <div class="text-sm">${escapeHtml(m.contenu).replace(/\n/g, '<br>')}</div>
                <div class="text-[10px] opacity-60 mt-1">${escapeHtml(m.date_envoi)}</div>
            </div>
        </div>`;
    }

    function chargerInfo() {
        fetch('/api/conversations/' + CONV_ID, {
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                const info = (json && json.data) || null;
                if (info && info.id_annonce) {
                    idAnnonce = info.id_annonce;
                    lastCount = -1;
                    charger(false);
                }
            })
            .catch(function () {});
    }

    function charger(scroll) {
        fetch('/api/conversations/' + CONV_ID + '/messages', {
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                const msgs = (json && json.data) || json;
                if (!Array.isArray(msgs) || msgs.length === lastCount) return;
                lastCount = msgs.length;
                const zone = document.getElementById('conv-messages');
                zone.innerHTML = msgs.map(bulle).join('');
                if (scroll !== false) zone.scrollTop = zone.scrollHeight;
            })
            .catch(function () {});
    }

    document.getElementById('conv-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('conv-input');
        const contenu = input.value.trim();
        if (!contenu) return;
        input.value = '';
        fetch('/api/conversations/' + CONV_ID + '/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
            body: JSON.stringify({ contenu: contenu })
        }).then(function () { charger(true); });
    });

    chargerInfo();
    charger(true);
    setInterval(function () { charger(true); }, 4000);
})();
</script>

<?php if ($__pro): ?>
        </main>
    </div>
</div>
</body>
</html>
<?php endif; ?>
