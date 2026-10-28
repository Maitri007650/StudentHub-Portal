<?php
session_start();
require_once __DIR__ . "/../config/db.php";

// Remove the saved Remember Me token
if (!empty($_COOKIE["remember_token"])) {
    $tokenHash = hash("sha256", $_COOKIE["remember_token"]);

    $stmt = $conn->prepare(
        "DELETE FROM remember_tokens WHERE token_hash = ?"
    );
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $stmt->close();
}

// Clear the Remember Me cookie
setcookie("remember_token", "", [
    "expires" => time() - 3600,
    "path" => "/",
    "httponly" => true,
    "samesite" => "Lax"
]);

// Destroy the session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(session_name(), "", [
        "expires" => time() - 42000,
        "path" => $params["path"],
        "domain" => $params["domain"],
        "secure" => $params["secure"],
        "httponly" => $params["httponly"],
        "samesite" => "Lax"
    ]);
}

session_destroy();

header("Location: login.php");
exit;
?>
