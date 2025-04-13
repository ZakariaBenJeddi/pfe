<?php
// Récupérer l'URL de redirection
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'pages/sign-in.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Expirée</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Session expirée',
                text: 'Votre session a expiré en raison d\'inactivité. Veuillez vous reconnecter.',
                icon: 'info',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Se reconnecter'
            }).then((result) => {
                // Rediriger vers la page de connexion
                window.location.href = '<?php echo $redirect_url; ?>';
            });
        });
    </script>
</head>
<body>
    <!-- Cette page sert uniquement à afficher l'alerte -->
</body>
</html>