
<?php
session_start();

header("Content-Type: application/json; charset=UTF-8");

function sendResponse($status, $data)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (
    isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > 900
) {
    $_SESSION = [];
    session_destroy();
    sendResponse(401, ["success" => false, "message" => "Session expired. Please log in again."]);
}

require_once __DIR__ . "/../config/db.php";

$method = $_SERVER["REQUEST_METHOD"];

try {
    if ($method === "GET") {
        $result = $conn->query(
            "SELECT event_id, title, description, event_date, location
             FROM events
             ORDER BY event_date ASC, event_id DESC"
        );

        sendResponse(200, [
            "success" => true,
            "events" => $result->fetch_all(MYSQLI_ASSOC)
        ]);
    }

    if ($method !== "POST") {
        header("Allow: GET, POST");
        sendResponse(405, [
            "success" => false,
            "message" => "Method not allowed."
        ]);
    }

    if (
        !isset($_SESSION["admin_id"]) ||
        ($_SESSION["role"] ?? "") !== "admin"
    ) {
        sendResponse(403, [
            "success" => false,
            "message" => "Admin access required."
        ]);
    }

    if (
        !isset($_SESSION["last_activity"]) ||
        time() - $_SESSION["last_activity"] > 900
    ) {
        sendResponse(401, [
            "success" => false,
            "message" => "Session expired."
        ]);
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        sendResponse(400, [
            "success" => false,
            "message" => "Invalid JSON request."
        ]);
    }

    if (
        !isset($input["csrf_token"]) ||
        !isset($_SESSION["event_csrf"]) ||
        !hash_equals($_SESSION["event_csrf"], $input["csrf_token"])
    ) {
        sendResponse(403, [
            "success" => false,
            "message" => "Invalid security token. Refresh the page."
        ]);
    }

    $_SESSION["last_activity"] = time();

    $action = $input["action"] ?? "";
    $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);

    if ($action === "add" || $action === "update") {
        $title = trim($input["title"] ?? "");
        $description = trim($input["description"] ?? "");
        $date = $input["event_date"] ?? "";
        $location = trim($input["location"] ?? "");

        $dateObj = DateTime::createFromFormat("!Y-m-d", $date);

        if (
            $title === "" ||
            strlen($title) > 150 ||
            strlen($location) > 150 ||
            !$dateObj ||
            $dateObj->format("Y-m-d") !== $date
        ) {
            sendResponse(422, [
                "success" => false,
                "message" => "Enter a valid title, date and location."
            ]);
        }

        if ($action === "add") {
            $stmt = $conn->prepare(
                "INSERT INTO events (title, description, event_date, location)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $title, $description, $date, $location);
            $stmt->execute();
            $newId = $conn->insert_id;
            $stmt->close();

            sendResponse(201, [
                "success" => true,
                "message" => "Event added successfully.",
                "event_id" => $newId
            ]);
        }

        if (!$id || $id < 1) {
            sendResponse(422, [
                "success" => false,
                "message" => "A valid event ID is required."
            ]);
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET title = ?, description = ?, event_date = ?, location = ?
             WHERE event_id = ?"
        );
        $stmt->bind_param("ssssi", $title, $description, $date, $location, $id);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();

        $stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            sendResponse(404, [
                "success" => false,
                "message" => "Event not found."
            ]);
        }

        sendResponse(200, [
            "success" => true,
            "message" => "Event updated successfully."
        ]);
    }

    if ($action === "delete") {
        if (!$id || $id < 1) {
            sendResponse(422, [
                "success" => false,
                "message" => "A valid event ID is required."
            ]);
        }

        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        if (!$deleted) {
            sendResponse(404, [
                "success" => false,
                "message" => "Event not found."
            ]);
        }

        sendResponse(200, [
            "success" => true,
            "message" => "Event deleted successfully."
        ]);
    }

    sendResponse(400, [
        "success" => false,
        "message" => "Invalid action."
    ]);

} catch (Throwable $e) {
    error_log($e->getMessage());

    sendResponse(500, [
        "success" => false,
        "message" => "A server error occurred."
    ]);
}
