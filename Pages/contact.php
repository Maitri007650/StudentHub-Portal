<?php
session_start();

if (empty($_SESSION["contact_csrf"])) {
    $_SESSION["contact_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

$name = "";
$email = "";
$subject = "";
$details = "";

function escapeOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// P7-03: Prevent CSV formula injection by neutralizing dangerous formula prefix characters
function sanitizeForCsv($value) {
    $value = (string)$value;
    if (isset($value[0]) && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $value;
    }
    return $value;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedCsrf = $_POST["csrf_token"] ?? "";

    // P7-05: CSRF verification
    if (!hash_equals($_SESSION["contact_csrf"] ?? "", $submittedCsrf)) {
        $message = "Invalid security token. Please refresh the page and try again.";
        $messageType = "error";
    } else {
        // P7-02: Input sanitization & validation
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $subject = trim($_POST["subject"] ?? "");
        $details = trim($_POST["message"] ?? "");

        $errors = [];

        if (strlen($name) < 2 || strlen($name) > 80 || !preg_match("/^[A-Za-z ]{2,80}$/", $name)) {
            $errors[] = "Name must be 2 to 80 characters, letters and spaces only.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        // Clean subject from CRLF headers
        $cleanSubject = str_replace(["\r", "\n", "\0"], ' ', $subject);
        if (strlen($cleanSubject) < 2 || strlen($cleanSubject) > 120) {
            $errors[] = "Subject is required and must be between 2 and 120 characters.";
        }

        // Clean message
        $cleanDetails = str_replace("\0", '', $details);
        if (strlen($cleanDetails) < 5 || strlen($cleanDetails) > 2000) {
            $errors[] = "Message must be between 5 and 2000 characters.";
        }

        if (empty($errors)) {
            $storageDir = dirname(__DIR__) . "/storage";

            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            $file = $storageDir . "/contact_messages.csv";
            $handle = fopen($file, "a");

            if ($handle !== false) {
                if (flock($handle, LOCK_EX)) {
                    if (filesize($file) === 0) {
                        fputcsv($handle, [
                            "Date", "Name", "Email", "Subject", "Message"
                        ]);
                    }

                    // Write CSV with formula injection defense
                    fputcsv($handle, [
                        date("Y-m-d H:i:s"),
                        sanitizeForCsv($name),
                        sanitizeForCsv($email),
                        sanitizeForCsv($cleanSubject),
                        sanitizeForCsv($cleanDetails)
                    ]);

                    fflush($handle);
                    flock($handle, LOCK_UN);
                    fclose($handle);

                    $message = "Your message has been saved successfully.";
                    $messageType = "success";

                    // Clear fields and rotate CSRF
                    $name = $email = $subject = $details = "";
                    $_SESSION["contact_csrf"] = bin2hex(random_bytes(32));
                } else {
                    fclose($handle);
                    $message = "Unable to lock storage file. Please try again.";
                    $messageType = "error";
                }
            } else {
                $message = "Unable to open the storage file.";
                $messageType = "error";
            }
        } else {
            $message = implode(" ", $errors);
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Contact</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <header>
        <h1>Contact StudentHub</h1>
        <p>Get in touch with the StudentHub team.</p>
    </header>

    <button id="menuToggle" type="button"
        aria-expanded="false"
        aria-controls="mainNav"
        aria-label="Open navigation menu">
    ☰ Menu
    </button>

    <nav id="mainNav">
        <a href="home.html">Home</a>
        <a href="about.html">About</a>
        <a href="register.php">Register</a>
        <a href="login.html">Login</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="profile.html">Profile</a>
        <a href="contact.php">Contact</a>
        <a href="admin.html">Admin</a>
        <a href="faq.html">FAQ</a>
        <a href="timetable.html">Timetable</a>
        <button id="themeToggle" type="button">Dark Mode</button>
    </nav>

    <main>
        <section>
            <h2>Contact Us</h2>
            <p>
                If you have any questions or need assistance, you can
                contact the StudentHub team using the form below.
            </p>
        </section>

        <section>
            <h2>Contact Form</h2>

            <?php if ($message !== ""): ?>
                <p class="<?= escapeOutput($messageType); ?>" role="status" aria-live="polite">
                    <?= escapeOutput($message); ?>
                </p>
            <?php endif; ?>

            <form action="contact.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= escapeOutput($_SESSION["contact_csrf"] ?? ""); ?>">

                <p>
                    <label for="name">Name:</label><br>
                    <input type="text" id="name" name="name"
                           value="<?= escapeOutput($name); ?>"
                           minlength="2" maxlength="80"
                           pattern="[A-Za-z ]{2,80}"
                           title="Letters and spaces only (2-80 characters)"
                           required>
                </p>

                <p>
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email"
                           value="<?= escapeOutput($email); ?>"
                           required>
                </p>

                <p>
                    <label for="subject">Subject:</label><br>
                    <input type="text" id="subject" name="subject"
                           value="<?= escapeOutput($subject); ?>"
                           minlength="2" maxlength="120"
                           required>
                </p>

                <p>
                    <label for="message">Message:</label><br>
                    <textarea id="message" name="message" rows="5" cols="30"
                              minlength="5" maxlength="2000"
                              required><?= escapeOutput($details); ?></textarea>
                </p>

                <p>
                    <button type="submit">Send Message</button>
                    <button type="reset">Clear</button>
                </p>
            </form>
        </section>

        <section>
            <h2>Contact Information</h2>
            <p>Email: studenthub@example.com</p>
            <p>Phone: +91 00000 00000</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>
