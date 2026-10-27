
<?php
$message = "";
$messageType = "";

$name = "";
$email = "";
$subject = "";
$details = "";

function escapeOutput($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $details = trim($_POST["message"] ?? "");

    $errors = [];

    if (strlen($name) < 2 || strlen($name) > 80) {
        $errors[] = "Name must be 2 to 80 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if ($subject === "" || strlen($subject) > 120) {
        $errors[] = "Subject is required and must be at most 120 characters.";
    }

    if (strlen($details) < 5 || strlen($details) > 2000) {
        $errors[] = "Message must be 5 to 2000 characters.";
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

                fputcsv($handle, [
                    date("Y-m-d H:i:s"),
                    $name,
                    $email,
                    $subject,
                    $details
                ]);

                fflush($handle);
                flock($handle, LOCK_UN);
                fclose($handle);

                $message = "Your message has been saved successfully.";
                $messageType = "success";

                $name = $email = $subject = $details = "";
            } else {
                fclose($handle);
                $message = "Unable to save your message. Please try again.";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .contact-box {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        .contact-box label {
            display: block;
            margin-top: 15px;
        }
        .contact-box input,
        .contact-box textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .contact-box button {
            margin-top: 18px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .success { color: green; }
        .error { color: #c62828; }
    </style>
</head>
<body>

<div class="contact-box">
    <h1>Contact StudentHub</h1>
    <p>Send us your questions or feedback.</p>

    <?php if ($message !== ""): ?>
        <p class="<?php echo escapeOutput($messageType); ?>">
            <?php echo escapeOutput($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="contact.php">
        <label for="name">Name</label>
        <input type="text" id="name" name="name"
            value="<?php echo escapeOutput($name); ?>"
            minlength="2" maxlength="80" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email"
            value="<?php echo escapeOutput($email); ?>" required>

        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject"
            value="<?php echo escapeOutput($subject); ?>"
            maxlength="120" required>

        <label for="message">Message</label>
        <textarea id="message" name="message" rows="5"
            minlength="5" maxlength="2000"
            required><?php echo escapeOutput($details); ?></textarea>

        <button type="submit">Submit Message</button>
    </form>
</div>

</body>
</html>
