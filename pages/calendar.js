//!version final
// document.addEventListener('DOMContentLoaded', function () {
//     let calendarEl = document.getElementById('calendar');

//     let calendar = new FullCalendar.Calendar(calendarEl, {
//         initialView: 'dayGridMonth',
//         locale: 'fr',
//         headerToolbar: {
//             left: 'prev,next today',
//             center: 'title',
//             right: 'dayGridMonth,timeGridWeek,timeGridDay'
//         },
//         events: 'fetch_events.php', // URL pour récupérer les événements
//         eventContent: function (arg) {
//             let timeText = arg.timeText;
//             let title = arg.event.title;
//             let titleParts = title.split('-')
//             let customHtml = `
//                 <div>
//                     <div>${timeText}</div>
//                     <div>${titleParts[0]}</div>
//                     <div>${titleParts[1]}</div>
//                 </div>
//             `;
//             return { html: customHtml };
//         },
//         eventDidMount: function(info) {
//             let title = info.event.title;
//             let titleParts = title.split('-');
//             let eventType = titleParts[1].trim();
            
//             // Définir les styles en fonction du type d'événement
//             let backgroundColor, borderColor;
//             switch (eventType) {
//                 case 'Anglais':
//                     backgroundColor = '#87CEEB'; // Bleu ciel
//                     borderColor = '#1E90FF'; // Bleu foncé
//                     break;
//                 case 'Arabe':
//                     backgroundColor = '#FFD700'; // Jaune
//                     borderColor = '#DAA520'; // Doré
//                     break;
//                 case 'Éducation Physique':
//                     backgroundColor = '#90EE90'; // Vert clair
//                     borderColor = '#32CD32'; // Vert foncé
//                     break;
//                 case 'Français':
//                     backgroundColor = '#FFC0CB'; // Rose
//                     borderColor = '#FF69B4'; // Rose foncé
//                     break;
//                 case 'Histoire-Géographie':
//                     backgroundColor = '#D8BFD8'; // Mauve
//                     borderColor = '#8B008B'; // Violet foncé
//                     break;
//                 case 'Maths':
//                     backgroundColor = '#ADD8E6'; // Bleu clair
//                     borderColor = '#1E90FF'; // Bleu foncé
//                     break;
//                 case 'PC':
//                     backgroundColor = '#FFA07A'; // Saumon
//                     borderColor = '#FF6347'; // Tomate
//                     break;
//                 case 'Philosophie':
//                     backgroundColor = '#FFDAB9'; // Pêche
//                     borderColor = '#CD853F'; // Marron clair
//                     break;
//                 case 'Physique-Chimie':
//                     backgroundColor = '#B0C4DE'; // Bleu gris
//                     borderColor = '#4169E1'; // Bleu royal
//                     break;
//                 case 'Sport':
//                     backgroundColor = '#98FB98'; // Vert pâle
//                     borderColor = '#00FA9A'; // Vert émeraude
//                     break;
//                 case 'SVT':
//                     backgroundColor = '#7CFC00'; // Vert prairie
//                     borderColor = '#32CD32'; // Vert foncé
//                     break;
//                 default:
//                     backgroundColor = '#ccc';
//                     borderColor = '#333';
//             }
            
//             // Appliquer les styles à l'événement
//             info.el.style.backgroundColor = backgroundColor;
//             info.el.style.borderColor = borderColor;
//             info.el.style.border = '1px solid';
//             info.el.style.margin = '1px';
//         },
//         editable: true, // Permet le drag and drop et le redimensionnement
//         eventDrop: function (info) {
//             // Appelé lorsqu'un événement est déplacé
//             updateEvent(info.event);
//             // window.location.href = 'calendrier.php';
//         },
//         eventResize: function (info) {
//             // Appelé lorsqu'un événement est redimensionné
//             updateEvent(info.event);
//         },
//         eventClick: function (info) {
//             // Votre logique existante pour afficher les détails de l'événement
//             info.jsEvent.preventDefault();

//             let event = info.event;

//             // Remplit les champs de la modale
//             document.querySelector('#event-details-modal #title').textContent = event.title || 'Non spécifié';
//             document.querySelector('#event-details-modal #description').textContent = event.extendedProps.description || 'Non spécifié';
//             document.querySelector('#event-details-modal #salle').textContent = event.extendedProps.salle || 'Non spécifiée';
//             document.querySelector('#event-details-modal #professeur').textContent = event.extendedProps.professeur || 'Non spécifié';
//             document.querySelector('#event-details-modal #start').textContent = event.start.toLocaleString();
//             document.querySelector('#event-details-modal #end').textContent = event.end ? event.end.toLocaleString() : 'Non spécifiée';

