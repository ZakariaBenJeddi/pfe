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