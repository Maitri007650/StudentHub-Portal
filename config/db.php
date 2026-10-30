<?php
// Configurable database credentials with local XAMPP defaults (P8-03)
$host = getenv("DB_HOST") ?: "localhost";
$dbname = getenv("DB_NAME") ?: "studenthub";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASS") !== false ? getenv("DB_PASS") : "";
$port = (int)(getenv("DB_PORT") ?: 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $dbname, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Unable to connect to the database.");
}
?>
