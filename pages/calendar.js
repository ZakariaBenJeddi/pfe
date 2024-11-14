// document.addEventListener('DOMContentLoaded', function () {
//   let calendarEl = document.getElementById('calendar');

//   let calendar = new FullCalendar.Calendar(calendarEl, {
//       initialView: 'dayGridMonth',
//       locale: 'fr',
//       events: 'fetch_events.php', // URL du fichier PHP
//       headerToolbar: {
//           left: 'prev,next today',
//           center: 'title',
//           right: 'dayGridMonth,timeGridWeek,timeGridDay'
//       }
//   });

//   calendar.render();
// });


// document.addEventListener('DOMContentLoaded', function () {
//   let calendarEl = document.getElementById('calendar');

//   let calendar = new FullCalendar.Calendar(calendarEl, {
//       initialView: 'dayGridMonth',
//       locale: 'fr',
//       headerToolbar: {
//           left: 'prev,next today',
//           center: 'title',
//           right: 'dayGridMonth,timeGridWeek,timeGridDay'
//       },
//       events: 'fetch_events.php', // URL pour récupérer les événements
//       eventClick: function (info) {
//           info.jsEvent.preventDefault();

//           let event = info.event;

//           // Remplit les champs de la modale
//           document.querySelector('#event-details-modal #title').textContent = event.title || 'Non spécifié';
//           document.querySelector('#event-details-modal #description').textContent = event.extendedProps.description || 'Non spécifié';
//           document.querySelector('#event-details-modal #salle').textContent = event.extendedProps.salle || 'Non spécifiée';
//           document.querySelector('#event-details-modal #professeur').textContent = event.extendedProps.professeur || 'Non spécifié';
//           document.querySelector('#event-details-modal #start').textContent = event.start.toLocaleString();
//           document.querySelector('#event-details-modal #end').textContent = event.end ? event.end.toLocaleString() : 'Non spécifiée';

//           // Définissez les ID des boutons Edit et Delete
//           document.querySelector('#event-details-modal #edit').setAttribute('data-id', event.id);
//           document.querySelector('#event-details-modal #delete').setAttribute('data-id', event.id);

//           // Affiche la modale
//           let modal = new bootstrap.Modal(document.getElementById('event-details-modal'));
//           modal.show();
//       },
//       eventMouseEnter: function (info) {
//           // Applique un style au survol
//           info.el.style.cursor = 'pointer';
//       },
//       eventMouseLeave: function (info) {
//           // Réinitialise le style quand le curseur quitte l'événement
//           info.el.style.cursor = '';
//       }
//   });

//   calendar.render();
// });

//!version supprime
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
//         eventClick: function (info) {
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
// });

//!version supprime et modifier
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
//         eventClick: function (info) {
//             info.jsEvent.preventDefault();

//             let event = info.event;

//             // Remplit les champs de la modale d'affichage
//             document.querySelector('#event-details-modal #title').textContent = event.title || 'Non spécifié';
//             document.querySelector('#event-details-modal #description').textContent = event.extendedProps.description || 'Non spécifié';
//             document.querySelector('#event-details-modal #salle').textContent = event.extendedProps.salle || 'Non spécifiée';
//             document.querySelector('#event-details-modal #professeur').textContent = event.extendedProps.professeur || 'Non spécifié';
//             document.querySelector('#event-details-modal #start').textContent = event.start.toLocaleString();
//             document.querySelector('#event-details-modal #end').textContent = event.end ? event.end.toLocaleString() : 'Non spécifiée';

//             // Définissez les ID des boutons Edit et Delete
//             let editButton = document.querySelector('#event-details-modal #edit');
//             editButton.setAttribute('data-id', event.id);

