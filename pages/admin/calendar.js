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

    // function updateEvent(event) {
    //     // Debug des données envoyées
    //     console.log('Event data:', {
    //         id: event.id,
    //         start: event.start.toISOString(),
    //         end: event.end ? event.end.toISOString() : null
    //     });

    //     fetch('update_schedule.php', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //         },
    //         body: JSON.stringify({
    //             id: event.id,
    //             start: event.start.toISOString(),
    //             end: event.end ? event.end.toISOString() : null
    //         }),
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         console.log('Réponse serveur:', data);
    //         if (data.status === 'success') {
    //             console.log('Événement mis à jour avec succès');
    //             calendar.refetchEvents();
    //         } else {
    //             console.error('Erreur:', data.message);
    //             event.revert();
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Erreur lors de la mise à jour:', error);
    //         event.revert();
    //     });
    // }

    function updateEvent(event) {
        // Créer des dates formatées qui conservent le fuseau horaire
        const startDate = moment(event.start).format('YYYY-MM-DD HH:mm:ss');
        const endDate = event.end ? moment(event.end).format('YYYY-MM-DD HH:mm:ss') : null;
        
        console.log('Event data:', {
            id: event.id,
            start: startDate,
            end: endDate
        });
    
        fetch('update_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: event.id,
                start: startDate,
                end: endDate
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

    // Gestionnaire pour le bouton Copier
    document.querySelector('.copier').addEventListener('click', function() {
        fetch('copy_timetable.php')
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    alert('Emploi du temps copié avec succès !');
                } else {
                    alert('Erreur lors de la copie : ' + data.message);
                }
            })
            .catch(error => {
                alert('Erreur lors de la copie : ' + error);
            });
    });

    // Gestionnaire pour le bouton Coller
    document.querySelector('.coller').addEventListener('click', function() {
        if(confirm('Voulez-vous vraiment coller cet emploi du temps ?')) {
            fetch('paste_timetable.php')
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        alert('Emploi du temps collé avec succès !');
                        // Recharger le calendrier pour afficher les nouveaux événements
                        calendar.refetchEvents();
                    } else {
                        alert('Erreur lors du collage : ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur lors du collage : ' + error);
                });
        }
    });

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
        console.log('Event Data:', {
            id: info.event.id,
            title: info.event.title,
            extendedProps: info.event.extendedProps,
            start: info.event.start,
            end: info.event.end
        });

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
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            // Assurez-vous que calendar est accessible dans ce contexte
                            if (info.view && info.view.calendar) {
                                info.view.calendar.refetchEvents();
                            }
                            // Fermer le modal après la suppression si nécessaire
                            if (document.querySelector('#event-details-modal')) {
                                bootstrap.Modal.getInstance(document.getElementById('event-details-modal')).hide();
                            }
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la suppression :', error);
                        alert('Une erreur est survenue lors de la suppression : ' + error.message);
                    });
            }
        };

        let editButton = document.querySelector('#event-details-modal #edit');
        editButton.onclick = async function() {
            // Fermer le modal des détails
            bootstrap.Modal.getInstance(document.getElementById('event-details-modal')).hide();
            
            // Remplir le formulaire avec les données de l'événement
            document.querySelector('input[name="id"]').value = info.event.id;
            
            // Remplir les champs cachés avec gestion des valeurs undefined
            const professeur = info.event.extendedProps.professeur || '';
            const matiere = info.event.extendedProps.matiere || '';
            const classe = info.event.extendedProps.classe || '';
            const salle = info.event.extendedProps.salle || '';

            document.getElementById('professeur-value').value = professeur;
            document.getElementById('matiere-value').value = matiere;
            document.getElementById('classe-value').value = classe;
            document.getElementById('salle-value').value = salle;
            
            // Remplir le titre et la description
            document.getElementById('title').value = info.event.title || '';
            document.getElementById('description').value = info.event.extendedProps.description || '';
            
            // Formater les dates
            const startDate = new Date(info.event.start);
            const endDate = info.event.end ? new Date(info.event.end) : startDate;
            
            document.getElementById('start_datetime').value = formatDateTimeForInput(startDate);
            document.getElementById('end_datetime').value = formatDateTimeForInput(endDate);
            
            try {
                // Sélectionner la classe
                const classeSelect = document.getElementById('classe-select');
                console.log('Recherche classe:', classe);
                console.log('Options classe disponibles:', Array.from(classeSelect.options).map(opt => ({text: opt.text, value: opt.value})));
                
                // Trouver l'option de classe soit par texte exact soit par ID
                await findAndSelectOption(classeSelect, classe);
                
                // Attendre le chargement des matières
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Sélectionner la matière
                const matiereSelect = document.getElementById('matiere-select');
                console.log('Recherche matière:', matiere);
                console.log('Options matière disponibles:', Array.from(matiereSelect.options).map(opt => ({text: opt.text, value: opt.value})));
                
                await findAndSelectOption(matiereSelect, matiere);
                
                // Attendre le chargement des professeurs
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Sélectionner le professeur
                const professeurSelect = document.getElementById('professeur-select');
                console.log('Recherche professeur:', professeur);
                console.log('Options professeur disponibles:', Array.from(professeurSelect.options).map(opt => ({text: opt.text, value: opt.value})));
                
                await findAndSelectOption(professeurSelect, professeur);
                
                // Sélectionner la salle
                const salleSelect = document.getElementById('salle-select');
                console.log('Recherche salle:', salle);
                console.log('Options salle disponibles:', Array.from(salleSelect.options).map(opt => ({text: opt.text, value: opt.value})));
                
                await findAndSelectOption(salleSelect, salle);
                
            } catch (error) {
                console.error('Erreur lors de la sélection des options:', error);
            }
            // AJOUTER CETTE LIGNE POUR OUVRIR LE MODAL DU FORMULAIRE
            new bootstrap.Modal(document.getElementById('CalendarModal')).show();
        };

        new bootstrap.Modal(document.getElementById('event-details-modal')).show();
    }

    async function findAndSelectOption(selectElement, searchValue) {
        if (!selectElement || !searchValue) return;
        
        // Attendre que le select soit enabled
        let attempts = 0;
        while (selectElement.disabled && attempts < 10) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        // Chercher d'abord par texte exact
        let found = Array.from(selectElement.options).find(opt => opt.text === searchValue);
        
        // Si non trouvé, chercher par valeur
        if (!found) {
            found = Array.from(selectElement.options).find(opt => opt.value === searchValue);
        }
        
        // Si non trouvé, chercher par texte partiel
        if (!found) {
            found = Array.from(selectElement.options).find(opt => 
                opt.text.toLowerCase().includes(searchValue.toLowerCase())
            );
        }
        
        if (found) {
            selectElement.value = found.value;
            selectElement.dispatchEvent(new Event('change'));
            console.log(`Option sélectionnée pour ${selectElement.id}:`, found.text);
        } else {
            console.warn(`Aucune option trouvée pour ${searchValue} dans ${selectElement.id}`);
        }
    }

    function formatDateTimeForInput(date) {
        if (!date) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    async function selectOptionByText(selectElement, text) {
        if (!selectElement) return;
        
        // Attendre que le select soit enabled
        let attempts = 0;
        while (selectElement.disabled && attempts < 10) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        // Parcourir les options pour trouver celle qui correspond
        for (let option of selectElement.options) {
            if (option.text === text) {
                selectElement.value = option.value;
                // Déclencher l'événement change
                selectElement.dispatchEvent(new Event('change'));
                break;
            }
        }
    }

    // Fonction utilitaire pour formater la date pour l'input datetime-local
    function formatDateTimeForInput(date) {
        if (!date) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }