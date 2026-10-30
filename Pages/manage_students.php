
<?php
session_start();

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

require_once __DIR__ . "/../config/db.php";

$message = "";
$search = trim($_GET["search"] ?? "");

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function logAdminAction($conn, $admin_id, $action, $details) {
    $stmt = $conn->prepare(
        "INSERT INTO audit_logs (admin_id, action, details)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param("iss", $admin_id, $action, $details);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "edit") {
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $mobile = trim($_POST["mobile"] ?? "");
        $course = trim($_POST["course"] ?? "");
        $year = (int)($_POST["year"] ?? 0);
        $gender = $_POST["gender"] ?? "";
        $id = (int)($_POST["student_id"] ?? 0);

        if (
            $name === "" ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !preg_match("/^[0-9]{10}$/", $mobile) ||
            $course === "" ||
            $year < 1 || $year > 4 ||
            !in_array($gender, ["Male", "Female", "Other"], true)
        ) {
            $message = "Please enter valid student details.";
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
                        $name, $email, $password, $mobile,
                        $course, $year, $gender
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

                } else {
                    $stmt = $conn->prepare(
                        "UPDATE students
                         SET name=?, email=?, mobile=?, course=?, year=?, gender=?
                         WHERE student_id=?"
                    );

                    $stmt->bind_param(
                        "ssssisi",
                        $name, $email, $mobile, $course, $year, $gender, $id
                    );

                    $stmt->execute();

                    logAdminAction(
                        $conn,
                        $_SESSION["admin_id"],
                        "Updated Student",
                        "Updated student ID: " . $id
                    );

                    $message = "Student update processed.";

                    $stmt->close();
                }

            } catch (mysqli_sql_exception $e) {
                $message = ((int)$e->getCode() === 1062)
                    ? "That email address is already registered."
                    : "Unable to save the student. Please check the database.";

                error_log("Student CRUD error: " . $e->getMessage());
            }
        }
    }

    if ($action === "delete") {
        $id = (int)($_POST["student_id"] ?? 0);

        if ($id > 0) {
            try {
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
                } else {
                    $message = "Student record was not found.";
                }

                $stmt->close();

            } catch (mysqli_sql_exception $e) {
                $message = "Unable to delete this student. The record may be linked to registrations.";
                error_log("Student delete error: " . $e->getMessage());
            }
        }
    }
}

$editStudent = null;

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT student_id, name, email, mobile, course, year, gender
         FROM students WHERE student_id = ?"
    );

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

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

$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

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
            <p role="status"><?= clean($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action"
                   value="<?= $editStudent ? "edit" : "add" ?>">

            <?php if ($editStudent): ?>
                <input type="hidden" name="student_id"
                       value="<?= (int)$editStudent["student_id"] ?>">
            <?php endif; ?>

            <label>Name</label>
            <input name="name" required maxlength="100"
                   value="<?= clean($editStudent["name"] ?? "") ?>">

            <label>Email</label>
            <input type="email" name="email" required maxlength="100"
                   value="<?= clean($editStudent["email"] ?? "") ?>">

            <label>Mobile</label>
            <input name="mobile" required pattern="[0-9]{10}"
                   maxlength="10"
                   value="<?= clean($editStudent["mobile"] ?? "") ?>">

            <label>Course</label>
            <input name="course" required
                   value="<?= clean($editStudent["course"] ?? "") ?>">

            <label>Year</label>
            <select name="year" required>
                <option value="">Select year</option>

                <?php for ($y = 1; $y <= 4; $y++): ?>
                    <option value="<?= $y ?>"
                        <?= (string)($editStudent["year"] ?? "") === (string)$y
                            ? "selected" : "" ?>>
                        Year <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select gender</option>

                <?php foreach (["Male", "Female", "Other"] as $g): ?>
                    <option value="<?= $g ?>"
                        <?= ($editStudent["gender"] ?? "") === $g
                            ? "selected" : "" ?>>
                        <?= $g ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">
                <?= $editStudent ? "Update Student" : "Add Student" ?>
            </button>

            <?php if ($editStudent): ?>
                <a href="manage_students.php">Cancel Edit</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <h2>Student Records</h2>

        <form method="get">
            <label>Search by name, email or course</label>

            <input name="search" value="<?= clean($search) ?>"
                   placeholder="Enter name, email or course">

            <button type="submit">Search</button>
            <a href="manage_students.php">Clear</a>
        </form>

        <p>Records displayed: <?= $students->num_rows ?></p>

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
            <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row["student_id"] ?></td>
                    <td><?= clean($row["name"]) ?></td>
                    <td><?= clean($row["email"]) ?></td>
                    <td><?= clean($row["mobile"]) ?></td>
                    <td><?= clean($row["course"]) ?></td>
                    <td><?= clean($row["year"]) ?></td>
                    <td><?= clean($row["gender"]) ?></td>

                    <td>
                        <a href="?edit=<?= (int)$row["student_id"] ?>">Edit</a>

                        <form method="post" style="display:inline"
                              onsubmit="return confirm('Delete this student record?');">

                            <input type="hidden" name="action" value="delete">

                            <input type="hidden" name="student_id"
                                   value="<?= (int)$row["student_id"] ?>">

                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </section>

</main>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
