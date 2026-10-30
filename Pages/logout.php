<?php
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => $isSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

require_once __DIR__ . "/../config/db.php";

// P10-03: Protect logout using POST and CSRF validation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedCsrf = $_POST["csrf_token"] ?? "";

    if (empty($_SESSION["logout_csrf"]) || !hash_equals($_SESSION["logout_csrf"], $submittedCsrf)) {
        http_response_code(403);
        die("Invalid security token. Please return to the dashboard and try again.");
    }

    // Remove the saved Remember Me token from database
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
        "secure" => $isSecure,
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
            "secure" => $isSecure,
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }

    session_destroy();

    header("Location: login.php?logged_out=1");
    exit;
}

// If accessed via GET, render a confirmation form requiring POST + CSRF to proceed
if (empty($_SESSION["logout_csrf"])) {
    $_SESSION["logout_csrf"] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Logout - StudentHub Portal</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Confirm Logout</h1>
        <p>Are you sure you want to log out of StudentHub?</p>
    </header>

    <main>
        <section style="max-width: 450px; margin: 40px auto; text-align: center; padding: 25px;">
            <p>To protect your account against unauthorized requests, logout requires confirmation.</p>

            <form action="logout.php" method="post" style="display:inline-block; margin-top: 15px;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["logout_csrf"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                <button type="submit" class="logout-btn">Yes, Log Me Out</button>
            </form>

            <p style="margin-top: 20px;">
                <a href="<?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php' ?>">← Cancel and return</a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>
</body>
</html>
