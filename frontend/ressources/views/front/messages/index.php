<section class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-2"><?= t('msgsupport_title', 'Mes messages') ?></h1>
    <p class="text-base-content/60 mb-6"><?= t('msgsupport_subtitle', "Besoin d'aide ? Contactez notre équipe support.") ?></p>

    <div class="bg-base-100 rounded-2xl shadow-sm flex flex-col" style="height: 65vh;">
        <div id="ticket-messages" class="flex-1 overflow-y-auto p-5 space-y-3"></div>

        <div id="ticket-en-attente" class="hidden text-center text-xs text-base-content/50 px-4 pb-2">
            <i class="fas fa-hourglass-half mr-1"></i>
            <?= t('msgsupport_waiting', 'Un admin acceptera votre ticket incessamment sous peu.') ?>
        </div>
        <div id="ticket-ferme" class="hidden text-center text-xs text-base-content/50 px-4 pb-2">
            <?= t('msgsupport_closed', 'Ce ticket est fermé. Envoyez un nouveau message pour en ouvrir un autre.') ?>
        </div>

        <form id="ticket-form" class="border-t border-base-300 p-4 flex gap-2">
            <input type="text" id="ticket-input" maxlength="1000" autocomplete="off"
                   placeholder="<?= t('msgsupport_ph', 'Écrivez votre message...') ?>"
                   class="input input-bordered flex-1">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <a href="/messages/historique" class="link link-hover text-sm text-base-content/50 mt-4 inline-block">
        <i class="fas fa-clock-rotate-left mr-1"></i> <?= t('msgsupport_history_link', 'Voir mes anciens tickets') ?>
    </a>
</section>

<script>
(function () {
    const TOKEN = <?= json_encode($token ?? '') ?>;
    let idTicket = null;
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
            <div class="max-w-[75%] rounded-2xl px-4 py-2 ${mine ? 'bg-primary text-primary-content' : 'bg-base-200'}">
                <div class="text-sm">${escapeHtml(m.contenu).replace(/\n/g, '<br>')}</div>
                <div class="text-[10px] opacity-60 mt-1">${escapeHtml(m.date_envoi)}</div>
            </div>
        </div>`;
    }

    function majEtatUI() {
        document.getElementById('ticket-en-attente').classList.toggle('hidden', statut !== 'en_attente');
        document.getElementById('ticket-ferme').classList.toggle('hidden', statut !== 'ferme');
        document.getElementById('ticket-input').disabled = statut === 'ferme';
    }

    function chargerMessages(scroll) {
        if (!idTicket) return;
        fetch('/api/tickets/' + idTicket + '/messages', {
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

    function chargerTicket() {
        fetch('/api/tickets/mon-ticket', { headers: { 'Authorization': 'Bearer ' + TOKEN } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                const t = (json && json.data) || null;
                if (t && t.id) {
                    idTicket = t.id;
                    statut = t.statut;
                    majEtatUI();
                    chargerMessages(true);
                } else {
                    idTicket = null;
                    statut = null;
                    lastCount = 0;
                    document.getElementById('ticket-messages').innerHTML =
                        '<p class="text-center text-sm text-base-content/40 mt-10"><?= t('msgsupport_empty', "Aucun ticket en cours. Envoyez un message pour démarrer.") ?></p>';
                    majEtatUI();
                }
            })
            .catch(function () {});
    }

    document.getElementById('ticket-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('ticket-input');
        const contenu = input.value.trim();
        if (!contenu || statut === 'ferme') return;
        input.value = '';

        const url = idTicket ? '/api/tickets/' + idTicket + '/messages' : '/api/tickets/messages';
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + TOKEN },
            body: JSON.stringify({ contenu: contenu })
        }).then(function () { chargerTicket(); });
    });

    chargerTicket();
    setInterval(chargerTicket, 4000);
})();
</script>
