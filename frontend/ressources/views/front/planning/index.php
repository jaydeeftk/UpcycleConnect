<section class="max-w-6xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">Mon espace</span>
            </div>
            <h1 class="text-3xl font-bold">Mon Planning</h1>
            <p class="text-base-content/60 mt-2">Retrouvez tous vos cours, événements et activités en cours et à venir.</p>
        </div>
        <div class="tabs tabs-boxed bg-base-100 p-1 rounded-2xl shadow-sm">
            <button onclick="setVue('jour')" id="tab-jour" class="tab">Jour</button>
            <button onclick="setVue('semaine')" id="tab-semaine" class="tab tab-active">Semaine</button>
            <button onclick="setVue('mois')" id="tab-mois" class="tab">Mois</button>
        </div>
    </div>

    <div class="grid lg:grid-cols-4 gap-8">

        <aside class="lg:col-span-1 space-y-6">
            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4">Résumé</h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl">
                        <i class="fas fa-graduation-cap text-purple-500"></i>
                        <div class="flex-1 text-sm">Formations</div>
                        <span class="font-bold text-purple-500"><?= count($formations ?? []) ?></span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                        <i class="fas fa-calendar-check text-blue-500"></i>
                        <div class="flex-1 text-sm">Événements</div>
                        <span class="font-bold text-blue-500"><?= count($evenements ?? []) ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4">Prochain rendez-vous</h2>
                <div id="prochain-rdv" class="bg-blue-50 rounded-xl p-4">
                    <div class="text-sm text-base-content/50">Chargement...</div>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-3">

            <div class="bg-base-100 rounded-2xl shadow-sm p-4 mb-6 flex items-center justify-between">
                <button onclick="naviguer(-1)" class="btn btn-ghost btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span id="periode-label" class="font-semibold"></span>
                <button onclick="naviguer(1)" class="btn btn-ghost btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div id="vue-container"></div>

            <div class="flex gap-4 mt-4 text-xs text-base-content/50">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-200 inline-block"></span> Formation</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200 inline-block"></span> Événement</span>
            </div>
        </div>
    </div>
</section>

<script>
const ITEMS = <?= json_encode(array_merge(
    array_map(fn($f) => [
        'id'    => $f['id'] ?? 0,
        'titre' => $f['titre'] ?? '',
        'date'  => $f['date'] ?? '',
        'lieu'  => $f['lieu'] ?? '',
        'duree' => $f['duree'] ?? 0,
        'type'  => 'formation',
        'url'   => '/formations/' . ($f['id'] ?? 0),
    ], $formations ?? []),
    array_map(fn($e) => [
        'id'    => $e['id'] ?? 0,
        'titre' => $e['titre'] ?? '',
        'date'  => $e['date'] ?? '',
        'lieu'  => $e['lieu'] ?? '',
        'duree' => 0,
        'type'  => 'evenement',
        'url'   => '/evenements/' . ($e['id'] ?? 0),
    ], $evenements ?? [])
)) ?>;

let vue = 'semaine';
let dateRef = new Date();
dateRef.setHours(0,0,0,0);

const MOIS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const JOURS_COURTS = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
const JOURS_LONGS  = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

function itemDate(item) {
    if (!item.date) return null;
    return new Date(item.date);
}

function sameDay(a, b) {
    return a.getFullYear() === b.getFullYear() &&
           a.getMonth() === b.getMonth() &&
           a.getDate() === b.getDate();
}

function formatDate(d) {
    return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
}

function formatHeure(d) {
    return String(d.getHours()).padStart(2,'0') + 'h' + String(d.getMinutes()).padStart(2,'0');
}

function colorClass(type) {
    return type === 'formation'
        ? 'bg-purple-100 text-purple-700 border-purple-300'
        : 'bg-blue-100 text-blue-700 border-blue-300';
}

function cardHtml(item) {
    const d = itemDate(item);
    const heure = d ? formatHeure(d) : '';
    return `<a href="${item.url}" class="${colorClass(item.type)} border rounded-lg p-2 text-xs cursor-pointer hover:opacity-80 transition block mb-1">
        <div class="font-semibold">${heure}</div>
        <div class="mt-0.5 leading-tight">${item.titre}</div>
        <div class="mt-1 opacity-70"><i class="fas fa-map-marker-alt mr-1"></i>${item.lieu}</div>
    </a>`;
}

function setVue(v) {
    vue = v;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
    document.getElementById('tab-' + v).classList.add('tab-active');
    render();
}

function naviguer(dir) {
    if (vue === 'jour')    dateRef.setDate(dateRef.getDate() + dir);
    if (vue === 'semaine') dateRef.setDate(dateRef.getDate() + dir * 7);
    if (vue === 'mois')    dateRef.setMonth(dateRef.getMonth() + dir);
    render();
}

function render() {
    if (vue === 'jour')    renderJour();
    if (vue === 'semaine') renderSemaine();
    if (vue === 'mois')    renderMois();
    renderProchain();
}

