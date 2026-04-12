<?php $token = $_SESSION['user']['token'] ?? ''; ?>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Messagerie interne</h2>
        <p class="text-slate-500">Gérez les conversations avec les utilisateurs</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="toggleTheme()" id="theme-btn" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition text-slate-600" title="Changer le thème">
            <i class="fas fa-moon" id="theme-icon"></i>
        </button>
        <button type="button" onclick="this.nextElementSibling.click()" class="p-2 text-slate-400 hover:text-emerald-500 transition"><i class="fas fa-paperclip"></i></button>
<input type="file" class="hidden" accept="image/*" onchange="uploadFile(this)">
<input type="text" id="search-conv" oninput="filterConversations()" placeholder="Rechercher un utilisateur..."
            class="border border-slate-200 rounded-lg px-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-emerald-300">
    </div>
</div>

<div id="msg-wrapper" class="flex gap-0 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" style="height:620px;">

    <div class="w-72 shrink-0 border-r border-slate-200 flex flex-col" id="conv-sidebar">
        <div class="p-3 border-b border-slate-100 bg-slate-50">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Conversations</p>
        </div>
        <div class="flex-1 overflow-y-auto" id="conv-list">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                <div class="conv-item px-4 py-3 hover:bg-slate-50 cursor-pointer border-b border-slate-50 transition-colors"
                     data-id="<?= htmlspecialchars($msg['id_utilisateur'] ?? '') ?>"
                     data-nom="<?= htmlspecialchars(($msg['prenom'] ?? '') . ' ' . ($msg['nom'] ?? '')) ?>"
                     data-email="<?= htmlspecialchars($msg['email'] ?? '') ?>"
                     onclick="selectConversation(this)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                            <?= strtoupper(substr($msg['prenom'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-sm text-slate-800 truncate"><?= htmlspecialchars(($msg['prenom'] ?? '') . ' ' . ($msg['nom'] ?? '')) ?></div>
                            <div class="text-xs text-slate-400 truncate"><?= htmlspecialchars(substr($msg['dernier_message'] ?? $msg['contenu'] ?? '', 0, 40)) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center text-slate-400 text-sm">Aucune conversation.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-w-0">
        <div class="p-4 border-b border-slate-200 flex items-center gap-3 bg-slate-50" id="chat-header">
            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-400 font-bold" id="chat-avatar">?</div>   
            <div>
                <p class="font-semibold text-slate-800" id="chat-nom">Sélectionnez une conversation</p>
                <p class="text-xs text-slate-400" id="chat-email"></p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="chat-messages">
            <div class="flex items-center justify-center h-full">
                <div class="text-center text-slate-400">
                    <i class="fas fa-comments text-4xl mb-3 text-slate-200"></i>
                    <p class="text-sm">Cliquez sur une conversation pour commencer</p>
                </div>
            </div>
        </div>

        
<div class="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 transition-colors duration-300">
    <div id="typing-indicator" class="text-[10px] text-slate-400 italic mb-1 h-4 opacity-0 transition-opacity duration-300">Quelqu'un écrit...</div>
    <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800 p-2 rounded-2xl border border-slate-200 dark:border-slate-700 focus-within:border-emerald-500 transition-all">
        <button type="button" onclick="document.getElementById('file-input').click()" class="p-2 text-slate-400 hover:text-emerald-500 transition-colors">
            <i class="fas fa-paperclip text-lg"></i>
        </button>
        <input type="file" id="file-input" class="hidden" accept="image/*" onchange="uploadFile(this)">
        <input type="text" id="message-input" oninput="notifyTyping()" onkeypress="if(event.key==='Enter') sendMessage()" placeholder="Écrivez votre message..." class="flex-1 bg-transparent border-none focus:ring-0 text-sm text-slate-700 dark:text-slate-100 placeholder-slate-400">
        <button onclick="sendMessage()" class="bg-emerald-500 hover:bg-emerald-600 text-white p-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition-all active:scale-95">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>
</div>
</div>

<script>
const _adminToken = '<?= htmlspecialchars($token) ?>';
let _currentUserId = null;
let _darkMode = false;
let ws = null;
let typingTimeout = null;

function initWS() {
    if (!_adminToken) return;
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    ws = new WebSocket(protocol + '//' + window.location.host + '/api/ws?token=' + _adminToken);

    ws.onmessage = function(e) {
        try {
            const data = JSON.parse(e.data);
            if (!data.is_admin && parseInt(data.user_id) === parseInt(_currentUserId)) {
                if (data.type === 'typing') {
                    showTyping();
                } else if (data.type === 'message') {
                    hideTyping();
                    appendMessage(data, false);
                }
            }
        } catch(err) {}
    };
}

