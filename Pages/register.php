
<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION["register_csrf"])) {
    $_SESSION["register_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

$name = "";
$email = "";
$mobile = "";
$course = "";
$year = "";
$gender = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $submittedCsrf = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["register_csrf"] ?? "", $submittedCsrf)) {
        $message = "Invalid security token. Please refresh the page and try again.";
        $messageType = "error";
    } else {
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $mobile = trim($_POST["mobile"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirmPassword = $_POST["confirmPassword"] ?? "";
        $course = trim($_POST["course"] ?? "");
        $year = $_POST["year"] ?? "";
        $gender = $_POST["gender"] ?? "";
        $terms = isset($_POST["terms"]);

        if (
            !preg_match("/^[A-Za-z ]{2,50}$/", $name) ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !preg_match("/^[0-9]{10}$/", $mobile) ||
            !preg_match("/^[A-Za-z ]{2,50}$/", $course) ||
            !in_array($year, ["1", "2", "3", "4"], true) ||
            !in_array($gender, ["Male", "Female", "Other"], true) ||
            !$terms
        ) {
            $message = "Please enter valid details in all fields.";
            $messageType = "error";

        } elseif (
            strlen($password) < 8 ||
            !preg_match("/[a-z]/", $password) ||
            !preg_match("/[A-Z]/", $password) ||
            !preg_match("/[0-9]/", $password) ||
            !preg_match("/[^A-Za-z0-9]/", $password)
        ) {
            $message = "Password must contain at least 8 characters, including uppercase, lowercase, a number and a special character.";
            $messageType = "error";

        } elseif ($password !== $confirmPassword) {
            $message = "Passwords do not match.";
            $messageType = "error";

        } else {
            try {
                // Check for an existing email.
                $check = $conn->prepare(
                    "SELECT student_id FROM students WHERE email = ?"
                );
                $check->bind_param("s", $email);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $message = "This email is already registered.";
                    $messageType = "error";
                    $check->close();

                } else {
                    $check->close();

                    // Hash the password before storing it.
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    // Insert the student using a prepared statement.
                    $stmt = $conn->prepare(
                        "INSERT INTO students
                        (name, email, password, mobile, course, year, gender)
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    $yearValue = (int) $year;
                    $stmt->bind_param(
                        "sssssis",
                        $name,
                        $email,
                        $hashedPassword,
                        $mobile,
                        $course,
                        $yearValue,
                        $gender
                    );

                    $stmt->execute();

                    if ($stmt->affected_rows === 1) {
                        $studentId = (string) $conn->insert_id;
                        $message = "Registration successful! Student ID: "
                            . htmlspecialchars(
                                $studentId,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        $messageType = "success";
                        // Reset fields on success
                        $name = $email = $mobile = $course = $year = $gender = "";
                        // Rotate CSRF token
                        $_SESSION["register_csrf"] = bin2hex(random_bytes(32));
                    } else {
                        $message = "Registration could not be confirmed. Please check the student records.";
                        $messageType = "error";
                    }

                    $stmt->close();
                }

            } catch (mysqli_sql_exception $e) {
                error_log("StudentHub registration error: " . $e->getMessage());

                if ((int) $e->getCode() === 1062) {
                    $message = "This email is already registered.";
                } else {
                    $message = "Registration failed because of a database error. Please try again.";
                }

                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>StudentHub Portal - Register</title>
</head>

<body>
    
    <header>
        <h1>StudentHub Portal</h1>
        <p>Create your StudentHub account.</p>
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
        <section class="register-section">
            <h2>Student Registration</h2>

            <form id="registrationForm" action="register.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["register_csrf"] ?? "", ENT_QUOTES, "UTF-8") ?>">

                <fieldset>
                    <legend>Registration Details</legend>

                    <p>
                        <label for="name">Full Name:</label><br>
                        <input type="text" id="name" name="name"
                            pattern="[A-Za-z ]{2,50}"
                            title="Name should contain only letters and spaces"
                            placeholder="Enter your full name" required
                            aria-describedby="nameError"
                            aria-invalid="false"
                            value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>">
                        <span id="nameError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <label for="email">Email:</label><br>
                        <input type="email" id="email" name="email"
                            placeholder="Enter your email" required
                            aria-describedby="emailError"
                            aria-invalid="false"
                            value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>">
                        <span id="emailError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <label for="mobile">Mobile Number:</label><br>
                        <input type="tel" id="mobile" name="mobile"
                            pattern="[0-9]{10}"
                            title="Enter a valid 10-digit mobile number"
                            placeholder="Enter 10-digit mobile number" required
                            aria-describedby="mobileError"
                            aria-invalid="false"
                            value="<?= htmlspecialchars($mobile, ENT_QUOTES, "UTF-8") ?>">
                        <span id="mobileError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <label for="password">Password:</label><br>
                        <input type="password" id="password" name="password"
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}"
                            title="Use at least 8 characters, including uppercase, lowercase, number and special character"
                            placeholder="Create a password" required
                            aria-describedby="passwordError passwordStrength"
                            aria-invalid="false">
                        <span id="passwordError" class="error-msg" role="alert" aria-live="polite"></span>
                        <span id="passwordStrength" class="strength-msg" role="status" aria-live="polite"></span>
                    </p>

                    <p>
                        <label for="confirmPassword">Confirm Password:</label><br>
                        <input type="password" id="confirmPassword"
                            name="confirmPassword"
                            placeholder="Confirm your password" required
                            aria-describedby="confirmPasswordError"
                            aria-invalid="false">
                        <span id="confirmPasswordError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <input type="checkbox" id="showPassword" name="showPassword">
                        <label for="showPassword">Show Password</label>
                    </p>

                    <p>
                        <label for="course">Course:</label><br>
                        <input type="text" id="course" name="course"
                            pattern="[A-Za-z ]{2,50}"
                            title="Course should contain only letters and spaces"
                            placeholder="Enter your course" required
                            aria-describedby="courseError"
                            aria-invalid="false"
                            value="<?= htmlspecialchars($course, ENT_QUOTES, "UTF-8") ?>">
                        <span id="courseError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <label for="year">Year:</label><br>
                        <select id="year" name="year" required
                            aria-describedby="yearError"
                            aria-invalid="false">
                            <option value="">Select Year</option>
                            <option value="1" <?= $year === "1" ? "selected" : "" ?>>1st Year</option>
                            <option value="2" <?= $year === "2" ? "selected" : "" ?>>2nd Year</option>
                            <option value="3" <?= $year === "3" ? "selected" : "" ?>>3rd Year</option>
                            <option value="4" <?= $year === "4" ? "selected" : "" ?>>4th Year</option>
                        </select>
                        <span id="yearError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <label>Gender:</label><br>
                        <input type="radio" id="male" name="gender"
                            value="Male" required aria-describedby="genderError"
                            <?= $gender === "Male" ? "checked" : "" ?>>
                        <label for="male">Male</label>

                        <input type="radio" id="female" name="gender"
                            value="Female" aria-describedby="genderError"
                            <?= $gender === "Female" ? "checked" : "" ?>>
                        <label for="female">Female</label>

                        <input type="radio" id="other" name="gender"
                            value="Other" aria-describedby="genderError"
                            <?= $gender === "Other" ? "checked" : "" ?>>
                        <label for="other">Other</label>

                        <span id="genderError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <input type="checkbox" id="terms" name="terms" required
                            aria-describedby="termsError"
                            aria-invalid="false">
                        <label for="terms">I accept the terms and conditions.</label>
                        <span id="termsError" class="error-msg" role="alert" aria-live="polite"></span>
                    </p>

                    <p>
                        <button type="submit">Register</button>
                        <button type="reset">Clear</button>
                    </p>

                    <p id="successMessage"
                        class="<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"
                        role="status" aria-live="polite">
                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </fieldset>
            </form>

            <p>
                Already have an account?
                <a href="login.html">Login here</a>
            </p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>

    <script src="../js/script.js"></script> 
</body>
</html>