function renderJour() {
    const label = JOURS_LONGS[dateRef.getDay()] + ' ' + dateRef.getDate() + ' ' + MOIS_FR[dateRef.getMonth()] + ' ' + dateRef.getFullYear();
    document.getElementById('periode-label').textContent = label;

    const itemsDuJour = ITEMS.filter(i => { const d = itemDate(i); return d && sameDay(d, dateRef); });
    const heures = Array.from({length: 16}, (_, i) => i + 6);

    const sansHeure = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() < 6; });

    let html = '<div class="bg-base-100 rounded-2xl shadow-sm p-6 space-y-3">';

    if (sansHeure.length > 0) {
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-base-content/40 w-12 pt-1 flex-shrink-0">Journée</span>
            <div class="flex-1 border-t border-base-200 pt-1 min-h-8">
                ${sansHeure.map(cardHtml).join('')}
            </div>
        </div>`;
    }

    for (const h of heures) {
        const label = String(h).padStart(2,'0') + 'h00';
        const items = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() === h; });
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-base-content/40 w-12 pt-1 flex-shrink-0">${label}</span>
            <div class="flex-1 border-t border-base-200 pt-1 min-h-8">
                ${items.map(cardHtml).join('')}
            </div>
        </div>`;
    }
    html += '</div>';
    document.getElementById('vue-container').innerHTML = html;
}

function renderSemaine() {
    const lundi = new Date(dateRef);
    const jour = dateRef.getDay();
    const diff = jour === 0 ? -6 : 1 - jour;
    lundi.setDate(dateRef.getDate() + diff);

    const jours = Array.from({length: 7}, (_, i) => {
        const d = new Date(lundi);
        d.setDate(lundi.getDate() + i);
        return d;
    });

    const dimanche = jours[6];
    document.getElementById('periode-label').textContent =
        'Semaine du ' + lundi.getDate() + ' au ' + dimanche.getDate() + ' ' + MOIS_FR[dimanche.getMonth()] + ' ' + dimanche.getFullYear();

    const today = new Date(); today.setHours(0,0,0,0);

    let html = '<div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">';
    html += '<div class="grid grid-cols-7 border-b border-base-300">';
    for (const d of jours) {
        const isToday = sameDay(d, today);
        html += `<div class="p-3 text-center text-sm ${isToday ? 'bg-primary/10 font-bold text-primary' : 'text-base-content/50'}">
            ${JOURS_COURTS[d.getDay()]} ${d.getDate()}
        </div>`;
    }
    html += '</div><div class="grid grid-cols-7 min-h-64 divide-x divide-base-300">';
    for (const d of jours) {
        const items = ITEMS.filter(i => { const id = itemDate(i); return id && sameDay(id, d); });
        html += `<div class="p-2 min-h-32">${items.map(cardHtml).join('')}</div>`;
    }
    html += '</div></div>';
    document.getElementById('vue-container').innerHTML = html;
}

function renderMois() {
    const annee = dateRef.getFullYear();
    const mois  = dateRef.getMonth();
    document.getElementById('periode-label').textContent = MOIS_FR[mois] + ' ' + annee;

    const premier = new Date(annee, mois, 1);
    const dernier = new Date(annee, mois + 1, 0);
    const today   = new Date(); today.setHours(0,0,0,0);

    let joursVides = premier.getDay() === 0 ? 6 : premier.getDay() - 1;

    let html = '<div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">';
    html += '<div class="grid grid-cols-7 border-b border-base-300">';
    for (const j of ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim']) {
        html += `<div class="p-3 text-center text-xs font-semibold text-base-content/50">${j}</div>`;
    }
    html += '</div><div class="grid grid-cols-7 divide-x divide-y divide-base-300">';
    for (let i = 0; i < joursVides; i++) {
        html += '<div class="p-2 min-h-20 bg-base-200/50"></div>';
    }
    for (let d = 1; d <= dernier.getDate(); d++) {
        const cur = new Date(annee, mois, d);
        const isToday = sameDay(cur, today);
        const items = ITEMS.filter(i => { const id = itemDate(i); return id && sameDay(id, cur); });
        html += `<div class="p-2 min-h-20 ${isToday ? 'bg-primary/5' : ''}">
            <span class="text-sm ${isToday ? 'font-bold text-primary' : 'text-base-content/60'}">${d}</span>
            ${items.map(i => `<a href="${i.url}" class="${colorClass(i.type)} rounded text-xs p-1 mt-1 leading-tight block hover:opacity-80 transition">${i.titre}</a>`).join('')}
        </div>`;
    }
    html += '</div></div>';
    document.getElementById('vue-container').innerHTML = html;
}

function renderProchain() {
    const now = new Date();
    const futurs = ITEMS
        .map(i => ({ ...i, _d: itemDate(i) }))
        .filter(i => i._d && i._d >= now)
        .sort((a, b) => a._d - b._d);

    const el = document.getElementById('prochain-rdv');
    if (!futurs.length) {
        el.innerHTML = '<div class="text-sm text-base-content/50">Aucun rendez-vous à venir.</div>';
        return;
    }
    const p = futurs[0];
    el.innerHTML = `
        <div class="badge badge-sm ${p.type === 'formation' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'} border-0 mb-2">${p.type === 'formation' ? 'Formation' : 'Événement'}</div>
        <a href="${p.url}" class="font-semibold text-sm hover:underline block mb-1">${p.titre}</a>
        <div class="text-xs text-base-content/60 space-y-1">
            <div><i class="fas fa-calendar mr-1"></i>${formatDate(p._d)}</div>
            <div><i class="fas fa-map-marker-alt mr-1"></i>${p.lieu}</div>
        </div>`;
}

render();
</script>