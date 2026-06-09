<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold"><?= t('adm_planning_title', 'Planning Global') ?></h2>
        <p class="text-gray-600"><?= t('adm_planning_subtitle', 'Vue d\'ensemble des événements et formations') ?></p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div id="calendar"></div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var rawData = <?= json_encode($planning ?? []) ?>;

        var TYPE_LBL = {evenement:'Événement', formation:'Formation', atelier:'Atelier'};
        var STATUT_LBL = {a_venir:'À venir', en_attente:'En attente', validee:'Validée', refusee:'Refusée', en_cours:'En cours', termine:'Terminé', actif:'Actif'};

        var eventsData = rawData.map(function(item) {
            var color = item.type === 'formation' ? '#3b82f6' : '#10b981';
            return {
                id: item.id,
                title: '[' + (TYPE_LBL[item.type] || item.type) + '] ' + item.titre,
                start: item.date,
                backgroundColor: color,
                borderColor: color,
                extendedProps: {
                    description: item.description,
                    statut: item.statut
                }
            };
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: eventsData,
            eventClick: function(info) {
                toast(info.event.title + '\n<?= t('adm_planning_js_status', 'Statut: ') ?>' + (STATUT_LBL[info.event.extendedProps.statut] || info.event.extendedProps.statut) + '\n\n' + info.event.extendedProps.description);
            }
        });
        calendar.render();
    });
</script>