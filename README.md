# Internship Assessment System (COMP1044 Coursework)

## 📌 Project Overview
This project is a comprehensive Web-based Database Application designed to manage and evaluate student internships. It features a robust MySQL database backend and a responsive PHP/HTML/CSS frontend, ensuring secure data handling, role-based access control, and automated score calculations.

## ✨ Key Features

### 👑 Admin Module
* **Admin Dashboard**: Overview page with real-time statistics (total students, assessors, internships, evaluations completed/pending, university average score) and a visual progress bar.
* **Manage Students**: Full CRUD operations — add, edit, delete student records. Includes **filtering by programme** for quick lookup.
* **Manage Assessors**: Full CRUD operations for assessor accounts, with optional password reset during editing.
* **Manage Internships**: Assign students to assessors, with full edit/delete support for existing assignments.
* **Global Result Viewing**: Access all students' evaluation results across the system with search, sort, and detailed view.
* **Export to CSV**: One-click export of all evaluation results to a CSV file for external reporting or archival.

### 🧑‍🏫 Assessor Module
* **Student Evaluation**: Input marks based on 8 strict assessment criteria (e.g., Task Performance, Report Presentation, Time Management).
* **Auto-Calculation**: The system automatically aggregates the 8 sub-scores into a final total score (out of 100) based on fixed weightages, minimizing human calculation errors.
* **Pending Evaluations Badge**: Navigation bar displays a red badge showing the number of students still awaiting evaluation.
* **Qualitative Comments**: Provide text-based feedback to justify the assigned scores.
* **Personalized View**: Assessors can only view and evaluate students specifically assigned to them.
* **Class Average Display**: Assessors can see the average score of students they have evaluated.

### 🔍 System-Wide Features
* **Role-Based Authentication**: Secure login system directing Admins and Assessors to their respective dashboards.
* **Secure Password Storage**: All passwords are hashed using PHP's `password_hash()` with bcrypt before being stored in the database.
* **Remember Username**: Optional cookie-based username remembering for convenience (30-day expiry).
* **Search & Filter**: Real-time searching by Student ID, Name, or Assessor across result pages; column sorting on tables.
* **Data Validation**: 
  - **Front-end**: HTML5 constraints (min/max, required), JavaScript validation for usernames, passwords, student IDs, company names.
  - **Back-end**: Server-side validation of score ranges (0–100), required fields, and numeric types — protection against form tampering.
* **SQL Injection Protection**: All database queries use PDO prepared statements.
* **Referential Integrity**: Foreign key constraints prevent deletion of records that are referenced elsewhere.

---

## 📂 Folder Structure

```text
COMP1044_CW_Gx/
 ┣ Admin/                     # Admin exclusive functional pages
 ┃  ┣ dashboard.php           # Admin overview with statistics
 ┃  ┣ manage_students.php     # Student CRUD + programme filter
 ┃  ┣ manage_internships.php  # Internship assignment CRUD
 ┃  ┣ manage_users.php        # Assessor account CRUD
 ┃  ┣ view_all_results.php    # Global results view
 ┃  ┗ export_results.php      # CSV export endpoint
 ┣ Assessor/                  # Assessor exclusive functional pages
 ┃  ┣ evaluate_student.php    # Score entry form
 ┃  ┗ submit_marks.php        # Submission handler + personal results view
 ┣ Includes/                  # Backend configurations and shared logic
 ┃  ┗ db_connect.php          # PDO Database connection script
 ┣ images/                    # Logo and static assets
 ┃  ┗ logo.png
 ┣ SQL&diagram/               # Database deliverables
 ┃  ┣ COMP1044_Database.sql   # SQL script (DDL & DML)
 ┃  ┗ COMP1044_ERD.pdf        # Entity-Relationship Diagram (Crow's foot)
 ┣ login.php                  # System authentication gateway
 ┣ logout.php                 # Session destruction script
 ┣ style.css                  # Global stylesheet (Moodle-inspired design)
 ┗ README.md                  # Project documentation
```

---

## 🚀 Installation & Setup Guide

To run this application locally for testing or grading, please follow these steps:

### 1. Environment Preparation
Ensure you have a local server environment installed (e.g., **XAMPP**, WAMP, or MAMP) with **PHP 7.0+** and **MySQL**.

### 2. Start Services
Launch your control panel and start both **Apache** and **MySQL** modules.

### 3. Database Setup ⚠️ Important — must be done before first login
1. Open phpMyAdmin at `http://localhost/phpmyadmin`.
2. Click the **Import** tab.
3. Choose the file located at `SQL&diagram/COMP1044_DB.sql`
4. Click **Go** to execute. This script will:
   - Create the database `COMP1044_CW_DB`.
   - Build all required tables (`Users`, `Students`, `Internships`, `Assessments`).
   - Insert sample data including pre-configured user accounts with securely hashed passwords.

### 4. Deploy Application
Copy the entire project folder (`COMP1044_CW_Gx`) into your local server's root directory (e.g., `C:\xampp\htdocs\`).

### 5. Launch
Open a web browser and navigate to:
`http://localhost/COMP1044_CW_Gx/login.php`

---

## 🔐 Sample Login Credentials

The following test accounts are pre-loaded in the SQL file. **You must import the database first (Step 3 above) before you can log in.**

> 🔒 **Security Note**: The passwords shown below are **plain-text passwords for login**. The database itself stores them as bcrypt hashes — the system hashes your input and compares it to the stored hash via `password_verify()`.

### Admin Account
| Username | Password |
|----------|----------|
| `admin_main` | `hashed_pwd_001` |

### Assessor Accounts
| Username | Password |
|----------|----------|
| `Dr_smith`   | `hashed_pwd_002` |
| `Prof_jones` | `hashed_pwd_003` |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3 (custom Moodle-style design), JavaScript (vanilla) |
| Backend | PHP 7+ (PDO for database access) |
| Database | MySQL 5.7+ / MariaDB |
| Server | Apache (XAMPP) |

---

## 👥 Team Members (Group X) ⚠️ 记得改成实际组号

* **[Lin Yiwei]** — Database Architect & Full-stack Logic (ERD, SQL, Core PHP integration)
* **[Deng Changhui]** — ⚠️ 补上职责
* **[Cao Shiyu]** — Back-End Development (PHP logic, authentication, data handling)