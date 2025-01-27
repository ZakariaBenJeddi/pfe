document.addEventListener('DOMContentLoaded', function() {
  const teacherSelect = document.getElementById('teacher-select');
  const groupSelect = document.getElementById('group-select');
  const roomSelect = document.getElementById('room-select');
  const scheduleContainer = document.getElementById('teacher-schedule-container');

  // Vérification de l'existence des éléments
  if (!scheduleContainer) {
      console.error("L'élément 'teacher-schedule-container' n'existe pas");
      return;
  }

  let currentType = null;
  let currentValue = null;

  function hideAllSections() {
      const sections = [
          'classes-section',
          'teachers-section',
          'rooms-section',
          'seance'
      ];
      
      sections.forEach(sectionId => {
          const section = document.getElementById(sectionId);
          if (section) {
              section.style.display = 'none';
          }
      });
  }

  function showLoadingState() {
      scheduleContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>';
  }

  function loadSchedule(type, value) {
      if (!value) {
          hideAllSections();
          scheduleContainer.innerHTML = '';
          return;
      }

      currentType = type;
      currentValue = value;

      showLoadingState();
      hideAllSections();

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
              scheduleContainer.innerHTML = 'Aucune donnée disponible';
          }
      })
      .catch(error => {
          console.error('Erreur:', error);
          scheduleContainer.innerHTML = `<div class="alert alert-danger">Erreur de chargement: ${error.message}</div>`;
      });
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

  // Gestionnaire pour le bouton "Afficher tous"
  const afficherTousBtn = document.getElementById('afficherTous');
  if (afficherTousBtn) {
      afficherTousBtn.addEventListener('click', function() {
          hideAllSections();
          document.getElementById('classes-section').style.display = 'block';
          document.getElementById('teachers-section').style.display = 'block';
          document.getElementById('rooms-section').style.display = 'block';
          scheduleContainer.innerHTML = '';
          
          // Réinitialiser les sélecteurs
          if (teacherSelect) teacherSelect.value = '';
          if (groupSelect) groupSelect.value = '';
          if (roomSelect) roomSelect.value = '';
      });
  }
});