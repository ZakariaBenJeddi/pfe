<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        function redirigerVersAutrePage() {
            // Vérifier si la session a expiré
            if (document.cookie.includes('session_expired=1')) {
                document.cookie = "session_expired=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                sessionStorage.setItem('session_expired', 'true');
            }
            window.location.href = "pages/sign-in.php";
        }
        setTimeout(redirigerVersAutrePage, 500);
    </script>
</head>
<body>
</html>