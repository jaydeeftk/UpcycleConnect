<section class="max-w-6xl mx-auto px-6 lg:px-10 py-16">

    <div class="mb-10 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-blue-600 uppercase tracking-wide"><?= t('planning_my_space', 'Mon espace') ?></span>
            </div>
            <h1 class="text-3xl font-bold"><?= t('planning_title', 'Mon Planning') ?></h1>
            <p class="text-base-content/60 mt-2"><?= t('planning_subtitle', 'Retrouvez tous vos cours, événements et activités en cours et à venir.') ?></p>
        </div>
        <div class="flex flex-col items-end gap-3">
            <button type="button" onclick="document.getElementById('modal-add-planning').classList.remove('hidden')"
                    class="bg-blue-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-blue-600 transition">
                <i class="fas fa-plus mr-2"></i><?= t('planning_btn_add', 'Ajouter au planning') ?>
            </button>
            <div class="tabs tabs-boxed bg-base-100 p-1 rounded-2xl shadow-sm">
                <button onclick="setVue('jour')" id="tab-jour" class="tab"><?= t('planning_tab_day', 'Jour') ?></button>
                <button onclick="setVue('semaine')" id="tab-semaine" class="tab tab-active"><?= t('planning_tab_week', 'Semaine') ?></button>
                <button onclick="setVue('mois')" id="tab-mois" class="tab"><?= t('planning_tab_month', 'Mois') ?></button>
            </div>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6"><i class="fas fa-exclamation-triangle mr-2"></i><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="grid lg:grid-cols-4 gap-8">

        <aside class="lg:col-span-1 space-y-6">
            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4"><?= t('planning_summary', 'Résumé') ?></h2>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl">
                        <i class="fas fa-graduation-cap text-purple-500"></i>
                        <div class="flex-1 text-sm"><?= t('planning_formations', 'Formations') ?></div>
                        <span class="font-bold text-purple-500"><?= count($formations ?? []) ?></span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                        <i class="fas fa-calendar-check text-blue-500"></i>
                        <div class="flex-1 text-sm"><?= t('planning_evenements', 'Événements') ?></div>
                        <span class="font-bold text-blue-500"><?= count($evenements ?? []) ?></span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl">
                        <i class="fas fa-bookmark text-emerald-500"></i>
                        <div class="flex-1 text-sm"><?= t('planning_libres', 'Entrées personnelles') ?></div>
                        <span class="font-bold text-emerald-500"><?= count($libres ?? []) ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 rounded-2xl shadow-sm p-5">
                <h2 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4"><?= t('planning_next_appointment', 'Prochain rendez-vous') ?></h2>
                <div id="prochain-rdv" class="bg-blue-50 rounded-xl p-4">
                    <div class="text-sm text-base-content/50"><?= t('planning_loading', 'Chargement...') ?></div>
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
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-purple-200 inline-block"></span> <?= t('planning_type_formation', 'Formation') ?></span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200 inline-block"></span> <?= t('planning_type_evenement', 'Événement') ?></span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-200 inline-block"></span> <?= t('planning_type_libre', 'Entrée personnelle') ?></span>
            </div>

            <?php if (!empty($libres)): ?>
            <div class="bg-base-100 rounded-2xl shadow-sm p-5 mt-6">
                <h3 class="font-semibold text-sm uppercase tracking-wide text-base-content/50 mb-4"><?= t('planning_libres_list', 'Mes entrées personnelles') ?></h3>
                <ul class="space-y-2">
                    <?php foreach ($libres as $l): ?>
                        <li class="flex items-center justify-between gap-4 p-3 bg-emerald-50 rounded-xl">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium truncate"><?= htmlspecialchars($l['titre'] ?? '') ?></div>
                                <div class="text-xs text-base-content/60"><?= formatDate($l['date'] ?? '', true) ?><?= !empty($l['lieu']) ? ' · ' . htmlspecialchars($l['lieu']) : '' ?></div>
                            </div>
                            <form method="POST" action="/planning/<?= (int)($l['id'] ?? 0) ?>/supprimer"
                                  onsubmit="return ucConfirm(this, '<?= t('planning_libre_confirm_delete', 'Supprimer cette entrée ?') ?>')">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="<?= t('planning_libre_delete', 'Supprimer') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