//             // Définissez les ID des boutons Edit et Delete
//             let deleteButton = document.querySelector('#event-details-modal #delete');
//             deleteButton.setAttribute('data-id', event.id);

//             // Ajoute un gestionnaire pour le bouton Delete
//             deleteButton.onclick = function () {
//                 if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
//                     // Requête de suppression via fetch
//                     fetch(`delete_schedule.php?id=${event.id}`)
//                         .then(response => response.text())
//                         .then(data => {
//                             alert('Événement supprimé avec succès !');
//                             // Rafraîchit le calendrier pour refléter les changements
//                             calendar.refetchEvents();
//                         })
//                         .catch(error => {
//                             console.error('Erreur lors de la suppression :', error);
//                             alert('Une erreur est survenue lors de la suppression.');
//                         });
//                 }
//             };

//             // Affiche la modale
//             let modal = new bootstrap.Modal(document.getElementById('event-details-modal'));
//             modal.show();
//         },
//         eventMouseEnter: function (info) {
//             // Applique un style au survol
//             info.el.style.cursor = 'pointer';
//         },
//         eventMouseLeave: function (info) {
//             // Réinitialise le style quand le curseur quitte l'événement
//             info.el.style.cursor = '';
//         }
//     });
//     calendar.render();

//     function updateEvent(event) {
//         // Récupère les nouvelles données de l'événement
//         let id = event.id;
//         let start = event.start.toISOString(); // Nouvelle date de début
//         let end = event.end ? event.end.toISOString() : null; // Nouvelle date de fin (si présente)

//         // Envoie les nouvelles données au serveur
//         fetch(`update_schedule.php`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//             },
//             body: JSON.stringify({
//                 id: id,
//                 start: start,
//                 end: end
//             }),
//         })
//             .then(response => response.text())
//             .then(data => {
//                 console.log('Événement mis à jour avec succès :', data);
//             })
//             .catch(error => {
//                 console.error('Erreur lors de la mise à jour de l\'événement :', error);
//                 alert('Une erreur est survenue lors de la mise à jour.');
//             });
//     }

// });

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
            // Récupérer la matière
            let matiere = info.event.extendedProps.matiere;
            
            // Appliquer les couleurs selon la matière
            const colors = getColorForMatiere(matiere);
            info.el.style.backgroundColor = colors.background;
            info.el.style.borderColor = colors.border;
            info.el.style.border = '1px solid';
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
    // État global pour stocker les filtres actifs
// let activeFilters = {
//     professeur: '',
//     classe: '',
//     salle: ''
// };

// function updateCalendar() {
//     // Mettre à jour l'état des filtres
//     activeFilters = {
//         professeur: document.getElementById('teacher-select').value,
//         classe: document.getElementById('group-select').value,
//         salle: document.getElementById('room-select').value
//     };
    
//     // Construire l'URL avec tous les filtres actifs
//     let queryParams = [];
    
//     if (activeFilters.professeur) {
//         queryParams.push(`professeur=${encodeURIComponent(activeFilters.professeur)}`);
//     }
//     if (activeFilters.classe) {
//         queryParams.push(`groupe=${encodeURIComponent(activeFilters.classe)}`);
//     }
//     if (activeFilters.salle) {
//         queryParams.push(`salle=${encodeURIComponent(activeFilters.salle)}`);
//     }
    
//     // Créer l'URL finale
//     let url = 'fetch_events.php';
//     if (queryParams.length > 0) {
//         url += '?' + queryParams.join('&');
//     }
    
//     // Mettre à jour les événements du calendrier
//     calendar.removeAllEvents();
//     calendar.setOption('events', url);
    
//     // Forcer le rechargement des événements
//     calendar.refetchEvents();
    
//     // Log pour le débogage
//     console.log('Filtres actifs:', activeFilters);
//     console.log('URL de requête:', url);
// }

// // Réinitialiser les filtres
// function resetFilters() {
//     document.getElementById('teacher-select').value = '';
//     document.getElementById('group-select').value = '';
//     document.getElementById('room-select').value = '';
//     activeFilters = {
//         professeur: '',
//         classe: '',
//         salle: ''
//     };
//     updateCalendar();
// }

// // Ajouter les écouteurs d'événements
// document.getElementById('teacher-select').addEventListener('change', updateCalendar);
// document.getElementById('group-select').addEventListener('change', updateCalendar);
// document.getElementById('room-select').addEventListener('change', updateCalendar);

// // Optionnel : Ajouter un bouton de réinitialisation
// // <button id="reset-filters">Réinitialiser les filtres</button>
// document.getElementById('reset-filters')?.addEventListener('click', resetFilters);

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