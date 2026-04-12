<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold">Planning Global</h2>
        <p class="text-gray-600">Vue d'ensemble des événements et formations</p>
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

        var eventsData = rawData.map(function(item) {
            var color = item.type === 'formation' ? '#3b82f6' : '#10b981';
            return {
                id: item.id,
                title: '[' + item.type.toUpperCase() + '] ' + item.titre,
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
                alert(info.event.title + '\nStatut: ' + info.event.extendedProps.statut + '\n\n' + info.event.extendedProps.description);
            }
        });
        calendar.render();
    });
</script>