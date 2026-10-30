<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/../config/db.php";

$student_id = (int)$_SESSION["student_id"];

// Generate CSRF token for profile update operations
if (empty($_SESSION["profile_csrf"])) {
    $_SESSION["profile_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";
$isEditing = isset($_GET["edit"]) && $_GET["edit"] === "1";

// Helper for escaping
function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, "UTF-8");
}

// Handle Profile Update (Strictly restricted to currently logged-in student)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedCsrf = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["profile_csrf"] ?? "", $submittedCsrf)) {
        $message = "Invalid security token. Please reload the page.";
        $messageType = "error";
    } else {
        $name = trim($_POST["name"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $gender = trim($_POST["gender"] ?? "");
        $course = trim($_POST["course"] ?? "");
        $year = (int)($_POST["year"] ?? 0);

        if (
            $name === "" ||
            strlen($name) < 2 ||
            strlen($name) > 100 ||
            !preg_match("/^[A-Za-z ]+$/", $name)
        ) {
            $message = "Name must be 2 to 100 characters and contain letters and spaces only.";
            $messageType = "error";
            $isEditing = true;
        } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
            $message = "Please enter a valid 10-digit mobile number.";
            $messageType = "error";
            $isEditing = true;
        } elseif (!in_array($gender, ["Male", "Female", "Other"], true)) {
            $message = "Please select a valid gender option.";
            $messageType = "error";
            $isEditing = true;
        } elseif ($course === "" || strlen($course) > 50) {
            $message = "Please enter a valid course name (up to 50 characters).";
            $messageType = "error";
            $isEditing = true;
        } elseif ($year < 1 || $year > 4) {
            $message = "Please select a valid academic year (Year 1 to Year 4).";
            $messageType = "error";
            $isEditing = true;
        } else {
            try {
                // Update ONLY the currently authenticated student's record
                $stmt = $conn->prepare(
                    "UPDATE students
                     SET name = ?, mobile = ?, gender = ?, course = ?, year = ?
                     WHERE student_id = ?"
                );
                $stmt->bind_param("ssssii", $name, $mobile, $gender, $course, $year, $student_id);
                $stmt->execute();
                $stmt->close();

                // Update session state
                $_SESSION["student_name"] = $name;

                $message = "Your profile has been updated successfully.";
                $messageType = "success";
                $isEditing = false;

            } catch (mysqli_sql_exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                $message = "Unable to update profile. Please try again later.";
                $messageType = "error";
                $isEditing = true;
            }
        }
    }
}

// Fetch currently logged-in student's latest data
$stmt = $conn->prepare(
    "SELECT student_id, name, email, mobile, course, year, gender, created_at
     FROM students
     WHERE student_id = ?"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    // If student record was not found, log out for security
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?denied=1");
    exit;
}

// Fetch registered events for this student
$regStmt = $conn->prepare(
    "SELECT e.event_id, e.title, e.event_date, e.location, r.registration_date
     FROM registrations r
     INNER JOIN events e ON r.event_id = e.event_id
     WHERE r.student_id = ?
     ORDER BY e.event_date ASC"
);
$regStmt->bind_param("i", $student_id);
$regStmt->execute();
$registeredEvents = $regStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$regStmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>StudentHub Portal - My Profile</title>
</head>