const ITEMS = <?= json_encode(array_merge(
    array_map(fn($f) => [
        'id'    => $f['id'] ?? 0,
        'titre' => $f['titre'] ?? '',
        'date'  => $f['date'] ?? '',
        'date_fin' => $f['date_fin'] ?? '',
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
        'duree' => $e['duree'] ?? 0,
        'type'  => 'evenement',
        'url'   => '/evenements/' . ($e['id'] ?? 0),
    ], $evenements ?? []),
    array_map(fn($l) => [
        'id'    => $l['id'] ?? 0,
        'titre' => $l['titre'] ?? '',
        'date'  => $l['date'] ?? '',
        'lieu'  => $l['lieu'] ?? '',
        'duree' => $l['duree'] ?? 0,
        'type'  => 'libre',
        'url'   => '#',
    ], $libres ?? [])
)) ?>;

let vue = 'semaine';
let dateRef = new Date();
dateRef.setHours(0,0,0,0);

const MOIS_FR = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
const JOURS_COURTS = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
const JOURS_LONGS  = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];

// Plage horaire affichee dans la vue "Semaine" (grille avec heures, comme un vrai
// calendrier) : un creneau de 14h a 17h occupe visuellement 3 lignes de la grille.
const SEMAINE_HEURE_DEBUT = 9;
const SEMAINE_HEURE_FIN   = 19;
const SEMAINE_ROW_H       = 48;

function itemDate(item) {
    if (!item.date) return null;
    const m = String(item.date).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!m) return null;
    return new Date(+m[1], +m[2] - 1, +m[3], +(m[4] || 0), +(m[5] || 0));
}

function sameDay(a, b) {
    return a.getFullYear() === b.getFullYear() &&
           a.getMonth() === b.getMonth() &&
           a.getDate() === b.getDate();
}

// Un item avec date_fin (formation sur plusieurs jours) doit apparaitre sur
// chaque jour de la grille compris entre sa date de debut et sa date de fin.
function itemCouvreJour(item, jour) {
    const debut = itemDate(item);
    if (!debut) return false;
    if (!item.date_fin) return sameDay(debut, jour);
    const m = String(item.date_fin).match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return sameDay(debut, jour);
    const fin       = new Date(+m[1], +m[2] - 1, +m[3]);
    const jourSeul  = new Date(jour.getFullYear(), jour.getMonth(), jour.getDate());
    const debutSeul = new Date(debut.getFullYear(), debut.getMonth(), debut.getDate());
    return jourSeul >= debutSeul && jourSeul <= fin;
}

function formatDate(d) {
    return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + d.getFullYear();
}

function formatHeure(d) {
    return String(d.getHours()).padStart(2,'0') + 'h' + String(d.getMinutes()).padStart(2,'0');
}

function trierParHeure(items) {
    return [...items].sort((a, b) => {
        const da = itemDate(a), db = itemDate(b);
        if (!da || !db) return 0;
        return da - db;
    });
}

function colorClass(type) {
    if (type === 'formation') return 'bg-purple-100 text-purple-700 border-purple-300';
    if (type === 'libre')     return 'bg-emerald-100 text-emerald-700 border-emerald-300';
    return 'bg-blue-100 text-blue-700 border-blue-300';
}

function formatPlageHoraire(item) {
    const d = itemDate(item);
    if (!d) return '';
    let label = formatHeure(d);
    if (item.duree && item.duree > 0) {
        const fin = new Date(d);
        fin.setHours(fin.getHours() + item.duree);
        label += ' - ' + formatHeure(fin);
    }
    return label;
}

function cardHtml(item) {
    const heure = formatPlageHoraire(item);
    return `<a href="${item.url}" class="${colorClass(item.type)} border rounded-lg p-2 text-xs cursor-pointer hover:opacity-80 transition block mb-1">
        <div class="font-semibold">${heure}</div>
        <div class="mt-0.5 leading-tight">${item.titre}</div>
        <div class="mt-1 opacity-70"><i class="fas fa-map-marker-alt mr-1"></i>${item.lieu}</div>
    </a>`;
}

