<?php
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => $isSecure,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

$timeout = 900;

if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: login.php?denied=1");
    exit;
}

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

// P11-01: Generate CSRF token for student operations
if (empty($_SESSION["student_csrf"])) {
    $_SESSION["student_csrf"] = bin2hex(random_bytes(32));
}

require_once __DIR__ . "/../config/db.php";

$message = "";
$messageType = "";
$search = trim($_GET["search"] ?? "");

function clean($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function logAdminAction($conn, $admin_id, $action, $details)
{
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (admin_id, action, details)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param("iss", $admin_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // P11-01: Validate CSRF token
    $submittedCsrf = $_POST["csrf_token"] ?? "";
    if (!hash_equals($_SESSION["student_csrf"] ?? "", $submittedCsrf)) {
        $message = "Invalid security token. Please refresh the page and try again.";
        $messageType = "error";
    } else {
        $action = $_POST["action"] ?? "";

        if ($action === "add" || $action === "edit") {
            $name = trim($_POST["name"] ?? "");
            $email = strtolower(trim($_POST["email"] ?? ""));
            $mobile = trim($_POST["mobile"] ?? "");
            $course = trim($_POST["course"] ?? "");
            $year = (int) ($_POST["year"] ?? 0);
            $gender = $_POST["gender"] ?? "";
            $id = (int) ($_POST["student_id"] ?? 0);

            if (
                $name === "" ||
                strlen($name) > 100 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                !preg_match("/^[0-9]{10}$/", $mobile) ||
                $course === "" ||
                strlen($course) > 50 ||
                $year < 1 || $year > 4 ||
                !in_array($gender, ["Male", "Female", "Other"], true)
            ) {
                $message = "Please enter valid student details in all fields.";
                $messageType = "error";
            } else {
                try {
                    if ($action === "add") {
                        $password = password_hash(
                            bin2hex(random_bytes(16)),
                            PASSWORD_DEFAULT
                        );

                        $stmt = $conn->prepare(
                            "INSERT INTO students
                            (name, email, password, mobile, course, year, gender)
                            VALUES (?, ?, ?, ?, ?, ?, ?)"
                        );

                        $stmt->bind_param(
                            "sssssis",
                            $name,
                            $email,
                            $password,
                            $mobile,
                            $course,
                            $year,
                            $gender
                        );

                        $stmt->execute();
                        $newStudentId = $conn->insert_id;
                        $stmt->close();

                        logAdminAction(
                            $conn,
                            $_SESSION["admin_id"],
                            "Added Student",
                            "Added student ID: " . $newStudentId
                        );

                        $message = "Student added successfully.";
                        $messageType = "success";

                    } else {
                        $stmt = $conn->prepare(
                            "UPDATE students
                             SET name=?, email=?, mobile=?, course=?, year=?, gender=?
                             WHERE student_id=?"
                        );

                        $stmt->bind_param(
                            "ssssisi",
                            $name,
                            $email,
                            $mobile,
                            $course,
                            $year,
                            $gender,
                            $id
                        );

                        $stmt->execute();
                        $affected = $stmt->affected_rows;
                        $stmt->close();

                        logAdminAction(
                            $conn,
                            $_SESSION["admin_id"],
                            "Updated Student",
                            "Updated student ID: " . $id
                        );

                        $message = "Student updated successfully.";
                        $messageType = "success";
                    }

                } catch (mysqli_sql_exception $e) {
                    $message = ((int) $e->getCode() === 1062)
                        ? "That email address is already registered."
                        : "Unable to save the student. Please check the database.";
                    $messageType = "error";

                    error_log("Student CRUD error: " . $e->getMessage());
                }
            }
        }

        if ($action === "delete") {
            $id = (int) ($_POST["student_id"] ?? 0);

            if ($id > 0) {
                try {
                    // P11-03: Protect against deleting students linked to event registrations
                    $chkStmt = $conn->prepare(
                        "SELECT COUNT(*) AS reg_count FROM registrations WHERE student_id = ?"
                    );
                    $chkStmt->bind_param("i", $id);
                    $chkStmt->execute();
                    $regCount = (int) ($chkStmt->get_result()->fetch_assoc()["reg_count"] ?? 0);
                    $chkStmt->close();

                    if ($regCount > 0) {
                        $message = "Cannot delete student: this student is linked to " . $regCount . " event registration(s).";
                        $messageType = "error";
                    } else {
                        $stmt = $conn->prepare(
                            "DELETE FROM students WHERE student_id = ?"
                        );

                        $stmt->bind_param("i", $id);
                        $stmt->execute();

                        if ($stmt->affected_rows === 1) {
                            logAdminAction(
                                $conn,
                                $_SESSION["admin_id"],
                                "Deleted Student",
                                "Deleted student ID: " . $id
                            );

                            $message = "Student deleted successfully.";
                            $messageType = "success";
                        } else {
                            $message = "Student record was not found.";
                            $messageType = "error";
                        }

                        $stmt->close();
                    }

                } catch (mysqli_sql_exception $e) {
                    $message = "Unable to delete this student. The record may be linked to other system records.";
                    $messageType = "error";
                    error_log("Student delete error: " . $e->getMessage());
                }
            }
        }
    }
}

$editStudent = null;

if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT student_id, name, email, mobile, course, year, gender
         FROM students WHERE student_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// P11-02: Pagination setup
$recordsPerPage = 5;

// Count total matching records
$countSql = "SELECT COUNT(*) AS total FROM students";
$countParams = [];
$countTypes = "";

if ($search !== "") {
    $countSql .= " WHERE name LIKE ? OR email LIKE ? OR course LIKE ?";
    $term = "%" . $search . "%";
    $countParams = [$term, $term, $term];
    $countTypes = "sss";
}

$countStmt = $conn->prepare($countSql);
if ($countParams) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$totalRecords = (int) ($countStmt->get_result()->fetch_assoc()["total"] ?? 0);
$countStmt->close();

$totalPages = max(1, (int) ceil($totalRecords / $recordsPerPage));
$currentPage = max(1, min($totalPages, (int) ($_GET["page"] ?? 1)));
$offset = ($currentPage - 1) * $recordsPerPage;

$sql = "SELECT student_id, name, email, mobile, course, year, gender, created_at
        FROM students";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " WHERE name LIKE ? OR email LIKE ? OR course LIKE ?";
    $term = "%" . $search . "%";
    $params = [$term, $term, $term];
    $types = "sss";
}

$sql .= " ORDER BY student_id DESC LIMIT ? OFFSET ?";
$params[] = $recordsPerPage;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <header>
        <h1>Student Management</h1>
        <p>Add, view, edit, delete, search and filter student records.</p>
        <a href="admin_dashboard.php" class="btn">← Back to Admin Dashboard</a>
    </header>

    <main>

        <section>
            <h2><?= $editStudent ? "Edit Student" : "Add Student" ?></h2>

            <?php if ($message !== ""): ?>
                <p role="status" class="<?= clean($messageType) ?>"><?= clean($message) ?></p>
            <?php endif; ?>

            <!-- P11-04: Associated form labels with input IDs -->
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= clean($_SESSION["student_csrf"] ?? "") ?>">

                <input type="hidden" name="action" value="<?= $editStudent ? "edit" : "add" ?>">

                <?php if ($editStudent): ?>
                    <input type="hidden" name="student_id" value="<?= (int) $editStudent["student_id"] ?>">
                <?php endif; ?>

                <p>
                    <label for="student_name">Name:</label><br>
                    <input id="student_name" name="name" required maxlength="100"
                        value="<?= clean($editStudent["name"] ?? "") ?>">
                </p>

                <p>
                    <label for="student_email">Email:</label><br>
                    <input type="email" id="student_email" name="email" required maxlength="100"
                        value="<?= clean($editStudent["email"] ?? "") ?>">
                </p>

                <p>
                    <label for="student_mobile">Mobile:</label><br>
                    <input id="student_mobile" name="mobile" required pattern="[0-9]{10}" maxlength="10"
                        value="<?= clean($editStudent["mobile"] ?? "") ?>">
                </p>

                <p>
                    <label for="student_course">Course:</label><br>
                    <input id="student_course" name="course" required maxlength="50"
                        value="<?= clean($editStudent["course"] ?? "") ?>">
                </p>

                <p>
                    <label for="student_year">Year:</label><br>
                    <select id="student_year" name="year" required>
                        <option value="">Select year</option>

                        <?php for ($y = 1; $y <= 4; $y++): ?>
                            <option value="<?= $y ?>" <?= (string) ($editStudent["year"] ?? "") === (string) $y
                                  ? "selected" : "" ?>>
                                Year <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </p>

                <p>
                    <label for="student_gender">Gender:</label><br>
                    <select id="student_gender" name="gender" required>
                        <option value="">Select gender</option>

                        <?php foreach (["Male", "Female", "Other"] as $g): ?>
                            <option value="<?= $g ?>" <?= ($editStudent["gender"] ?? "") === $g
                                  ? "selected" : "" ?>>
                                <?= $g ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p>
                    <button type="submit">
                        <?= $editStudent ? "Update Student" : "Add Student" ?>
                    </button>

                    <?php if ($editStudent): ?>
                        <a href="manage_students.php" class="cancel-action">Cancel Edit</a>
                    <?php endif; ?>
                </p>
            </form>
        </section>

        <section>
            <h2>Student Records</h2>

            <!-- Search Form -->
            <form method="get">
                <p>
                    <label for="student_search">Search by name, email or course:</label><br>
                    <input id="student_search" name="search" value="<?= clean($search) ?>"
                        placeholder="Enter name, email or course">

                    <button type="submit">Search</button>
                    <a href="manage_students.php" class="clear-action">Clear</a>
                </p>
            </form>

            <p>Total Records: <?= $totalRecords ?> | Displaying Page <?= $currentPage ?> of <?= $totalPages ?></p>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($students->num_rows > 0): ?>
                        <?php while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int) $row["student_id"] ?></td>
                                <td><?= clean($row["name"]) ?></td>
                                <td><?= clean($row["email"]) ?></td>
                                <td><?= clean($row["mobile"]) ?></td>
                                <td><?= clean($row["course"]) ?></td>
                                <td><?= clean($row["year"]) ?></td>
                                <td><?= clean($row["gender"]) ?></td>

                                <td>
                                    <a href="?edit=<?= (int) $row["student_id"] ?>&page=<?= $currentPage ?><?= $search !== "" ? "&search=" . urlencode($search) : "" ?>"
                                        class="edit-action">Edit</a>

                                    <form method="post" class="delete-form" style="display:inline"
                                        onsubmit="return confirm('Delete this student record?');">

                                        <input type="hidden" name="csrf_token"
                                            value="<?= clean($_SESSION["student_csrf"] ?? "") ?>">

                                        <input type="hidden" name="action" value="delete">

                                        <input type="hidden" name="student_id" value="<?= (int) $row["student_id"] ?>">

                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- P11-02: Pagination Navigation -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-controls" style="margin-top: 15px; text-align: center;">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?><?= $search !== "" ? "&search=" . urlencode($search) : "" ?>"
                            class="btn">« Previous</a>
                    <?php else: ?>
                        <button disabled class="btn">« Previous</button>
                    <?php endif; ?>

                    <span style="margin: 0 10px;">Page <?= $currentPage ?> of <?= $totalPages ?></span>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?><?= $search !== "" ? "&search=" . urlencode($search) : "" ?>"
                            class="btn">Next »</a>
                    <?php else: ?>
                        <button disabled class="btn">Next »</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const statusMsg = document.querySelector('p[role="status"]');
            const savedY = sessionStorage.getItem("manage_students_scroll");

            if (savedY !== null) {
                window.scrollTo(0, parseInt(savedY, 10));
                sessionStorage.removeItem("manage_students_scroll");
            }

            if (statusMsg) {
                // Ensure success or error messages remain visible
                const rect = statusMsg.getBoundingClientRect();
                if (rect.top < 0 || rect.bottom > window.innerHeight) {
                    statusMsg.scrollIntoView({ behavior: "smooth", block: "nearest" });
                }
            }

            // Preserve scroll position on Previous, Next, Clear, Edit, and Cancel Edit
            const scrollLinks = document.querySelectorAll(
                ".pagination-controls a, a.edit-action, a.clear-action, a.cancel-action"
            );

            scrollLinks.forEach(function (link) {
                link.addEventListener("click", function () {
                    sessionStorage.setItem("manage_students_scroll", String(window.scrollY));
                });
            });

            // Also preserve scroll on delete form submission
            const deleteForms = document.querySelectorAll("form.delete-form");
            deleteForms.forEach(function (df) {
                df.addEventListener("submit", function () {
                    sessionStorage.setItem("manage_students_scroll", String(window.scrollY));
                });
            });
        });
    </script>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>