<?php
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => $isSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

header("Content-Type: application/json; charset=UTF-8");

function sendJson($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Timeout check
$timeout = 900;
if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"] > $timeout)) {
    $_SESSION = [];
    session_destroy();
    sendJson(401, ["success" => false, "message" => "Session expired. Please log in again."]);
}

// Student authentication check
if (!isset($_SESSION["student_id"]) || ($_SESSION["role"] ?? "") !== "student") {
    sendJson(401, ["success" => false, "message" => "Student authentication required."]);
}

$_SESSION["last_activity"] = time();

if (empty($_SESSION["profile_csrf"])) {
    $_SESSION["profile_csrf"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../config/db.php";

$student_id = (int)$_SESSION["student_id"];
$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method === "GET") {
        $stmt = $conn->prepare(
            "SELECT student_id, name, email, mobile, course, year, gender, created_at
             FROM students WHERE student_id = ?"
        );
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$student) {
            sendJson(404, ["success" => false, "message" => "Student record not found."]);
        }

        // Fetch registered events
        $regStmt = $conn->prepare(
            "SELECT e.event_id, e.title, e.event_date, e.location, r.registration_date
             FROM registrations r
             INNER JOIN events e ON r.event_id = e.event_id
             WHERE r.student_id = ? ORDER BY e.event_date ASC"
        );
        $regStmt->bind_param("i", $student_id);
        $regStmt->execute();
        $events = $regStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $regStmt->close();

        sendJson(200, [
            "success" => true,
            "student" => $student,
            "registered_events" => $events,
            "csrf_token" => $_SESSION["profile_csrf"]
        ]);
    }

    if ($method === "POST") {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $csrf = $input["csrf_token"] ?? "";
        if (!hash_equals($_SESSION["profile_csrf"] ?? "", $csrf)) {
            sendJson(403, ["success" => false, "message" => "Invalid security token. Please refresh."]);
        }

        $name = trim($input["name"] ?? "");
        $mobile = trim($input["mobile"] ?? "");
        $gender = trim($input["gender"] ?? "");
        $course = trim($input["course"] ?? "");
        $year = (int)($input["year"] ?? 0);

        if ($name === "" || strlen($name) < 2 || strlen($name) > 100 || !preg_match("/^[A-Za-z ]+$/", $name)) {
            sendJson(422, ["success" => false, "message" => "Name must be 2 to 100 characters and contain letters and spaces only."]);
        }
        if (!preg_match("/^[0-9]{10}$/", $mobile)) {
            sendJson(422, ["success" => false, "message" => "Please enter a valid 10-digit mobile number."]);
        }
        if (!in_array($gender, ["Male", "Female", "Other"], true)) {
            sendJson(422, ["success" => false, "message" => "Please select a valid gender option."]);
        }
        if ($course === "" || strlen($course) > 50) {
            sendJson(422, ["success" => false, "message" => "Course name is required (max 50 characters)."]);
        }
        if ($year < 1 || $year > 4) {
            sendJson(422, ["success" => false, "message" => "Year must be between 1 and 4."]);
        }

        $stmt = $conn->prepare(
            "UPDATE students SET name = ?, mobile = ?, gender = ?, course = ?, year = ? WHERE student_id = ?"
        );
        $stmt->bind_param("ssssii", $name, $mobile, $gender, $course, $year, $student_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION["student_name"] = $name;

        sendJson(200, [
            "success" => true,
            "message" => "Profile updated successfully.",
            "student" => [
                "student_id" => $student_id,
                "name" => $name,
                "mobile" => $mobile,
                "gender" => $gender,
                "course" => $course,
                "year" => $year
            ]
        ]);
    }

    sendJson(405, ["success" => false, "message" => "Method not allowed."]);

} catch (Throwable $e) {
    error_log("profile_api error: " . $e->getMessage());
    sendJson(500, ["success" => false, "message" => "Internal server error."]);
}

