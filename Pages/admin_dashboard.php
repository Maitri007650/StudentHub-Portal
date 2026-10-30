<?php
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => $isSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

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

if (empty($_SESSION["logout_csrf"])) {
    $_SESSION["logout_csrf"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../config/db.php";

$admin_id = $_SESSION["admin_id"];

// Get admin name
$stmt = $conn->prepare(
    "SELECT name FROM admins WHERE admin_id = ?"
);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Dashboard statistics
$totalStudents = $conn->query(
    "SELECT COUNT(*) AS total FROM students"
)->fetch_assoc()["total"];

$totalEvents = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
)->fetch_assoc()["total"];

$totalAdmins = $conn->query(
    "SELECT COUNT(*) AS total FROM admins"
)->fetch_assoc()["total"];

$totalUsers = $totalStudents + $totalAdmins;

// Get latest registrations
$latestRegistrations = $conn->query(
    "SELECT
        registrations.registration_id,
        students.name AS student_name,
        events.title AS event_title,
        registrations.registration_date
     FROM registrations
     INNER JOIN students
        ON registrations.student_id = students.student_id
     INNER JOIN events
        ON registrations.event_id = events.event_id
     ORDER BY registrations.registration_date DESC
     LIMIT 5"
);

// Record dashboard access in audit log
$action = "Viewed Admin Dashboard";
$details = "Admin accessed the dashboard.";

$logStmt = $conn->prepare(
    "INSERT INTO audit_logs (admin_id, action, details)
     VALUES (?, ?, ?)"
);
$logStmt->bind_param("iss", $admin_id, $action, $details);
$logStmt->execute();
$logStmt->close();

// Fetch recent audit logs with administrator name (P14 / Item 3)
$auditLogs = $conn->query(
    "SELECT
        audit_logs.log_id,
        audit_logs.action,
        audit_logs.details,
        audit_logs.created_at,
        admins.name AS admin_name
     FROM audit_logs
     INNER JOIN admins
        ON audit_logs.admin_id = admins.admin_id
     ORDER BY audit_logs.created_at DESC, audit_logs.log_id DESC
     LIMIT 10"
);

// Dynamic menu based on role
$menuItems = [];

if ($_SESSION["role"] === "admin") {
    $menuItems = [
        "manage_students.php" => "Student Management",
        "manage_events.php" => "Event Management"
    ];
}
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

        <p>
            Welcome,
            <?= htmlspecialchars($admin["name"] ?? "Admin", ENT_QUOTES, "UTF-8") ?>!
        </p>

        <?php foreach ($menuItems as $link => $label): ?>
            <a href="<?= htmlspecialchars($link, ENT_QUOTES, "UTF-8") ?>" class="btn">
                <?= htmlspecialchars($label, ENT_QUOTES, "UTF-8") ?>
            </a>
        <?php endforeach; ?>

        <div class="logout-container">
            <form action="logout.php" method="post" style="display:inline;">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["logout_csrf"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>

    <main>
        <section>
            <h2>Administrator Panel</h2>
            <p>You are logged in with administrator access.</p>
        </section>

        <section>
            <h2>Dashboard Statistics</h2>

            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?= $totalUsers ?></p>
                </div>

                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p><?= $totalStudents ?></p>
                </div>

                <div class="stat-card">
                    <h3>Total Events</h3>
                    <p><?= $totalEvents ?></p>
                </div>
            </div>
        </section>

        <section>
            <h2>Latest Registrations</h2>

            <table>
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Student Name</th>
                        <th>Event Name</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($latestRegistrations->num_rows > 0): ?>
                        <?php while ($row = $latestRegistrations->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["registration_id"]) ?></td>
                                <td><?= htmlspecialchars($row["student_name"]) ?></td>
                                <td><?= htmlspecialchars($row["event_title"]) ?></td>
                                <td><?= htmlspecialchars($row["registration_date"]) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No registrations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <section>
            <h2>Recent Activity / Audit Log</h2>
            <p>Recent administrative actions recorded in the system.</p>

            <table>
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Action</th>
                        <th>Administrator</th>
                        <th>Details</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($auditLogs && $auditLogs->num_rows > 0): ?>
                        <?php while ($log = $auditLogs->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int) $log["log_id"] ?></td>
                                <td><?= htmlspecialchars($log["action"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($log["admin_name"] ?? "Admin", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($log["details"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($log["created_at"], ENT_QUOTES, "UTF-8") ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No administrative activity recorded.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>

</body>

</html>