<?php
$userId = $user_id ?? $_SESSION['user']['id'] ?? 0;
$token  = $token ?? $_SESSION['user']['token'] ?? '';
?>
<section class="max-w-3xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold mb-2">Mes messages</h1>
    <p class="text-base-content/60 mb-6">Historique de vos echanges avec le support UpcycleConnect.</p>

    <div id="chat-box" class="space-y-4 mb-8 min-h-[80px] max-h-[500px] overflow-y-auto pr-2">
        <div class="text-center text-base-content/40 text-sm py-8" id="loading-msg">
            <i class="fas fa-spinner fa-spin mr-2"></i>Chargement...
        </div>
    </div>

    <div class="bg-base-100 rounded-2xl border border-base-300 p-6">
        <h3 class="font-semibold mb-3">Envoyer un message au support</h3>
        <div class="space-y-3">
            <textarea id="msg-input" rows="4" placeholder="Votre message..."
                class="w-full textarea textarea-bordered resize-none"></textarea>
            <div id="msg-error" class="text-rose-500 text-sm hidden"></div>
            <button onclick="sendMessage()"
                class="bg-black text-white px-6 py-3 rounded-xl font-medium hover:bg-neutral-800 transition">
                <i class="fas fa-paper-plane mr-2"></i>Envoyer
            </button>
        </div>
    </div>
</section>

<script>
const _token  = '<?= htmlspecialchars($token) ?>';
const _userId = <?= (int)$userId ?>;
let ws = null;
let typingTimeout = null;

function initWS() {
    if (!_token) return;
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    ws = new WebSocket(protocol + '//' + window.location.host + '/api/ws?token=' + _token);

    ws.onmessage = function(e) {
        try {
            const data = JSON.parse(e.data);
            if (data.type === 'typing' && data.is_admin) {
                showTyping();
            } else if (data.type === 'message' && data.is_admin) {
                hideTyping();
                appendMessage(data, true);
            }
        } catch(err) {}
    };
}

function escHtml(s) {
    let r = String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    return r.replace(/\[IMG\](.*?)\[\/IMG\]/g, '<a href=\"$1\" target=\"_blank\"><img src=\"$1\" class=\"max-w-xs rounded-lg mt-2 border border-slate-200\"></a>');
}

function showTyping() {
    let el = document.getElementById('typing-indicator');
    if (!el) {
        const box = document.getElementById('chat-box');
        if (box.querySelector('.fa-envelope-open')) box.innerHTML = '';
        box.insertAdjacentHTML('beforeend', '<div id="typing-indicator" class="flex justify-start gap-3 items-end mb-3"><div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs shrink-0"><i class="fas fa-headset"></i></div><div class="max-w-lg bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3"><p class="text-sm italic text-emerald-600">Le support est en train d\'écrire...</p></div></div>');
        box.scrollTop = box.scrollHeight;
    }
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        const ind = document.getElementById('typing-indicator');
        if (ind) ind.remove();
    }, 3000);
}

function hideTyping() {
    const ind = document.getElementById('typing-indicator');
    if (ind) ind.remove();
    clearTimeout(typingTimeout);
}

function appendMessage(m, isAdmin) {
    const box = document.getElementById('chat-box');
    if (box.querySelector('.fa-envelope-open')) box.innerHTML = '';
    const time = (m.date || new Date().toISOString()).substring(0, 16).replace('T', ' ');
    let html = '';

    if (isAdmin) {
        html = '<div class="flex justify-start gap-3 items-end mb-3"><div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs shrink-0"><i class="fas fa-headset"></i></div><div class="max-w-lg bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3"><div class="text-xs font-semibold text-emerald-600 mb-1">Support</div><p class="text-sm">' + escHtml(m.contenu || '') + '</p><p class="text-xs text-base-content/40 mt-1">' + time + '</p></div></div>';
    } else {
        html = '<div class="flex justify-end gap-3 items-end mb-3"><div class="max-w-lg bg-white border border-base-300 rounded-2xl px-4 py-3"><div class="text-xs font-semibold text-slate-500 mb-1">Vous</div><p class="text-sm">' + escHtml(m.contenu || '') + '</p><p class="text-xs text-base-content/40 mt-1 text-right">' + time + '</p></div><div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs shrink-0"><i class="fas fa-user"></i></div></div>';
    }

    box.insertAdjacentHTML('beforeend', html);
    box.scrollTop = box.scrollHeight;
}

