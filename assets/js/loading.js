// window.onload = function() {
//   const loadingScreen = document.getElementById('loading-screen');
//   timeReload = 5000
//   // Affiche l'animation de chargement
//   loadingScreen.style.display = 'flex';

//   if (!sessionStorage.getItem('pageReloaded')) {
//       // Si la page n'a pas encore été rechargée
//       setTimeout(function() {
//           location.reload(); // Recharge la page
//       }, timeReload); // Temps en millisecondes
//       location.reload(); // Recharge la page

//       // Marque la page comme "déjà rechargée"
//       sessionStorage.setItem('pageReloaded', true);
//   } else {
//       // Cache l'animation après le chargement
//       setTimeout(function() {
//           loadingScreen.style.display = 'none';
//       }, timeReload); // Cache l'animation après 1 seconde
//   }
// };