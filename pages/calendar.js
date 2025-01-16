document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'fetch_events.php',
        // Configuration pour masquer samedi et dimanche
        hiddenDays: [0, 6], // 0 = dimanche, 6 = samedi
        // Configuration des jours ouvrables uniquement
                weekends: false,
        slotEventOverlap: false,
        allDaySlot: false,
        eventMaxStack: 1,
        dayMaxEvents: true, // Permet l'affichage du bouton +more
        dayMaxEventRows: 4, // Limite le nombre de lignes avant de grouper
        // Modification des horaires pour les cours du soir
        slotMinTime: '08:30:00',
        slotMaxTime: '19:00:00',
        eventMaxStack: 1,
        dayMaxEvents: false,
        // Configuration pour contraindre les événements dans la plage horaire
        eventConstraint: {
            startTime: '08:30:00',
            endTime: '19:00:00'
        },
        eventContent: function (arg) {
            return {
                html: `
                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 4px;">
                        <div>${arg.timeText}</div>
                        <div><strong>${arg.event.extendedProps.matiere}</strong></div>
                        <div>${arg.event.extendedProps.professeur}</div>
                        <div>${arg.event.extendedProps.classe}</div>
                        <div>${arg.event.extendedProps.salle}</div>
                    </div>
                `
            };
        },
        eventDidMount: function(info) {
            let matiere = info.event.extendedProps.matiere;
            const colors = getColorForMatiere(matiere);
            info.el.style.backgroundColor = colors.background;
            info.el.style.borderColor = colors.border;
            info.el.style.border = '1px solid';
            info.el.style.margin = '2px';
            info.el.style.width = '95%';
        },
        moreLinkContent: function(arg) {
            return '+' + arg.num; // Personnalise le texte du lien "more" pour afficher "+10" au lieu de "10 more"
        },
        editable: true,
        eventDrop: function(info) {
            updateEvent(info.event);
        },
        eventResize: function(info) {
            updateEvent(info.event);
        },
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            showEventDetails(info);
        },
        eventMouseEnter: function (info) {
            info.el.style.cursor = 'pointer';
        },
        eventMouseLeave: function (info) {
            info.el.style.cursor = '';
        }
    });
    
    calendar.render();

// Ajout d'une fonction de formatage de date
function formatDateTime(date) {
    if (!date) return 'Non spécifié';
    return new Date(date).toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Fonction mise à jour pour la gestion des événements

function updateEvent(event) {
    // Debug des données envoyées
    console.log('Event data:', {
        id: event.id,
        start: event.start.toISOString(),
        end: event.end ? event.end.toISOString() : null
    });

    fetch('update_schedule.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: event.id,
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : null
        }),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Réponse serveur:', data);
        if (data.status === 'success') {
            console.log('Événement mis à jour avec succès');
            calendar.refetchEvents();
        } else {
            console.error('Erreur:', data.message);
            event.revert();
        }
    })
    .catch(error => {
        console.error('Erreur lors de la mise à jour:', error);
        event.revert();
    });
}


    // Fonction de mise à jour du calendrier selon les filtres
    function updateCalendar() {
        let professeur = document.getElementById('teacher-select').value;
        let classe = document.getElementById('group-select').value;
        let salle = document.getElementById('room-select').value;
        
        let url = 'fetch_events.php?';
        if (professeur) url += `professeur=${encodeURIComponent(professeur)}&`;
        if (classe) url += `classe=${encodeURIComponent(classe)}&`;
        if (salle) url += `salle=${encodeURIComponent(salle)}`;
        
        calendar.removeAllEvents();
        calendar.setOption('events', url);
        calendar.refetchEvents();
    }

    // Ajouter les écouteurs d'événements pour les filtres
    document.getElementById('teacher-select').addEventListener('change', updateCalendar);
    document.getElementById('group-select').addEventListener('change', updateCalendar);
    document.getElementById('room-select').addEventListener('change', updateCalendar);
});

// Fonction pour obtenir les couleurs selon la matière
function getColorForMatiere(matiere) {
    const colors = {
        'Anglais': { background: '#87CEEB', border: '#1E90FF' },
        'Arabe': { background: '#FFD700', border: '#DAA520' },
        'Français': { background: '#FFC0CB', border: '#FF69B4' },
        'Maths': { background: '#ADD8E6', border: '#1E90FF' },
        'SVT': { background: '#90EE90', border: '#32CD32' },
        'Physique': { background: '#FFA07A', border: '#FF6347' },
        'Philosophie': { background: '#D8BFD8', border: '#8B008B' },
        'Histoire-Géographie': { background: '#FFDAB9', border: '#CD853F' }
    };
    
    return colors[matiere] || { background: '#ccc', border: '#333' };
}

// Fonction mise à jour pour l'affichage des détails
function showEventDetails(info) {
    document.querySelector('#event-details-modal #title').textContent = info.event.title;
    document.querySelector('#event-details-modal #description').textContent = 
        info.event.extendedProps.description || 'Non spécifié';
    document.querySelector('#event-details-modal #salle').textContent = 
        info.event.extendedProps.salle || 'Non spécifié';
    document.querySelector('#event-details-modal #professeur').textContent = 
        info.event.extendedProps.professeur || 'Non spécifié';
    document.querySelector('#event-details-modal #start').textContent = 
        new Date(info.event.start).toLocaleString('fr-FR');
    document.querySelector('#event-details-modal #end').textContent = 
        info.event.end ? new Date(info.event.end).toLocaleString('fr-FR') : 'Non spécifié';

    // Configuration du bouton de suppression
    let deleteButton = document.querySelector('#event-details-modal #delete');
    deleteButton.setAttribute('data-id', info.event.id);
    deleteButton.onclick = function() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
            fetch(`delete_schedule.php?id=${info.event.id}`)
                .then(response => response.text())
                .then(data => {
                    alert('Événement supprimé avec succès !');
                    calendar.refetchEvents();
                })
                .catch(error => {
                    console.error('Erreur lors de la suppression :', error);
                    alert('Une erreur est survenue lors de la suppression.');
                });
        }
    };

    new bootstrap.Modal(document.getElementById('event-details-modal')).show();
}