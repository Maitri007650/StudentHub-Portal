
<?php
session_start();

$timeout = 900;

// Check session timeout
if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

// Only admins can access this dashboard
if (
    !isset($_SESSION["admin_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php?denied=1");
    exit;
}

$_SESSION["last_activity"] = time();

require_once __DIR__ . "/../config/db.php";

$stmt = $conn->prepare(
    "SELECT name FROM admins WHERE admin_id = ?"
);
$stmt->bind_param("i", $_SESSION["admin_id"]);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Admin Dashboard - StudentHub</title>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($admin["name"] ?? "Admin", ENT_QUOTES, "UTF-8") ?>!</p>
        
        <a href="manage_students.php" class="btn">Student Management</a>
        <a href="manage_events.php" class="btn">Event Management</a>
        <div class="logout-container">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <main>
        <section>
            <h2>Administrator Panel</h2>
            <p>You are logged in with administrator access.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>
</body>
</html>