function renderMessages(msgs) {
    const box = document.getElementById('chat-box');
    if (!msgs || !msgs.length) {
        box.innerHTML = '<div class="p-12 text-center text-base-content/40"><i class="fas fa-envelope-open text-5xl mb-4"></i><p>Aucun message pour le moment.</p></div>';
        return;
    }
    box.innerHTML = '';
    msgs.forEach(m => appendMessage(m, m.is_admin === true));
}

function loadMessages() {
    if (!_token || !_userId) {
        document.getElementById('loading-msg').textContent = 'Connectez-vous pour voir vos messages.';
        return;
    }
    fetch('/api/messages/user/' + _userId, {
        headers: { 'Authorization': 'Bearer ' + _token }
    }).then(r => r.json()).then(data => {
        renderMessages(Array.isArray(data.data || data) ? (data.data || data) : []);
    }).catch(() => {
        document.getElementById('loading-msg').textContent = 'Impossible de charger les messages.';
    });
}

function sendMessage() {
    const input = document.getElementById('msg-input');
    const errEl = document.getElementById('msg-error');
    const contenu = input.value.trim();
    errEl.classList.add('hidden');
    if (!contenu) return;
    if (!_token) { errEl.textContent = 'Vous devez etre connecte.'; errEl.classList.remove('hidden'); return; }

    fetch('/api/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _token },
        body: JSON.stringify({ contenu: contenu })
    }).then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(() => {
        input.value = '';
        const msgData = { type: 'message', contenu: contenu, is_admin: false, user_id: _userId, date: new Date().toISOString() };
        appendMessage(msgData, false);
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(msgData));
        }
    }).catch(err => {
        errEl.textContent = 'Erreur: ' + err.message;
        errEl.classList.remove('hidden');
    });
}

document.getElementById('msg-input').addEventListener('input', function() {
    if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type: 'typing', is_admin: false, user_id: _userId }));
    }
});

document.getElementById('msg-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

loadMessages();
initWS();

async function uploadFile(input) {
    if (!input.files.length) return;
    let file = input.files[0];
    let fd = new FormData();
    fd.append("file", file);
    try {
        let res = await fetch("/api/messages/upload", {
            method: "POST",
            headers: {"Authorization": "Bearer " + token},
            body: fd
        });
        let data = await res.json();
        if (data.url) {
            let textInput = input.nextElementSibling;
            let oldVal = textInput.value;
            textInput.value = "[IMG]" + data.url + "[/IMG]";
            sendMessage();
            setTimeout(() => { textInput.value = oldVal; }, 50);
        }
    } catch (e) {}
    input.value = "";
}


let typingTimeout;
function notifyTyping() {
    if(!ws || ws.readyState !== WebSocket.OPEN) return;
    ws.send(JSON.stringify({type: "typing", recipient_id: parseInt(currentRecipientId)}));
}

function handleSocketMessage(e) {
    const data = JSON.parse(e.data);
    if(data.type === "typing" && data.sender_id == currentRecipientId) {
        const indicator = document.getElementById('typing-indicator');
        indicator.classList.remove('opacity-0');
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => indicator.classList.add('opacity-0'), 2000);
    } else if(data.type === "message") {
        appendMessage(data);
        const container = document.getElementById('messages-container');
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    }
}

</script>

<script>
window.typingTimeout = null;
window.notifyTyping = function() {
    if(typeof ws === 'undefined' || ws.readyState !== WebSocket.OPEN) return;
    ws.send(JSON.stringify({type: "typing", recipient_id: parseInt(currentRecipientId)}));
};
window.uploadFile = async function(input) {
    if (!input.files.length) return;
    let fd = new FormData();
    fd.append("file", input.files[0]);
    try {
        let res = await fetch("/api/messages/upload", {
            method: "POST",
            headers: {"Authorization": "Bearer " + token},
            body: fd
        });
        let data = await res.json();
        if (data.url) {
            const msgInput = document.getElementById('message-input');
            msgInput.value = "[IMG]" + data.url + "[/IMG]";
            window.sendMessage();
        }
    } catch (e) { console.error(e); }
    input.value = "";
};
</script>
