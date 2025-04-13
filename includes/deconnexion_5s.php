<?php
$inactivity_limit = 600; // 10 minutes
if (isset($_SESSION['last_action'])) {
    $inactivity_duration = time() - $_SESSION['last_action'];
    if ($inactivity_duration > $inactivity_limit) {
        // Store current page URL for later redirect after login
        $current_url = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $current_url;
        
        // Store login URL for the intermediate page
        $login_url = 'pages/sign-in.php';
        if (strpos($current_url, 'admin/') !== false) {
            $login_url = '../pages/sign-in.php'; // Adjust path based on current directory
        }
        
        // Unset and destroy session
        session_unset();
        session_destroy();
        
        // Start a new session just to store the redirect URL
        session_start();
        $_SESSION['redirect_after_login'] = $current_url;
        
        // Set cookie for the login redirect
        setcookie("redirect_after_login", $current_url, time() + 3600, "/");
        
        // Redirect to the session expired page
        $session_expired_url = 'session_expired.php?redirect=' . urlencode($login_url);
        if (strpos($current_url, 'admin/') !== false) {
            $session_expired_url = '../session_expired.php?redirect=' . urlencode($login_url);
        }
        
        header("Location: " . $session_expired_url);
        exit();
    }
}
$_SESSION['last_action'] = time();
?>