# StudentHub Portal

StudentHub Portal is a full-featured, secure, web-based academic student portal developed as part of the Web Development Framework (WDF) subject.

The main goal of this project is to provide a unified platform for students to access academic information, college events, timetable, and personal profile self-service, alongside comprehensive administrative management tools for student and event records.

---

## Table of Contents

1. [Project Overview & Key Features](#project-overview--key-features)
2. [Technologies Used](#technologies-used)
3. [System Architecture & Implemented Practicals](#system-architecture--implemented-practicals)
4. [Prerequisites & System Requirements](#prerequisites--system-requirements)
5. [Local Installation & Setup (XAMPP)](#local-installation--setup-xampp)
6. [Database Setup & Import Instructions](#database-setup--import-instructions)
7. [Running the Application](#running-the-application)
8. [Configuration Details](#configuration-details)
9. [Demo Accounts](#demo-accounts)
10. [Events API Reference (Practical 13)](#events-api-reference-practical-13)
11. [Database Schema & ER Diagram](#database-schema--er-diagram)
12. [Security Features](#security-features)
13. [Project Directory Structure](#project-directory-structure)
14. [Author](#author)

---

## Project Overview & Key Features

- **Student Authentication & Session Management:** Secure user registration, bcrypt password hashing, session timeout enforcement (15 minutes), Remember Me with cryptographic token rotation, and POST-protected logout with CSRF tokens.

- **Personalized Student Profile (`Pages/profile.php`):** Personal student dashboard displaying the logged-in student's academic and personal records, including Student ID, course, year, contact information, and registered events. Includes self-service profile updating for permitted fields with server-side validation and CSRF protection.

- **Admin Management & CRUD:**
  - **Student Management (`Pages/manage_students.php`):** Full CRUD operations with search, filtering, server-side pagination, referential integrity safeguards against deleting students with active event registrations, and scroll position preservation across actions.
  - **Event Management (`Pages/manage_events.php`):** Add, view, edit, and delete events with secure image poster uploads, MIME type validation, file size limits, and AJAX integration.

- **RESTful Events API (`Pages/events_api.php`):** JSON API endpoints supporting `GET` requests and `POST` actions (`add`, `update`, `delete`) with administrative role enforcement and automatic audit logging.

- **Admin Dashboard & Audit Logs (`Pages/admin_dashboard.php`):** Dynamic aggregate statistics (Total Users, Total Students, Total Events), latest event registrations, and a visible administrative Audit Log tracking actions with timestamps and administrator details.

- **Responsive & Accessible Design:** HTML5 semantic markup, accessible ARIA attributes, dark theme toggle, mobile navigation drawer, and cross-browser styling.

---

## Technologies Used

### Frontend
- HTML5 (Semantic Structure & Accessibility)
- CSS3 (Flexbox, Grid, Media Queries, Light/Dark Theme Variables)
- JavaScript ES6+ (Fetch API, DOM Manipulation, Form Validation)

### Backend
- PHP 8.x (Session Management, Authentication, Role-Based Access Control, File Handling)

### Database
- MySQL / MariaDB (Relational Database with Foreign Key Constraints and Transactional Integrity)
- PHP MySQLi Extension with Parameterized Prepared Statements (`bind_param`)

### Server Environment
- Apache 2.4 (via XAMPP) with `.htaccess` security hardening

---

## System Architecture & Implemented Practicals

The portal implements course practical milestones from Practical 1 through Practical 14, along with final integration and review improvements.

- **Practical 1–4:** Requirement analysis, sitemap, wireframes, HTML5 semantic structure, CSS3 responsive layout, and navigation.
- **Practical 5:** Accessible form validation (`aria-describedby`, `aria-invalid`, `role="alert"`, and descriptive password strength meter).
- **Practical 6:** Dynamic UI, events filtering/sorting/pagination, student directory search/filter/sort, and safe DOM rendering.
- **Practical 7:** Contact form handling, server-side validation, secure CSV storage with formula injection protection, direct access prevention via `.htaccess`, and CSRF protection.
- **Practical 8:** MySQL database integration (`studenthub`), prepared statements, configurable credentials via environment variables, and SQL database export.
- **Practical 9:** Secure student registration, password hashing (`PASSWORD_DEFAULT`), duplicate email checks, and CSRF protection.
- **Practical 10:** Secure authentication, role-based access control, secure session cookies, Remember Me token generation and rotation, session timeout (15 minutes), and POST-protected logout with CSRF tokens.
- **Practical 11:** Administrator Student CRUD with CSRF protection, pagination, foreign key constraint verification, and accessible label associations.
- **Practical 12:** Event management CRUD, poster file uploads with MIME validation, 2 MB size limits, script execution defense in uploads, and safe old-poster removal after confirmed database changes.
- **Practical 13:** Asynchronous AJAX/Fetch API integration (`Pages/events_api.php`) for event management (GET, add, update, delete) and dynamic table updates without full page reloads.
- **Practical 14:** Admin dashboard with dynamic statistics, audit logging (`audit_logs`) tracking administrative actions, and role-based navigation.

### Final Review Improvements

- Converted the student profile into a personal student profile (`Pages/profile.php` and `Pages/profile_api.php`), allowing students to edit only their own permitted details.
- Implemented scroll position preservation and persistent status alerts in `Pages/manage_students.php`.
- Added a visible Recent Activity / Audit Log table in `Pages/admin_dashboard.php`.
- Created a relational database ER diagram in PNG, SVG, Mermaid, and PlantUML formats.

---

## Prerequisites & System Requirements

- **Operating System:** Windows 10/11, Linux, or macOS
- **Web Server:** Apache 2.4+
- **PHP:** Version 8.0 or higher (with `mysqli`, `fileinfo`, `session`, and `json` extensions enabled)
- **Database:** MySQL 5.7+ or MariaDB 10.4+
- **Browser:** Modern web browser (Chrome, Edge, Firefox, Safari)

---

## Local Installation & Setup (XAMPP)

### 1. Install XAMPP
Download and install XAMPP from the official website:

https://www.apachefriends.org/

Ensure Apache, PHP, and MySQL components are installed.

### 2. Clone or Copy the Project

Place the project folder inside your XAMPP web root directory:

```text
C:\xampp\htdocs\StudentHub-Portal\
```

### 3. Start Apache and MySQL

1. Open the XAMPP Control Panel.
2. Click **Start** next to Apache.
3. Click **Start** next to MySQL.
4. Ensure both services show green status indicators.

---

## Database Setup & Import Instructions

### 1. Open phpMyAdmin

Open your browser and navigate to:

http://localhost:8080/phpmyadmin/

### 2. Create the Database

1. Click the **Databases** tab.
2. Enter `studenthub` as the database name.
3. Choose the collation `utf8mb4_general_ci`.
4. Click **Create**.

### 3. Import the SQL File

1. Select the newly created `studenthub` database from the left sidebar.
2. Click the **Import** tab.
3. Click **Choose File** and select:

```text
C:\xampp\htdocs\StudentHub-Portal\database\studenthub_submission.sql
```

4. Click **Import** (or **Go**) at the bottom of the page.
5. Wait for the success message confirming that the tables and sample data have been imported.

**Important:** Use a fresh, empty database for the initial import. The submission SQL file creates the required tables and inserts fictional sample records. It does not contain commands to drop existing tables.

---

## Running the Application

1. Open your web browser.
2. Navigate to the project homepage:

```text
http://localhost:8080/StudentHub-Portal/
```

The root `index.php` automatically redirects to the Home page (`Pages/home.html`).

### Important Application Pages

| Page | URL |
|---|---|
| Home | `http://localhost:8080/StudentHub-Portal/Pages/home.html` |
| Student Registration | `http://localhost:8080/StudentHub-Portal/Pages/register.php` |
| User Login | `http://localhost:8080/StudentHub-Portal/Pages/login.php` |
| Student Dashboard | `http://localhost:8080/StudentHub-Portal/Pages/dashboard.php` |
| Student Personal Profile | `http://localhost:8080/StudentHub-Portal/Pages/profile.php` |
| College Events | `http://localhost:8080/StudentHub-Portal/Pages/events.html` |
| Contact Us | `http://localhost:8080/StudentHub-Portal/Pages/contact.php` |
| Admin Dashboard | `http://localhost:8080/StudentHub-Portal/Pages/admin_dashboard.php` |
| Admin Student Management | `http://localhost:8080/StudentHub-Portal/Pages/manage_students.php` |
| Admin Event Management | `http://localhost:8080/StudentHub-Portal/Pages/manage_events.php` |

---

## Configuration Details

Database connection parameters are located in `config/db.php`.

The application supports environment-variable configuration with XAMPP defaults:

```php
$host     = getenv("DB_HOST") ?: "localhost";
$dbname   = getenv("DB_NAME") ?: "studenthub";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASS") !== false ? getenv("DB_PASS") : "";
$port     = (int)(getenv("DB_PORT") ?: 3306);
```

### Default XAMPP Credentials

- **Host:** `localhost`
- **Username:** `root`
- **Password:** Empty by default
- **Port:** `3306`

### Environment Variable Overrides

Custom database connection settings can be configured using:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_PORT`

This allows database settings to be changed without modifying application source files.

---

## Demo Accounts

The following fictional sample accounts are provided for assessment and demonstration purposes after importing `database/studenthub_submission.sql`.

### 1. Administrator Account

- **Role:** Admin
- **Email:** `admin@studenthub.com`
- **Password:** `Admin@123`
- **Access:** Administrative dashboard, student CRUD, event CRUD, and audit log viewing.

### 2. Student Demo Account

- **Role:** Student
- **Email:** `teststudent@gmail.com`
- **Password:** `Student@123`
- **Access:** Student dashboard, personal profile viewing, permitted profile self-updates, and event registrations.

Alternatively, a new student account can be registered through `Pages/register.php`.

**Note:** These are demonstration credentials intended for a local academic project. Change them before using the application in any real deployment.

---

## Events API Reference (Practical 13)

The Events API endpoint is located at:

`Pages/events_api.php`

| Method | Endpoint | Access | Description |
|---|---|---|---|
| `GET` | `/Pages/events_api.php` | Public | Returns a JSON list of active events with dates, locations, and descriptions. |
| `POST` | `/Pages/events_api.php` (`action: "add"`) | Admin | Creates an event using title, description, event date, location, and CSRF token. Returns 201 Created and logs the action. |
| `POST` | `/Pages/events_api.php` (`action: "update"`) | Admin | Updates an existing event using event ID and event details. Returns 200 OK and logs the action. |
| `POST` | `/Pages/events_api.php` (`action: "delete"`) | Admin | Deletes an event using its event ID and CSRF token. Returns 409 Conflict if registrations exist and logs the action. |

The API uses JSON responses and supports asynchronous Fetch/AJAX operations.

---

## Database Schema & ER Diagram

The database consists of six relational tables that enforce referential integrity.

### Table Relationships

```text
[admins] 1 --- N [audit_logs]
[admins] 1 --- N [remember_tokens]
[students] 1 --- N [remember_tokens]
[students] 1 --- N [registrations] M --- 1 [events]
```

### Database Tables

- **`admins`:** Administrator accounts (`admin_id`, `name`, `email`, `password`).
- **`students`:** Student records (`student_id`, `name`, `email`, `password`, `created_at`, `mobile`, `course`, `year`, `gender`).
- **`events`:** Scheduled events (`event_id`, `title`, `description`, `event_date`, `location`, `created_at`, `poster`).
- **`registrations`:** Many-to-many junction table (`registration_id`, `student_id`, `event_id`, `registration_date`). Includes a unique constraint on `(student_id, event_id)`.
- **`remember_tokens`:** Hashed Remember Me authentication tokens (`id`, `student_id`, `token_hash`, `expires_at`, `admin_id`).
- **`audit_logs`:** Administrative audit trail (`log_id`, `admin_id`, `action`, `details`, `created_at`).

### ER Diagram Files

- **PNG Diagram:** `database/ER_Diagram.png`
- **SVG Diagram:** `database/ER_Diagram.svg`
- **Mermaid Source:** `database/ER_Diagram.mmd`
- **PlantUML Source:** `database/ER_Diagram.puml`

---

## Security Features

1. **SQL Injection Defense:** Parameterized prepared statements using MySQLi (`prepare()` and `bind_param()`).
2. **CSRF Protection:** Cryptographically secure per-session tokens and token validation using `hash_equals()` for protected operations.
3. **Password Security:** Passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT`.
4. **Session Security:** Session cookies use `httponly` and `samesite=Lax` settings, HTTPS adaptation, 15-minute inactivity timeouts, and session ID regeneration.
5. **File Upload Security:** Event poster uploads are validated using `finfo` MIME verification, restricted to JPG, PNG, and WEBP, limited to 2 MB, stored with random filenames, and protected against script execution.
6. **CSV Formula Injection Protection:** Formula prefixes are neutralized before writing contact messages to CSV. Direct web access to the storage directory is blocked using Apache access rules.
7. **Strict Authorization:** Student profile viewing and modification are scoped to the logged-in student's session ID, preventing unauthorized access to other students' records.

---

## Project Directory Structure

```text
StudentHub-Portal/
├── config/
│   └── db.php
├── css/
│   └── style.css
├── data/
│   ├── events.json
│   ├── faqs.json
│   └── students.json
├── database/
│   ├── .htaccess
│   ├── ER_Diagram.png
│   ├── ER_Diagram.svg
│   ├── ER_Diagram.mmd
│   ├── ER_Diagram.puml
│   └── studenthub_submission.sql
├── image/
│   └── student.png
├── js/
│   └── script.js
├── Pages/
│   ├── about.html
│   ├── admin.html
│   ├── admin_dashboard.php
│   ├── auth.php
│   ├── contact.html
│   ├── contact.php
│   ├── create_admin.php
│   ├── dashboard.html
│   ├── dashboard.php
│   ├── events.html
│   ├── events_api.php
│   ├── faq.html
│   ├── home.html
│   ├── login.html
│   ├── login.php
│   ├── logout.php
│   ├── manage_events.php
│   ├── manage_students.php
│   ├── profile.html
│   ├── profile.php
│   ├── profile_api.php
│   ├── register.php
│   └── timetable.html
├── storage/
│   ├── .htaccess
│   └── index.php
├── uploads/
│   └── event_posters/
│       ├── .htaccess
│       └── index.php
├── wireframe/
├── index.php
├── readme.md
└── requirement.md
```

---

## Author

**Enrollment No.:** 25CE081

**Project:** StudentHub Portal

**Subject:** Web Development Framework (WDF)