// Bloc positionne en absolu dans la grille horaire de la vue "Semaine" : le top
// et la hauteur sont calcules a partir de l'heure de debut et de la duree, pour
// qu'un creneau de 14h a 17h occupe visuellement l'espace entre ces deux heures.
function carteSemainePositionnee(item) {
    const d = itemDate(item);
    const debutH = d ? Math.max(SEMAINE_HEURE_DEBUT, Math.min(d.getHours() + d.getMinutes() / 60, SEMAINE_HEURE_FIN)) : SEMAINE_HEURE_DEBUT;
    const dureeH = item.duree && item.duree > 0 ? item.duree : 1;
    const top    = (debutH - SEMAINE_HEURE_DEBUT) * SEMAINE_ROW_H;
    const height = Math.max(dureeH * SEMAINE_ROW_H, 22);
    const heure  = formatPlageHoraire(item);
    return `<a href="${item.url}" class="${colorClass(item.type)} border rounded-lg p-1 text-xs cursor-pointer hover:opacity-80 transition absolute left-1 right-1 overflow-hidden" style="top:${top}px;height:${height}px;">
        <div class="font-semibold leading-tight">${heure}</div>
        <div class="leading-tight">${item.titre}</div>
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

    const itemsDuJour = ITEMS.filter(i => itemCouvreJour(i, dateRef));
    const heures = Array.from({length: 16}, (_, i) => i + 6);

    const sansHeure = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() < 6; });

    let html = '<div class="bg-base-100 rounded-2xl shadow-sm p-6 space-y-3">';

    if (sansHeure.length > 0) {
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-base-content/40 w-12 pt-1 flex-shrink-0"><?= t('planning_all_day', 'Journée') ?></span>
            <div class="flex-1 border-t border-base-200 pt-1 min-h-8">
                ${trierParHeure(sansHeure).map(cardHtml).join('')}
            </div>
        </div>`;
    }

    for (const h of heures) {
        const label = String(h).padStart(2,'0') + 'h00';
        const items = itemsDuJour.filter(i => { const d = itemDate(i); return d && d.getHours() === h; });
        html += `<div class="flex gap-4 items-start">
            <span class="text-xs text-base-content/40 w-12 pt-1 flex-shrink-0">${label}</span>
            <div class="flex-1 border-t border-base-200 pt-1 min-h-8">
                ${trierParHeure(items).map(cardHtml).join('')}
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
        '<?= t('planning_week_of', 'Semaine du') ?> ' + lundi.getDate() + ' <?= t('planning_week_to', 'au') ?> ' + dimanche.getDate() + ' ' + MOIS_FR[dimanche.getMonth()] + ' ' + dimanche.getFullYear();

    const today = new Date(); today.setHours(0,0,0,0);
    const heuresAxe = Array.from({length: SEMAINE_HEURE_FIN - SEMAINE_HEURE_DEBUT}, (_, i) => SEMAINE_HEURE_DEBUT + i);
    const hauteurTotale = heuresAxe.length * SEMAINE_ROW_H;

    let html = '<div class="bg-base-100 rounded-2xl shadow-sm overflow-hidden">';
    html += '<div class="grid" style="grid-template-columns: 48px repeat(7, 1fr);">';
    html += '<div class="border-b border-base-300"></div>';
    for (const d of jours) {
        const isToday = sameDay(d, today);
        html += `<div class="p-3 text-center text-sm border-b border-l border-base-300 ${isToday ? 'bg-primary/10 font-bold text-primary' : 'text-base-content/50'}">
            ${JOURS_COURTS[d.getDay()]} ${d.getDate()}
        </div>`;
    }
    html += '</div>';

    html += '<div class="grid" style="grid-template-columns: 48px repeat(7, 1fr);">';
    html += `<div style="height:${hauteurTotale}px;">`;
    for (const h of heuresAxe) {
        html += `<div class="text-xs text-base-content/40 text-right pr-1" style="height:${SEMAINE_ROW_H}px;">${String(h).padStart(2,'0')}h</div>`;
    }
    html += '</div>';
    for (const d of jours) {
        const items = ITEMS.filter(i => itemCouvreJour(i, d));
        html += `<div class="relative border-l border-base-300" style="height:${hauteurTotale}px;">`;
        for (let i = 0; i < heuresAxe.length; i++) {
            html += `<div class="absolute left-0 right-0 border-t border-base-200" style="top:${i * SEMAINE_ROW_H}px;"></div>`;
        }
        html += items.map(carteSemainePositionnee).join('');
        html += '</div>';
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
        const items = ITEMS.filter(i => itemCouvreJour(i, cur));
        html += `<div class="p-2 min-h-20 ${isToday ? 'bg-primary/5' : ''}">
            <span class="text-sm ${isToday ? 'font-bold text-primary' : 'text-base-content/60'}">${d}</span>
            ${trierParHeure(items).map(i => `<a href="${i.url}" class="${colorClass(i.type)} rounded text-xs p-1 mt-1 leading-tight block hover:opacity-80 transition">${formatPlageHoraire(i)} ${i.titre}</a>`).join('')}
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
        el.innerHTML = '<div class="text-sm text-base-content/50"><?= t('planning_no_upcoming', 'Aucun rendez-vous à venir.') ?></div>';
        return;
    }
    const p = futurs[0];
    const badgeLabel = p.type === 'formation'
        ? '<?= t('planning_type_formation', 'Formation') ?>'
        : (p.type === 'libre' ? '<?= t('planning_type_libre', 'Entrée personnelle') ?>' : '<?= t('planning_type_evenement', 'Événement') ?>');
    const badgeCls = p.type === 'formation' ? 'bg-purple-100 text-purple-700'
        : (p.type === 'libre' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700');
    const titreHtml = p.url && p.url !== '#'
        ? `<a href="${p.url}" class="font-semibold text-sm hover:underline block mb-1">${p.titre}</a>`
        : `<div class="font-semibold text-sm mb-1">${p.titre}</div>`;
    el.innerHTML = `
        <div class="badge badge-sm ${badgeCls} border-0 mb-2">${badgeLabel}</div>
        ${titreHtml}
        <div class="text-xs text-base-content/60 space-y-1">
            <div><i class="fas fa-calendar mr-1"></i>${formatDate(p._d)} · ${formatPlageHoraire(p)}</div>
            <div><i class="fas fa-map-marker-alt mr-1"></i>${p.lieu || '—'}</div>
        </div>`;
}

