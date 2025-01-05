function zoomOutScreen(scale) {
  document.body.style.transform = `scale(${scale})`;
  document.body.style.transformOrigin = 'top left';
  document.body.style.width = `${100 / scale}%`;
  
  // Récupérer l'élément calendrier
  const calendar = document.getElementById('calendar');
  
  // Détecter si c'est un smartphone
  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    // Styles spécifiques pour smartphone
    calendar.style.width = '100vw';  // Prend toute la largeur de la fenêtre
    calendar.style.maxWidth = 'none';
    calendar.style.margin = '0';
    calendar.style.padding = '0';
    
    // Ajuster les styles des éléments internes du calendrier
    const calendarHeader = calendar.querySelector('.fc-header-toolbar');
    if (calendarHeader) {
      calendarHeader.style.padding = '5px';
      calendarHeader.style.fontSize = '0.9em';
    }
    
    // Ajuster la taille de la grille du calendrier
    const calendarView = calendar.querySelector('.fc-view');
    if (calendarView) {
      calendarView.style.width = '100%';
    }
    
    // Forcer le rafraîchissement du calendrier si FullCalendar est initialisé
    if (calendar.fullCalendar) {
      calendar.fullCalendar('render');
    }
  }
}

// Fonction pour vérifier la taille de l'écran et appliquer le zoom
function checkScreenSize() {
  if (window.innerWidth <= 768) {
    // Appliquer le zoom pour les petits écrans
    zoomOutScreen(0.8);
  } else {
    // Réinitialiser le zoom pour les grands écrans
    document.body.style.transform = '';
    document.body.style.width = '';
    
    const calendar = document.getElementById('calendar');
    calendar.style.width = '';
    calendar.style.maxWidth = '';
  }
}

// Ajouter des styles CSS pour le calendrier sur mobile
const mobileStyles = document.createElement('style');
mobileStyles.textContent = `
  @media screen and (max-width: 768px) {
    #calendar {
      width: 100vw !important;
      margin: 0 !important;
      padding: 0 !important;
      overflow-x: hidden !important;
    }
    
    .fc .fc-toolbar {
      flex-wrap: wrap;
    }
    
    .fc .fc-toolbar-title {
      font-size: 1.2em !important;
    }
    
    .fc .fc-button {
      padding: 0.2em 0.4em !important;
      font-size: 0.9em !important;
    }
    
    .fc-event-title {
      font-size: 0.9em !important;
    }
  }
`;
document.head.appendChild(mobileStyles);

// Écouter les changements de taille d'écran
window.addEventListener('resize', checkScreenSize);

// Vérifier la taille de l'écran au chargement
document.addEventListener('DOMContentLoaded', checkScreenSize);