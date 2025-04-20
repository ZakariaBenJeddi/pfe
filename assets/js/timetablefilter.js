
document.addEventListener('DOMContentLoaded', function() {
    const teacherSelect = document.getElementById('teacher-select');
    const groupSelect = document.getElementById('group-select');
    const roomSelect = document.getElementById('room-select');
    
    // Sélectionnez les sections existantes
    const classesSection = document.getElementById('classes-section');
    const teachersSection = document.getElementById('teachers-section');
    const roomsSection = document.getElementById('rooms-section');
    
    // Créez le conteneur pour les résultats filtrés s'il n'existe pas
    let scheduleContainer = document.getElementById('teacher-schedule-container');
    if (!scheduleContainer) {
        scheduleContainer = document.createElement('div');
        scheduleContainer.id = 'teacher-schedule-container';
        scheduleContainer.className = 'mt-4 table-responsive';
        document.querySelector('.px-0.pt-0').appendChild(scheduleContainer);
    }
  
    let currentType = null;
    let currentValue = null;
  
    function hideAllSections() {
        if (classesSection) classesSection.style.display = 'none';
        if (teachersSection) teachersSection.style.display = 'none';
        if (roomsSection) roomsSection.style.display = 'none';
        
        // Affichage du conteneur de résultats
        scheduleContainer.style.display = 'block';
    }
    
    function showAllSections() {
        if (classesSection) classesSection.style.display = 'block';
        if (teachersSection) teachersSection.style.display = 'block';
        if (roomsSection) roomsSection.style.display = 'block';
        
        // Masquage du conteneur de résultats
        scheduleContainer.style.display = 'none';
    }
  
    function showLoadingState() {
        scheduleContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
    }
  
    function loadSchedule(type, value) {
        if (!value) {
            showAllSections();
            scheduleContainer.innerHTML = '';
            return;
        }
  
        currentType = type;
        currentValue = value;
  
        showLoadingState();
        hideAllSections();
  
        // Adapter selon votre structure de données
        let sectionId;
        let selector;
        
        switch(type) {
            case 'teacher':
                sectionId = 'teachers-section';
                selector = `h2:contains("Professeur: ${value}")`;
                break;
            case 'group':
                sectionId = 'classes-section';
                selector = `h2:contains("Classe: ${value}")`;
                break;
            case 'room':
                sectionId = 'rooms-section';
                selector = `h2:contains("Salle: ${value}")`;
                break;
        }
        
        // Option 1: Utiliser AJAX
        fetch(`?${type}=${encodeURIComponent(value)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            if (html.trim()) {
                scheduleContainer.innerHTML = html;
            } else {
                // Option 2: Filtrer les données existantes si AJAX ne retourne rien
                const section = document.getElementById(sectionId);
                if (section) {
                    // Recherche de l'élément correspondant (par exemple, le h2 contenant le nom du prof)
                    const tableContainer = section.querySelector(`.schedule-container h2:contains("${value}")`);
                    
                    if (tableContainer) {
                        const scheduleTable = tableContainer.closest('.schedule-container');
                        scheduleContainer.innerHTML = scheduleTable.outerHTML;
                    } else {
                        scheduleContainer.innerHTML = 'Aucune donnée disponible pour ce filtre';
                    }
                } else {
                    scheduleContainer.innerHTML = 'Aucune donnée disponible';
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            scheduleContainer.innerHTML = `<div class="alert alert-danger">Erreur de chargement: ${error.message}</div>`;
            
            // Option de secours: Filtrer les données localement si AJAX échoue
            filterLocalData(type, value);
        });
    }
    
    // Fonction pour filtrer localement à partir des données existantes
    function filterLocalData(type, value) {
        let sourceSection;
        let searchText;
        
        switch(type) {
            case 'teacher':
                sourceSection = teachersSection;
                searchText = `Professeur: ${value}`;
                break;
            case 'group':
                sourceSection = classesSection;
                searchText = `Classe: ${value}`;
                break;
            case 'room':
                sourceSection = roomsSection;
                searchText = `Salle: ${value}`;
                break;
        }
        
        if (sourceSection) {
            const scheduleContainers = sourceSection.querySelectorAll('.schedule-container');
            let found = false;
            
            scheduleContainers.forEach(container => {
                const heading = container.querySelector('h2');
                if (heading && heading.textContent.includes(value)) {
                    scheduleContainer.innerHTML = container.outerHTML;
                    found = true;
                }
            });
            
            if (!found) {
                scheduleContainer.innerHTML = 'Aucune donnée disponible pour ce filtre';
            }
        }
    }
  
    // Ajouter la méthode 'contains' à l'object String pour les sélecteurs
    if (!String.prototype.contains) {
        String.prototype.contains = function(search) {
            return this.indexOf(search) !== -1;
        };
    }
    
    // Ajouter une méthode personnalisée pour querySelector
    function querySelectorWithContains(selector) {
        const parts = selector.split(':contains(');
        if (parts.length === 1) return document.querySelector(selector);
        
        const baseSelector = parts[0];
        const searchText = parts[1].slice(0, -1);
        
        const elements = document.querySelectorAll(baseSelector);
        for (let i = 0; i < elements.length; i++) {
            if (elements[i].textContent.includes(searchText)) {
                return elements[i];
            }
        }
        return null;
    }
  
    // Gestionnaires d'événements pour les sélecteurs
    teacherSelect?.addEventListener('change', function() {
        loadSchedule('teacher', this.value);
        if (groupSelect) groupSelect.value = '';
        if (roomSelect) roomSelect.value = '';
    });
  
    groupSelect?.addEventListener('change', function() {
        loadSchedule('group', this.value);
        if (teacherSelect) teacherSelect.value = '';
        if (roomSelect) roomSelect.value = '';
    });
  
    roomSelect?.addEventListener('change', function() {
        loadSchedule('room', this.value);
        if (teacherSelect) teacherSelect.value = '';
        if (groupSelect) groupSelect.value = '';
    });
    showAllSections();
});