render();
</script>

<div id="modal-add-planning" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold"><?= t('planning_add_title', 'Ajouter au planning') ?></h3>
            <button type="button" onclick="document.getElementById('modal-add-planning').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="/planning/ajouter">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('planning_add_titre', 'Titre') ?> *</label>
                <input type="text" name="titre" required maxlength="150"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="<?= t('planning_add_titre_ph', 'Ex : Rendez-vous artisan') ?>">
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('planning_add_debut', 'Début') ?> *</label>
                    <input type="datetime-local" name="date_debut" required min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('planning_add_fin', 'Fin') ?></label>
                    <input type="datetime-local" name="date_fin" min="<?= dateProgrammationMin() ?>" max="<?= dateProgrammationMax() ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('planning_add_lieu', 'Lieu') ?></label>
                <input type="text" name="lieu" maxlength="150"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="<?= t('planning_add_lieu_ph', 'Ex : Paris 11ème') ?>">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><?= t('planning_add_description', 'Description') ?></label>
                <textarea name="description" rows="3" maxlength="255"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="<?= t('planning_add_description_ph', 'Notes...') ?>"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-add-planning').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"><?= t('planning_add_cancel', 'Annuler') ?></button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-save mr-2"></i><?= t('planning_add_submit', 'Ajouter') ?>
                </button>
            </div>
        </form>
    </div>
</div>