//             // Gère le clic sur le bouton Edit
//             editButton.onclick = function () {
//                 // Remplit les champs du formulaire avec les données de l'événement
//                 document.querySelector('#schedule-form input[name="id"]').value = event.id;
//                 document.querySelector('#schedule-form input[name="title"]').value = event.title || '';
//                 document.querySelector('#schedule-form textarea[name="description"]').value = event.extendedProps.description || '';
//                 document.querySelector('#schedule-form select[name="professeur"]').value = event.extendedProps.professeur || '';
//                 document.querySelector('#schedule-form input[name="salle"]').value = event.extendedProps.salle || '';
//                 document.querySelector('#schedule-form input[name="start_datetime"]').value = event.start.toISOString().slice(0, 16); // Format YYYY-MM-DDTHH:mm
//                 document.querySelector('#schedule-form input[name="end_datetime"]').value = event.end ? event.end.toISOString().slice(0, 16) : '';

//                 // Scroll ou focus sur le formulaire (optionnel)
//                 document.querySelector('#schedule-form').scrollIntoView({ behavior: 'smooth' });
//             };

//             // Gère le clic sur le bouton Delete
//             let deleteButton = document.querySelector('#event-details-modal #delete');
//             deleteButton.setAttribute('data-id', event.id);
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
// });

//!version final
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
        events: 'fetch_events.php', // URL pour récupérer les événements
        editable: true, // Permet le drag and drop et le redimensionnement
        eventDrop: function (info) {
            // Appelé lorsqu'un événement est déplacé
            updateEvent(info.event);
        },
        eventResize: function (info) {
            // Appelé lorsqu'un événement est redimensionné
            updateEvent(info.event);
        },
        eventClick: function (info) {
            // Votre logique existante pour afficher les détails de l'événement
            info.jsEvent.preventDefault();

            let event = info.event;

            // Remplit les champs de la modale
            document.querySelector('#event-details-modal #title').textContent = event.title || 'Non spécifié';
            document.querySelector('#event-details-modal #description').textContent = event.extendedProps.description || 'Non spécifié';
            document.querySelector('#event-details-modal #salle').textContent = event.extendedProps.salle || 'Non spécifiée';
            document.querySelector('#event-details-modal #professeur').textContent = event.extendedProps.professeur || 'Non spécifié';
            document.querySelector('#event-details-modal #start').textContent = event.start.toLocaleString();
            document.querySelector('#event-details-modal #end').textContent = event.end ? event.end.toLocaleString() : 'Non spécifiée';

            // Définissez les ID des boutons Edit et Delete
            let deleteButton = document.querySelector('#event-details-modal #delete');
            deleteButton.setAttribute('data-id', event.id);

            // Ajoute un gestionnaire pour le bouton Delete
            deleteButton.onclick = function () {
                if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                    // Requête de suppression via fetch
                    fetch(`delete_schedule.php?id=${event.id}`)
                        .then(response => response.text())
                        .then(data => {
                            alert('Événement supprimé avec succès !');
                            // Rafraîchit le calendrier pour refléter les changements
                            calendar.refetchEvents();
                        })
                        .catch(error => {
                            console.error('Erreur lors de la suppression :', error);
                            alert('Une erreur est survenue lors de la suppression.');
                        });
                }
            };

            // Affiche la modale
            let modal = new bootstrap.Modal(document.getElementById('event-details-modal'));
            modal.show();
        },
        eventMouseEnter: function (info) {
            // Applique un style au survol
            info.el.style.cursor = 'pointer';
        },
        eventMouseLeave: function (info) {
            // Réinitialise le style quand le curseur quitte l'événement
            info.el.style.cursor = '';
        }
    });

    calendar.render();

    function updateEvent(event) {
        // Récupère les nouvelles données de l'événement
        let id = event.id;
        let start = event.start.toISOString(); // Nouvelle date de début
        let end = event.end ? event.end.toISOString() : null; // Nouvelle date de fin (si présente)

        // Envoie les nouvelles données au serveur
        fetch(`update_schedule.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                start: start,
                end: end
            }),
        })
            .then(response => response.text())
            .then(data => {
                console.log('Événement mis à jour avec succès :', data);
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de l\'événement :', error);
                alert('Une erreur est survenue lors de la mise à jour.');
            });
    }
});