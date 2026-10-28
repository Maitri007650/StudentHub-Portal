
<?php
session_start();

$timeout = 900;

// Check session timeout
if (
    isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

// Only students can access this dashboard
if (
    !isset($_SESSION["student_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "student"
) {
    header("Location: login.php?denied=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>
