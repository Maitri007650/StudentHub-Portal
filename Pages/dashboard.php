<?php
require_once __DIR__ . "/auth.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/style.css">

    <title>StudentHub Portal - Dashboard</title>
</head>

<body>
    <header>
        <h1>Student Dashboard</h1>
        <div class="logout-container">
            <form action="logout.php" method="post" style="display:inline;">
                <input type="hidden" name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION["logout_csrf"] ?? "", ENT_QUOTES, "UTF-8") ?>">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </header>


    <button id="menuToggle" type="button" aria-expanded="false" aria-controls="mainNav"
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

        <section>
            <h2>Welcome Student</h2>
            <p>
                Welcome to your StudentHub Dashboard.
                Here you can access important student information and services.
            </p>
        </section>

        <section>
            <h2>Quick Statistics</h2>

            <div class="feature-grid">

                <article class="feature-card">
                    <h3>Attendance</h3>
                    <p>85%</p>
                </article>

                <article class="feature-card">
                    <h3>Pending Assignments</h3>
                    <p>3</p>
                </article>

                <article class="feature-card">
                    <h3>Upcoming Events</h3>
                    <p>2</p>
                </article>

            </div>
        </section>

        <section>
            <h2>Recent Activity</h2>
            <p>No recent activity available.</p>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal. All rights reserved.</p>
    </footer>
    <script src="../js/script.js"></script>
</body>

</html>