function showTyping() {
    let el = document.getElementById('typing-indicator');
    if (!el) {
        const box = document.getElementById('chat-messages');
        if (box.querySelector('.fa-comments')) box.innerHTML = '';
        box.insertAdjacentHTML('beforeend', '<div id="typing-indicator" class="flex justify-start"><div class="max-w-sm bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-2.5 shadow-sm"><p class="text-sm italic text-slate-500">L\'utilisateur est en train d\'écrire...</p></div></div>');
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

function filterConversations() {
    const q = document.getElementById('search-conv').value.toLowerCase();
    document.querySelectorAll('.conv-item').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function selectConversation(el) {
    document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('bg-emerald-50', 'border-l-4', 'border-emerald-500'));
    el.classList.add('bg-emerald-50', 'border-l-4', 'border-emerald-500');
    _currentUserId = el.dataset.id;
    const nom = el.dataset.nom;
    const email = el.dataset.email;
    document.getElementById('chat-nom').textContent = nom;
    document.getElementById('chat-email').textContent = email;
    document.getElementById('chat-avatar').textContent = nom.trim().charAt(0).toUpperCase();
    loadMessages(_currentUserId);
}

function loadMessages(userId) {
    const el = document.getElementById('chat-messages');
    el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>';
    fetch('/api/messages/user/' + userId, {
        headers: {'Authorization': 'Bearer ' + _adminToken}
    }).then(r => r.json()).then(data => {
        renderMessages(data.data || data || []);
    }).catch(() => {
        el.innerHTML = '<div class="text-center text-rose-400 text-sm py-8">Impossible de charger les messages.</div>';
    });
}

function appendMessage(m, isAdmin) {
    const el = document.getElementById('chat-messages');
    if (el.querySelector('.fa-comments')) el.innerHTML = '';
    const time = (m.date || new Date().toISOString()).substring(0, 16).replace('T', ' ');
    let html = '';

    if (isAdmin) {
        html = `<div class="flex justify-end">
            <div class="max-w-sm bg-emerald-500 text-white rounded-2xl rounded-tr-sm px-4 py-2.5 shadow-sm">
                <div class="text-xs font-semibold text-emerald-100 mb-1 flex items-center gap-1"><i class="fas fa-headset text-xs"></i> Support</div>
                <p class="text-sm leading-relaxed">${escHtml(m.contenu || '')}</p>
                <p class="text-[10px] text-emerald-100 mt-1 text-right">${time}</p>
            </div>
        </div>`;
    } else {
        html = `<div class="flex justify-start">
            <div class="max-w-sm bg-slate-100 rounded-2xl rounded-tl-sm px-4 py-2.5 shadow-sm">
                <p class="text-sm text-slate-800 leading-relaxed">${escHtml(m.contenu || '')}</p>
                <p class="text-[10px] text-slate-400 mt-1">${time}</p>
            </div>
        </div>`;
    }

    el.insertAdjacentHTML('beforeend', html);
    el.scrollTop = el.scrollHeight;
}

function renderMessages(msgs) {
    const el = document.getElementById('chat-messages');
    if (!msgs.length) {
        el.innerHTML = '<div class="text-center text-slate-400 text-sm py-8">Aucun message dans cette conversation.</div>';
        return;
    }
    el.innerHTML = '';
    msgs.forEach(m => appendMessage(m, m.is_admin === true));
}

function escHtml(s) {
    let r = String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
    return r.replace(/\[IMG\](.*?)\[\/IMG\]/g, '<a href=\"$1\" target=\"_blank\"><img src=\"$1\" class=\"max-w-xs rounded-lg mt-2 border border-slate-200\"></a>');
}

function sendAdminReply() {
    const input = document.getElementById('reply-input');
    const contenu = input.value.trim();
    if (!contenu || !_currentUserId) return;
    const btn = document.getElementById('btn-send');
    btn.disabled = true;

    fetch('/api/admin/messages/', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + _adminToken},
        body: JSON.stringify({id_utilisateur: parseInt(_currentUserId), contenu: contenu})
    }).then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    }).then(() => {
        input.value = '';
        const msgData = { type: 'message', contenu: contenu, is_admin: true, user_id: parseInt(_currentUserId), date: new Date().toISOString() };
        appendMessage(msgData, true);
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify(msgData));
        }
    }).catch(err => {
        alert('Erreur lors de l\'envoi : ' + err.message);
    }).finally(() => {
        btn.disabled = false;
    });
}

document.getElementById('reply-input').addEventListener('input', function() {
    if (ws && ws.readyState === WebSocket.OPEN && _currentUserId) {
        ws.send(JSON.stringify({ type: 'typing', is_admin: true, user_id: parseInt(_currentUserId) }));
    }
});

function toggleTheme() {
    _darkMode = !_darkMode;
    const wrapper = document.getElementById('msg-wrapper');
    const icon = document.getElementById('theme-icon');
    if (_darkMode) {
        wrapper.style.background = '#1e293b';
        wrapper.style.color = '#cbd5e1';
        document.getElementById('conv-sidebar').style.borderColor = '#334155';
        document.querySelectorAll('#conv-list .conv-item').forEach(el => el.style.borderColor = '#1e293b');
        icon.className = 'fas fa-sun';
    } else {
        wrapper.style.background = '';
        wrapper.style.color = '';
        document.getElementById('conv-sidebar').style.borderColor = '';
        icon.className = 'fas fa-moon';
    }
}

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

</script>
