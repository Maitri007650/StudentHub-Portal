
<?php
session_start();

$timeout = 900;

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

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

if (empty($_SESSION["event_csrf"])) {
    $_SESSION["event_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "success";

$uploadDir = __DIR__ . "/../uploads/event_posters/";
$uploadUrl = "../uploads/event_posters/";

function escapeHtml($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function uploadPoster($uploadDir)
{
    if (
        !isset($_FILES["poster"]) ||
        $_FILES["poster"]["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return [null, null];
    }

    $file = $_FILES["poster"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return [null, "Poster upload failed. Please try again."];
    }

    if ($file["size"] > 2 * 1024 * 1024) {
        return [null, "Poster must be 2 MB or smaller."];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        return [null, "Only JPG, PNG and WEBP images are allowed."];
    }

    if (!is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true) &&
        !is_dir($uploadDir)) {
        return [null, "Unable to create the poster upload folder."];
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

    if (!move_uploaded_file(
        $file["tmp_name"],
        $uploadDir . $filename
    )) {
        return [null, "Unable to save the poster."];
    }

    return [$filename, null];
}

function removePoster($filename, $uploadDir)
{
    if (
        $filename &&
        basename($filename) === $filename &&
        is_file($uploadDir . $filename)
    ) {
        unlink($uploadDir . $filename);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["event_csrf"], $_POST["csrf_token"])
    ) {
        $message = "Invalid request. Please refresh and try again.";
        $messageType = "error";
    } else {
        $action = $_POST["action"] ?? "";

        try {
            if ($action === "add" || $action === "update") {
                $id = (int)($_POST["event_id"] ?? 0);
                $title = trim($_POST["title"] ?? "");
                $description = trim($_POST["description"] ?? "");
                $date = $_POST["event_date"] ?? "";
                $location = trim($_POST["location"] ?? "");

                $dateObj = DateTime::createFromFormat("!Y-m-d", $date);

                if (
                    $title === "" ||
                    strlen($title) > 150 ||
                    ($location !== "" && strlen($location) > 150) ||
                    !$dateObj ||
                    $dateObj->format("Y-m-d") !== $date
                ) {
                    throw new RuntimeException(
                        "Enter a valid title, date and location."
                    );
                }

                [$newPoster, $uploadError] = uploadPoster($uploadDir);

                if ($uploadError !== null) {
                    throw new RuntimeException($uploadError);
                }

                if ($action === "add") {
                    $stmt = $conn->prepare(
                        "INSERT INTO events
                        (title, description, event_date, location, poster)
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "sssss",
                        $title,
                        $description,
                        $date,
                        $location,
                        $newPoster
                    );

                    $stmt->execute();
                    $stmt->close();

                    $message = "Event added successfully.";
                } else {
                    $stmt = $conn->prepare(
                        "SELECT poster FROM events WHERE event_id = ?"
                    );
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $existing = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$existing) {
                        if ($newPoster) {
                            removePoster($newPoster, $uploadDir);
                        }
                        throw new RuntimeException("Event not found.");
                    }

                    $poster = $newPoster ?? $existing["poster"];

                    $stmt = $conn->prepare(
                        "UPDATE events
                         SET title = ?, description = ?, event_date = ?,
                             location = ?, poster = ?
                         WHERE event_id = ?"
                    );

                    $stmt->bind_param(
                        "sssssi",
                        $title,
                        $description,
                        $date,
                        $location,
                        $poster,
                        $id
                    );

                    $stmt->execute();
                    $stmt->close();

                    if ($newPoster && $existing["poster"]) {
                        removePoster($existing["poster"], $uploadDir);
                    }

                    $message = "Event updated successfully.";
                }
            } elseif ($action === "delete") {
                $id = (int)($_POST["event_id"] ?? 0);

                $stmt = $conn->prepare(
                    "SELECT poster FROM events WHERE event_id = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $event = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$event) {
                    throw new RuntimeException("Event not found.");
                }

                $stmt = $conn->prepare(
                    "DELETE FROM events WHERE event_id = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                removePoster($event["poster"], $uploadDir);
                $message = "Event deleted successfully.";
            } else {
                throw new RuntimeException("Invalid action.");
            }
        } catch (Throwable $e) {
            $message = $e instanceof RuntimeException
                ? $e->getMessage()
                : "An error occurred while saving the event.";

            $messageType = "error";
        }
    }
}

$editEvent = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT * FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$editEvent) {
        $message = "Event not found.";
        $messageType = "error";
    }
}

$result = $conn->query(
    "SELECT * FROM events ORDER BY event_date ASC, event_id DESC"
);
$events = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .event-container {
            max-width: 1100px;
            margin: 25px auto;
            padding: 20px;
        }

        .event-form {
            display: grid;
            gap: 12px;
            margin-bottom: 30px;
        }

        .event-form input,
        .event-form textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .event-table th,
        .event-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .event-table img {
            width: 90px;
            height: auto;
        }

        .notice {
            padding: 12px;
            margin: 15px 0;
            background: #e5f5e8;
        }

        .notice.error {
            background: #ffe5e5;
        }

        .event-table form {
            display: inline-block;
        }

        @media (max-width: 700px) {
            .event-table {
                font-size: 12px;
            }

            .event-table th,
            .event-table td {
                padding: 5px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Event Management</h1>
    <p>Add, view, update and delete college events.</p>
    <a href="admin_dashboard.php" class="btn">Back to Dashboard</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<main class="event-container">

    <?php if ($message !== ""): ?>
        <p class="notice <?= $messageType === "error" ? "error" : "" ?>">
            <?= escapeHtml($message) ?>
        </p>
    <?php endif; ?>

    <section>
        <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>

        <form class="event-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"
                   value="<?= escapeHtml($_SESSION["event_csrf"]) ?>">

            <input type="hidden" name="action"
                   value="<?= $editEvent ? "update" : "add" ?>">

            <?php if ($editEvent): ?>
                <input type="hidden" name="event_id"
                       value="<?= (int)$editEvent["event_id"] ?>">
            <?php endif; ?>

            <label for="title">Event Title</label>
            <input id="title" name="title" type="text" maxlength="150"
                   required value="<?= escapeHtml($editEvent["title"] ?? "") ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= escapeHtml($editEvent["description"] ?? "") ?></textarea>

            <label for="event_date">Event Date</label>
            <input id="event_date" name="event_date" type="date" required
                   value="<?= escapeHtml($editEvent["event_date"] ?? "") ?>">

            <label for="location">Location</label>
            <input id="location" name="location" type="text" maxlength="150"
                   value="<?= escapeHtml($editEvent["location"] ?? "") ?>">

            <label for="poster">Poster (JPG, PNG or WEBP; maximum 2 MB)</label>
            <input id="poster" name="poster" type="file"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

            <?php if (!empty($editEvent["poster"])): ?>
                <p>Current poster:</p>
                <img src="<?= $uploadUrl . rawurlencode(basename($editEvent["poster"])) ?>"
                     alt="Current event poster" width="120">
                <p>Leave the upload field empty to keep the current poster.</p>
            <?php endif; ?>

            <button type="submit">
                <?= $editEvent ? "Update Event" : "Add Event" ?>
            </button>

            <?php if ($editEvent): ?>
                <a href="manage_events.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Existing Events (<?= count($events) ?>)</h2>

        <table class="event-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Poster</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$events): ?>
                <tr><td colspan="6">No events found.</td></tr>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= (int)$event["event_id"] ?></td>
                        <td><?= escapeHtml($event["title"]) ?></td>
                        <td><?= escapeHtml($event["event_date"]) ?></td>
                        <td><?= escapeHtml($event["location"]) ?></td>
                        <td>
                            <?php if (!empty($event["poster"])): ?>
                                <img
                                    src="<?= $uploadUrl . rawurlencode(basename($event["poster"])) ?>"
                                    alt="Event poster">
                            <?php else: ?>
                                No poster
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="manage_events.php?edit=<?= (int)$event["event_id"] ?>">
                                Edit
                            </a>

                            <form method="POST" action="manage_events.php"
                                onsubmit="return confirm('Delete this event?');">
                                <input type="hidden" name="csrf_token"
                                       value="<?= escapeHtml($_SESSION["event_csrf"]) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="event_id"
                                       value="<?= (int)$event["event_id"] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
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
