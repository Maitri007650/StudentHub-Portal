
<?php
session_start();

$timeout = 900; // 15 minutes

// Check session timeout
if (
    isset($_SESSION["last_activity"]) &&
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(session_name(), "", [
            "expires" => time() - 42000,
            "path" => $params["path"],
            "domain" => $params["domain"],
            "secure" => $params["secure"],
            "httponly" => $params["httponly"],
            "samesite" => "Lax"
        ]);
    }

    session_destroy();
    session_start();

    header("Location: login.php?timeout=1");
    exit;
}

require_once __DIR__ . "/../config/db.php";

$message = "";

if (isset($_GET["timeout"]) && $_GET["timeout"] === "1") {
    $message = "Your session expired. Please log in again.";
}

// Restore login using Remember Me token
if (
    !isset($_SESSION["student_id"]) &&
    !isset($_SESSION["admin_id"]) &&
    !empty($_COOKIE["remember_token"])
) {
    $tokenHash = hash("sha256", $_COOKIE["remember_token"]);

    $stmt = $conn->prepare(
        "SELECT student_id, admin_id
         FROM remember_tokens
         WHERE token_hash = ? AND expires_at > NOW()"
    );

    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $tokenRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($tokenRow && !empty($tokenRow["student_id"])) {
        $stmt = $conn->prepare(
            "SELECT student_id, name, email
             FROM students WHERE student_id = ?"
        );
        $stmt->bind_param("i", $tokenRow["student_id"]);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($student) {
            session_regenerate_id(true);
            $_SESSION["student_id"] = $student["student_id"];
            $_SESSION["student_name"] = $student["name"];
            $_SESSION["student_email"] = $student["email"];
            $_SESSION["role"] = "student";
            $_SESSION["last_activity"] = time();
        }
    } elseif ($tokenRow && !empty($tokenRow["admin_id"])) {
        $stmt = $conn->prepare(
            "SELECT admin_id, name, email
             FROM admins WHERE admin_id = ?"
        );
        $stmt->bind_param("i", $tokenRow["admin_id"]);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin) {
            session_regenerate_id(true);
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["student_name"] = $admin["name"];
            $_SESSION["student_email"] = $admin["email"];
            $_SESSION["role"] = "admin";
            $_SESSION["last_activity"] = time();
        }
    }

    if (
        !isset($_SESSION["student_id"]) &&
        !isset($_SESSION["admin_id"])
    ) {
        $stmt = $conn->prepare(
            "DELETE FROM remember_tokens WHERE token_hash = ?"
        );
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $stmt->close();

        setcookie("remember_token", "", [
            "expires" => time() - 3600,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }
}


 // Redirect logged-in users unless access was denied
if (!isset($_GET["denied"])) {
    if (isset($_SESSION["admin_id"]) && ($_SESSION["role"] ?? "") === "admin") {
        header("Location: admin_dashboard.php");
        exit;
    }

    if (isset($_SESSION["student_id"]) && ($_SESSION["role"] ?? "") === "student") {
        header("Location: dashboard.php");
        exit;
    }
}


// Process login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    if ($role === "student") {
        try {
            $stmt = $conn->prepare(
                "SELECT student_id, name, email, password
                 FROM students WHERE email = ?"
            );
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($student && password_verify($password, $student["password"])) {
                session_regenerate_id(true);

                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["student_name"] = $student["name"];
                $_SESSION["student_email"] = $student["email"];
                $_SESSION["role"] = "student";
                $_SESSION["last_activity"] = time();

                if (($_POST["remember"] ?? "") === "1") {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash("sha256", $token);
                    $expires = date("Y-m-d H:i:s", time() + 2592000);

                    $stmt = $conn->prepare(
                        "INSERT INTO remember_tokens
                         (student_id, token_hash, expires_at)
                         VALUES (?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "iss",
                        $student["student_id"],
                        $tokenHash,
                        $expires
                    );
                    $stmt->execute();
                    $stmt->close();

                    setcookie("remember_token", $token, [
                        "expires" => time() + 2592000,
                        "path" => "/",
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]);
                }

                header("Location: dashboard.php");
                exit;
            }

            $message = "Invalid email or password.";
        } catch (mysqli_sql_exception $e) {
            error_log("StudentHub login error: " . $e->getMessage());
            $message = "Login failed because of a database error. Please try again.";
        }
    } elseif ($role === "admin") {
        try {
            $stmt = $conn->prepare(
                "SELECT admin_id, name, email, password
                 FROM admins WHERE email = ?"
            );
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $admin = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($admin && password_verify($password, $admin["password"])) {
                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["admin_id"];
                $_SESSION["student_name"] = $admin["name"];
                $_SESSION["student_email"] = $admin["email"];
                $_SESSION["role"] = "admin";
                $_SESSION["last_activity"] = time();

                if (($_POST["remember"] ?? "") === "1") {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash("sha256", $token);
                    $expires = date("Y-m-d H:i:s", time() + 2592000);

                    $stmt = $conn->prepare(
                        "INSERT INTO remember_tokens
                         (admin_id, token_hash, expires_at)
                         VALUES (?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "iss",
                        $admin["admin_id"],
                        $tokenHash,
                        $expires
                    );
                    $stmt->execute();
                    $stmt->close();

                    setcookie("remember_token", $token, [
                        "expires" => time() + 2592000,
                        "path" => "/",
                        "httponly" => true,
                        "samesite" => "Lax"
                    ]);
                }

                header("Location: admin_dashboard.php");
                exit;
            }

            $message = "Invalid email or password.";
        } catch (mysqli_sql_exception $e) {
            error_log("StudentHub admin login error: " . $e->getMessage());
            $message = "Login failed because of a database error. Please try again.";
        }
    } else {
        $message = "Please select a valid role.";
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
    <p>Login to access your account.</p>
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
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember Me</label>
                </p>

                <p>
                    <button type="submit">Login</button>
                    <button type="reset">Clear</button>
                </p>
            </fieldset>
        </form>

        <p role="status" aria-live="polite">
            <?php if (isset($_GET["denied"])): ?>
                <p>You do not have permission to access that dashboard.</p>
            <?php endif; ?>
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
