<section class="max-w-2xl mx-auto px-4 py-10">
    <a href="/messages/historique" class="link link-hover text-sm text-base-content/50 mb-4 inline-block">
        <i class="fas fa-arrow-left mr-1"></i> <?= t('msghistd_back', "Retour à l'historique") ?>
    </a>
    <h1 class="text-xl font-bold mb-6"><?= t('msghistd_title', 'Ticket') ?> #<?= (int)$id_ticket ?></h1>

    <div class="bg-base-100 rounded-2xl shadow-sm p-5 space-y-3" style="min-height: 300px;" id="ticket-messages"></div>
</section>

<script>
(function () {
    const ID_TICKET = <?= (int)$id_ticket ?>;
    const TOKEN = <?= json_encode($token ?? '') ?>;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function bulle(m) {
        const mine = m.est_moi;
        return `<div class="flex ${mine ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[75%] rounded-2xl px-4 py-2 ${mine ? 'bg-primary text-primary-content' : 'bg-base-200'}">
                <div class="text-sm">${escapeHtml(m.contenu).replace(/\n/g, '<br>')}</div>
                <div class="text-[10px] opacity-60 mt-1">${escapeHtml(m.date_envoi)}</div>
            </div>
        </div>`;
    }

    fetch('/api/tickets/' + ID_TICKET + '/messages', {
        headers: { 'Authorization': 'Bearer ' + TOKEN }
    })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (json) {
            const msgs = (json && json.data) || json;
            const zone = document.getElementById('ticket-messages');
            zone.innerHTML = Array.isArray(msgs) && msgs.length
                ? msgs.map(bulle).join('')
                : '<p class="text-center text-sm text-base-content/40"><?= t('msghistd_empty', 'Aucun message.') ?></p>';
        })
        .catch(function () {});
})();
</script>
