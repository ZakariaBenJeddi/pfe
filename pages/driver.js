document.getElementById('startTourButton').addEventListener('click', function() {
  const driverObj = window.driver.js.driver({
    showProgress: true,
    showButtons: ['next', 'previous'],
    steps: [{
        element: '#nombre_salle',
        popover: {
          title: 'Premier exemple',
          description: 'Voici la première étape du tour',
          side: "left",
          align: 'start'
        }
      },
      {
        element: '#nombre_eleve',
        popover: {
          title: 'Premier exemple',
          description: 'Voici la première étape du tour',
          side: "right",
          align: 'start'
        }
      },
      {
        element: '#ensaignant',
        popover: {
          title: 'Import de la librairie',
          description: 'Première ligne de code',
          side: "bottom",
          align: 'start'
        }
      },
      {
        element: '#abscence',
        popover: {
          title: 'Import de la librairie',
          description: 'Première ligne de code',
          side: "left",
          align: 'start',
        }
      },
      {
        element: '#column-chart',
        popover: {
          title: 'Abscence Chart',
          description: 'Première ligne de code',
          side: "right",
          align: 'start',
        }
      },
      // ... autres étapes ...
    ]
  });

  driverObj.drive();
});