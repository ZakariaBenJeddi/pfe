<?php
$inactivity_limit = 300*2; // 5 minutes
if (isset($_SESSION['last_action'])) {
    $inactivity_duration = time() - $_SESSION['last_action'];
    if ($inactivity_duration > $inactivity_limit) {
        session_unset();
        session_destroy();
        header("Location: logout.php");
        exit();
    }
}
$_SESSION['last_action'] = time();