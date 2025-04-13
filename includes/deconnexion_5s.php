<?php
$inactivity_limit = 600; // 5 minutes
if (isset($_SESSION['last_action'])) {
    $inactivity_duration = time() - $_SESSION['last_action'];
    if ($inactivity_duration > $inactivity_limit) {
        // Store current page URL before logout
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        session_unset();
        session_destroy();
        // Start a new session just to store the redirect URL
        session_start();
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: logout.php");
        exit();
    }
}
$_SESSION['last_action'] = time();