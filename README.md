Markdown
# Internship Assessment System (COMP1044 Coursework)

## 📌 Project Overview
This project is a comprehensive Web-based Database Application designed to manage and evaluate student internships. It features a robust MySQL database backend and a responsive PHP/HTML/CSS frontend, ensuring secure data handling, role-based access control, and automated score calculations.

## ✨ Key Features

### 👑 Admin Module
* **Manage Students**: Add new student records and remove existing ones.
* **Manage Assessors**: Create and authorize new evaluator accounts.
* **Assign Internships**: Assign specific students to designated assessors, recording company names and internship details.
* **Global Result Viewing**: Access and search all students' evaluation results across the system.

### 🧑‍🏫 Assessor Module
* **Student Evaluation**: Input marks based on 8 strict assessment criteria (e.g., Task Performance, Report Presentation, Time Management).
* **Auto-Calculation**: The system automatically aggregates the 8 sub-scores into a final total score (out of 100) to minimize human calculation errors.
* **Qualitative Comments**: Provide text-based feedback to justify the assigned scores.
* **Personalized View**: Assessors can only view and evaluate students specifically assigned to them.

### 🔍 System-Wide Features
* **Role-Based Authentication**: Secure login system directing Admins and Assessors to their respective dashboards.
* **Search & Filter**: Real-time searching functionality by Student ID or Student Name on the results page.
* **Data Validation**: Strict front-end HTML5 validation (min/max constraints) to prevent invalid score inputs.

---

## 📂 Folder Structure

```text
COMP1044_CW_Gx/
 ┣ Admin/                     # Admin exclusive functional pages
 ┃  ┣ manage_internships.php
 ┃  ┣ manage_students.php
 ┃  ┗ manage_users.php
 ┣ Assessor/                  # Assessor exclusive functional pages
 ┃  ┣ evaluate_student.php
 ┃  ┗ submit_marks.php
 ┣ Includes/                  # Backend configurations and shared logic
 ┃  ┗ db_connect.php          # PDO Database connection script
 ┣ SQL&diagram/               # Database deliverables
 ┃  ┣ COMP1044_Database.sql   # SQL script (DDL & DML)
 ┃  ┗ COMP1044_ERD.pdf        # Entity-Relationship Diagram (Crow's foot)
 ┣ login.php                  # System authentication gateway
 ┣ logout.php                 # Session destruction script
 ┣ view_results.php           # Shared result viewing page with search
 ┗ README.md                  # Project documentation
🚀 Installation & Setup Guide
To run this application locally for testing or grading, please follow these steps:

Environment Preparation: Ensure you have a local server environment installed (e.g., XAMPP, WAMP, or MAMP).

Start Services: Launch your control panel and start both Apache and MySQL modules.

Database Setup:

Open phpMyAdmin (http://localhost/phpmyadmin).

Import the provided SQL file located at SQL&diagram/COMP1044_Database.sql.

This script will automatically create the database (COMP1044_CW_DB), build the tables, and insert sample data.

Deploy Application:

Copy the entire project folder (COMP1044_CW_Gx) into your local server's root directory (e.g., C:\xampp\htdocs\).

Launch:

Open a web browser and navigate to: http://localhost/COMP1044_CW_Gx/login.php

🔐 Sample Login Credentials
Use the following test accounts (pre-loaded in the database) to explore the system:

Admin Account:

Username: admin_main

Password: hashed_pwd_001

Assessor Accounts:

Username: Dr_smith

Password: hashed_pwd_002

Username: Prof_jones

Password: hashed_pwd_003

👥 Team Members (Group X)
[Lin Yiwei] - Database Architect & Full-stack Logic (ERD, SQL, Core PHP integration)

[Deng Changhui] - 

[Cao Shiyu] - 