<body>

    <header>
        <h1>Student Profile</h1>
        <p>Personal and academic profile for <?= esc($student["name"]) ?></p>
        <div class="logout-container">
            <form action="logout.php" method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= esc($_SESSION["logout_csrf"] ?? "") ?>">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
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
        <a href="login.php">Login</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="events.html">Events</a>
        <a href="profile.php">Profile</a>
        <a href="contact.php">Contact</a>
        <a href="faq.html">FAQ</a>
        <a href="timetable.html">Timetable</a>
        <button id="themeToggle" type="button">Dark Mode</button>
    </nav>

    <main>

        <?php if ($message !== ""): ?>
            <p role="status" class="<?= esc($messageType) ?>" id="statusMessage"><?= esc($message) ?></p>
        <?php endif; ?>

        <!-- Student Summary Header -->
        <section class="profile-header">
            <img src="../image/student.png" alt="Student Photo" width="140" height="140">
            <div class="profile-info">
                <h2><?= esc($student["name"]) ?></h2>
                <p><strong>Student ID / Roll No:</strong> <?= (int)$student["student_id"] ?></p>
                <p><strong>Course:</strong> <?= esc($student["course"] ?: "Not specified") ?></p>
                <p><strong>Academic Year:</strong> <?= $student["year"] ? "Year " . (int)$student["year"] : "Not specified" ?></p>
                <p><strong>Account Status:</strong> <span style="display:inline; color:green; font-weight:bold;">Active Student</span></p>
            </div>
        </section>

        <?php if ($isEditing): ?>
        <!-- Edit Profile Form (Student Self-Service) -->
        <section id="editProfileSection">
            <h2>Edit Profile</h2>
            <p>You can update your personal contact and academic details below. Email and Student ID are read-only.</p>

            <form method="post" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?= esc($_SESSION["profile_csrf"] ?? "") ?>">

                <p>
                    <label for="student_id_display">Student ID (Read-only):</label><br>
                    <input type="text" id="student_id_display" value="<?= (int)$student["student_id"] ?>" disabled>
                </p>

                <p>
                    <label for="email_display">Email Address (Read-only):</label><br>
                    <input type="email" id="email_display" value="<?= esc($student["email"]) ?>" disabled>
                </p>

                <p>
                    <label for="edit_name">Full Name:</label><br>
                    <input type="text" id="edit_name" name="name" required maxlength="100"
                           value="<?= esc($student["name"]) ?>">
                </p>

                <p>
                    <label for="edit_mobile">Mobile Number (10 digits):</label><br>
                    <input type="tel" id="edit_mobile" name="mobile" required pattern="[0-9]{10}" maxlength="10"
                           value="<?= esc($student["mobile"] ?? "") ?>" placeholder="10-digit mobile number">
                </p>

                <p>
                    <label for="edit_gender">Gender:</label><br>
                    <select id="edit_gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male" <?= ($student["gender"] ?? "") === "Male" ? "selected" : "" ?>>Male</option>
                        <option value="Female" <?= ($student["gender"] ?? "") === "Female" ? "selected" : "" ?>>Female</option>
                        <option value="Other" <?= ($student["gender"] ?? "") === "Other" ? "selected" : "" ?>>Other</option>
                    </select>
                </p>

                <p>
                    <label for="edit_course">Course / Department:</label><br>
                    <input type="text" id="edit_course" name="course" required maxlength="50"
                           value="<?= esc($student["course"] ?? "") ?>" placeholder="e.g. Computer Engineering">
                </p>

                <p>
                    <label for="edit_year">Academic Year:</label><br>
                    <select id="edit_year" name="year" required>
                        <option value="">-- Select Year --</option>
                        <?php for ($y = 1; $y <= 4; $y++): ?>
                            <option value="<?= $y ?>" <?= (int)($student["year"] ?? 0) === $y ? "selected" : "" ?>>
                                Year <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </p>

                <p>
                    <button type="submit" class="btn">Save Changes</button>
                    <a href="profile.php" class="btn" style="background-color:#64748b; margin-left: 8px;">Cancel</a>
                </p>
            </form>
        </section>
        <?php else: ?>

        <!-- Personal & Academic Information Details (View Mode) -->
        <section>
            <h2>Personal Information</h2>
            <div class="profile-grid">
                <div>
                    <strong>Full Name:</strong>
                    <p><?= esc($student["name"]) ?></p>
                </div>
                <div>
                    <strong>Email Address:</strong>
                    <p><?= esc($student["email"]) ?></p>
                </div>
                <div>
                    <strong>Mobile Number:</strong>
                    <p><?= esc($student["mobile"] ?: "Not provided") ?></p>
                </div>
                <div>
                    <strong>Gender:</strong>
                    <p><?= esc($student["gender"] ?: "Not specified") ?></p>
                </div>
                <div>
                    <strong>Member Since:</strong>
                    <p><?= esc(date("F j, Y", strtotime($student["created_at"]))) ?></p>
                </div>
                <div>
                    <strong>Portal Role:</strong>
                    <p>Student</p>
                </div>
            </div>
        </section>

        <section>
            <h2>Academic Information</h2>
            <div class="profile-grid">
                <div>
                    <strong>Student ID:</strong>
                    <p><?= (int)$student["student_id"] ?></p>
                </div>
                <div>
                    <strong>Enrolled Program / Course:</strong>
                    <p><?= esc($student["course"] ?: "Not assigned") ?></p>
                </div>
                <div>
                    <strong>Current Academic Year:</strong>
                    <p><?= $student["year"] ? "Year " . (int)$student["year"] : "Not assigned" ?></p>
                </div>
                <div>
                    <strong>Enrolled Events:</strong>
                    <p><?= count($registeredEvents) ?> event(s) registered</p>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <a href="profile.php?edit=1" class="btn">Edit Profile</a>
            </div>
        </section>

        <!-- Registered Events Section -->
        <section>
            <h2>My Registered Events</h2>
            <?php if (count($registeredEvents) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registeredEvents as $rev): ?>
                            <tr>
                                <td><strong><?= esc($rev["title"]) ?></strong></td>
                                <td><?= esc($rev["event_date"]) ?></td>
                                <td><?= esc($rev["location"] ?: "Campus") ?></td>
                                <td><?= esc($rev["registration_date"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not registered for any events yet. Check out upcoming events on the <a href="events.html">Events Page</a>.</p>
            <?php endif; ?>
        </section>
        <?php endif; ?>

    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>

    <script src="../js/script.js"></script>
</body>
</html>

