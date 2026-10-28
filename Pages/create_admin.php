
<?php
require_once __DIR__ . "/../config/db.php";

$name = "Admin";
$email = "admin@studenthub.com";
$password = password_hash("Admin@123", PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO admins (name, email, password)
     VALUES (?, ?, ?)"
);

$stmt->bind_param("sss", $name, $email, $password);
$stmt->execute();
$stmt->close();

echo "Admin account created successfully.";
?>
