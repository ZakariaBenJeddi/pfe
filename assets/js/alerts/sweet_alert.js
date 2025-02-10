function confirmDelete(event, element) {
  event.preventDefault(); // Empêche la redirection immédiate

  Swal.fire({
    title: 'Êtes-vous sûr?',
    text: "Cette action est irréversible!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Oui, supprimer!',
    cancelButtonText: 'Annuler'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = element.href; // Redirige si l'utilisateur confirme
    }
  });

  return false; // Empêche le comportement par défaut du lien
}

document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  let alertShown = false; // Pour éviter de doubler les alertes

  if (urlParams.has('success')) {
      sessionStorage.removeItem('alertShown');
      if (!sessionStorage.getItem('alertShown')) {
          Swal.fire({
              title: 'Succès!',
              text: 'Opération effectuée avec succès',
              icon: 'success',
              confirmButtonColor: '#3085d6',
              confirmButtonText: 'OK'
          });
          sessionStorage.setItem('alertShown', 'true');
          alertShown = true;
      }
  } else if (urlParams.has('error')) {
      sessionStorage.removeItem('alertShown');
      if (!sessionStorage.getItem('alertShown')) {
          Swal.fire({
              title: 'Erreur!',
              text: decodeURIComponent(urlParams.get('error')),
              icon: 'error',
              confirmButtonColor: '#d33',
              confirmButtonText: 'OK'
          });
          sessionStorage.setItem('alertShown', 'true');
          alertShown = true;
      }
  }

  // Supprimer les paramètres de l'URL après l'affichage de l'alerte
  if (alertShown) {
      const newUrl = window.location.pathname; // Garde juste l'URL de base sans query params
      history.replaceState({}, document.title, newUrl);
  }
});
