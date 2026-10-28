
<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    if ($role === "student") {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, name, email, password
                 FROM students
                 WHERE email = ?"
            );

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $stmt->close();

            if (
                $student &&
                password_verify($password, $student["password"])
            ) {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["student_name"] = $student["name"];
                $_SESSION["student_email"] = $student["email"];
                $_SESSION["role"] = "student";

                $message = "Login successful! Welcome, "
                    . htmlspecialchars(
                        $student["name"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
            } else {
                $message = "Invalid email or password.";
            }

        } catch (mysqli_sql_exception $e) {
            error_log("StudentHub login error: " . $e->getMessage());
            $message = "Login failed because of a database error. Please try again.";
        }

    } else {
        $message = "Admin login is not configured yet. Please select Student.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>StudentHub Portal - Login</title>
</head>
<body>

<header>
    <h1>StudentHub Portal</h1>
    <p>Login to access your student account.</p>
</header>

<main>
    <section class="login-section">
        <h2>User Login</h2>

        <form action="login.php" method="post">
            <fieldset>
                <legend>Login Details</legend>

                <p>
                    <label for="email">Email Address:</label><br>
                    <input type="email" id="email" name="email"
                           placeholder="Enter your email" required>
                </p>

                <p>
                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password" required>
                </p>

                <p>
                    <label for="role">Select Role:</label><br>
                    <select id="role" name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </p>

                <p>
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </p>

                <p>
                    <button type="submit">Login</button>
                    <button type="reset">Clear</button>
                </p>
            </fieldset>
        </form>

        <p role="status" aria-live="polite">
            <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
        </p>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </section>
</main>

<footer>
    <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
</footer>

</body>
</html>
