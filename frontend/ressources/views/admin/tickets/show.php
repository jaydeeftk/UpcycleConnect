<div class="mb-4">
    <a href="/admin/tickets" class="text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
        <i class="fas fa-arrow-left mr-1"></i> <?= t('adm_ticket_back', 'Retour aux tickets') ?>
    </a>
</div>

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col" style="height: 65vh;">
    <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="font-semibold text-slate-800 dark:text-slate-100"><?= t('adm_ticket_title', 'Ticket') ?> #<?= (int)$id_ticket ?></h2>
        <button type="button" id="ticket-fermer-btn" class="hidden btn btn-outline btn-error btn-sm">
            <i class="fas fa-lock mr-2"></i><?= t('adm_ticket_close_btn', 'Fermer le ticket') ?>
        </button>
    </div>
    <div id="ticket-messages" class="flex-1 overflow-y-auto p-5 space-y-3"></div>
    <div id="ticket-ferme-notice" class="hidden text-center text-xs text-slate-400 px-4 pb-2">
        <?= t('adm_ticket_closed_notice', 'Ce ticket est fermé.') ?>
    </div>
    <form id="ticket-form" class="border-t border-slate-100 dark:border-slate-800 p-4 flex gap-2">
        <input type="text" id="ticket-input" maxlength="1000" autocomplete="off"
               placeholder="<?= t('adm_ticket_ph', 'Répondre...') ?>"
               class="input input-bordered flex-1">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<script>
(function () {
    const ID_TICKET = <?= (int)$id_ticket ?>;
    const TOKEN = <?= json_encode($token ?? '') ?>;
    let statut = null;
    let lastCount = 0;

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function bulle(m) {
        const mine = m.est_moi;
        return `<div class="flex ${mine ? 'justify-end' : 'justify-start'}">
            <div class="max-w-[75%] rounded-2xl px-4 py-2 ${mine ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-800'}">
                <div class="text-sm">${escapeHtml(m.contenu).replace(/\n/g, '<br>')}</div>
                <div class="text-[10px] opacity-60 mt-1">${escapeHtml(m.date_envoi)}</div>
            </div>
        </div>`;
    }

    function charger(scroll) {
        fetch('/api/tickets/' + ID_TICKET + '/messages', {
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                const msgs = (json && json.data) || json;
                if (!Array.isArray(msgs) || msgs.length === lastCount) return;
                lastCount = msgs.length;
                const zone = document.getElementById('ticket-messages');
                zone.innerHTML = msgs.map(bulle).join('');
                if (scroll !== false) zone.scrollTop = zone.scrollHeight;
            })
            .catch(function () {});
    }

    document.getElementById('ticket-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('ticket-input');
        const contenu = input.value.trim();
        if (!contenu) return;
        input.value = '';
        fetch('/api/tickets/' + ID_TICKET + '/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
            body: JSON.stringify({ contenu: contenu })
        }).then(function () { charger(true); });
    });

    document.getElementById('ticket-fermer-btn').addEventListener('click', function () {
        if (!confirm(<?= json_encode(t('adm_ticket_confirm_close', 'Fermer ce ticket ?')) ?>)) return;
        fetch('/api/tickets/' + ID_TICKET + '/fermer', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + TOKEN }
        }).then(function () {
            document.getElementById('ticket-fermer-btn').classList.add('hidden');
            document.getElementById('ticket-ferme-notice').classList.remove('hidden');
            document.getElementById('ticket-input').disabled = true;
        });
    });

    document.getElementById('ticket-fermer-btn').classList.remove('hidden');
    charger(true);
    setInterval(function () { charger(true); }, 4000);
})();
</script>
