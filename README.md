# Internship Assessment System — COMP1044 Group 10

## What is this?

A web-based system for managing and grading student internships, built for the COMP1044 coursework. The idea is pretty straightforward — instead of tracking everything in spreadsheets and calculating weighted scores by hand, this system handles it automatically.

There are two roles: **Admin** (manages students, assessors, and internship assignments) and **Assessor** (submits marks and comments for assigned students). The final score is calculated automatically based on fixed weightages.

Stack: MySQL + PHP (PDO) + HTML/CSS/JavaScript.

---

## Features

### Admin

- **Dashboard** — shows key stats at a glance: total students, assessors, how many evaluations are done vs pending, and the university-wide average. Each stat card is clickable and takes you to the relevant page.
- **Student management** — add, edit, delete students. You can also filter the list by programme.
- **Assessor management** — create and manage assessor accounts. When editing, the password field is optional — leave it blank to keep the current one.
- **Internship assignment** — assign students to assessors and record the company name and any other notes.
- **View all results** — see every student's scores in one table. Supports search, column sorting, and double-clicking a row opens a detailed breakdown with comments. Scores are colour-coded: green (70+), blue (50–69), red (below 50).
- **Export CSV** — download all evaluation results as a CSV file.

### Assessor

- **Dashboard** — shows your own workload: how many students you have, how many you've evaluated, and the average score you've given.
- **Evaluate students** — search for a student from a dropdown, enter raw scores (0–100) for each of the 8 criteria, and the page shows you the weighted total in real time. There's a confirmation popup before submitting, and once submitted the scores can't be changed.
- **View results** — see detailed scores for all students you've evaluated, with search and sort.

### General

- Login redirects you to the right dashboard based on your role. Accessing the wrong pages just kicks you back to login.
- All queries use PDO prepared statements — no SQL injection risk.
- Validation happens on both sides: the frontend checks input formats, the backend re-validates score ranges and required fields.
- Delete actions have a confirmation modal. Records with linked data (e.g. a student who already has an internship assigned) can't be deleted until those links are removed first.
- "Remember username" cookie on login, expires after 30 days.

---

## Folder Structure

```
COMP1044_CW_G10/
├── Admin/
│   ├── admin_help_modal.php       # Help/rubric modal
│   ├── dashboard.php              # Admin overview page
│   ├── export_results.php         # CSV export endpoint
│   ├── manage_internships.php     # Internship assignment CRUD
│   ├── manage_students.php        # Student CRUD + programme filter
│   ├── manage_users.php           # Assessor account CRUD
│   └── view_all_results.php       # All results with colour-coded grades
├── Assessor/
│   ├── assessor_dashboard.php     # Assessor overview page
│   ├── assessor_help_modal.php    # Help/rubric modal
│   ├── evaluate_student.php       # Score entry form
│   └── submit_marks.php           # Submission handler + personal results view
├── Includes/
│   └── db_connect.php             # PDO database connection
├── images/
│   └── logo.png
├── login.php
├── logout.php
├── style.css
└── README.md
```

---

## How to Run Locally

1. Install XAMPP (or WAMP / MAMP) and start both Apache and MySQL.
2. Open phpMyAdmin, go to Import, and select `COMP1044_Database.sql`. This file already includes the schema and sample data — you only need to run it once and the database is ready to go.
3. Copy the project folder into your `htdocs/` directory.
4. Open a browser and go to `http://localhost/COMP1044_CW_G10/login.php`.

---

## Test Accounts

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Assessor | `Dr_smith` | `smith123` |
| Assessor | `Prof_jones` | `jones123` |

---

## Sample Data

The imported database comes with 6 students covering different score ranges and statuses:

| Student | Programme | Assessor | Status |
|---------|-----------|----------|--------|
| Alice Wong (S2024001) | Computer Science | Dr_smith | Evaluated — 92.50 |
| Bob Chen (S2024002) | Software Engineering | Dr_smith | Evaluated — 74.00 |
| Charlie Davis (S2024003) | Information Technology | Dr_smith | **Pending** |
| Diana Lim (S2024004) | Computer Science | Prof_jones | Evaluated — 84.50 |
| Evan Taylor (S2024005) | Data Science | Prof_jones | Evaluated — 58.50 |
| Frank Wilson (S2024006) | Software Engineering | Dr_smith | Evaluated — 42.50 |

If you log in as `Dr_smith`, you'll notice a red "Evaluate [1]" badge in the nav bar — that's because Charlie hasn't been graded yet. Good for demonstrating the pending workload indicator.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, vanilla JavaScript |
| Backend | PHP 7+, PDO |
| Database | MySQL 8.0 |
| Server | Apache (XAMPP) |

---

## Team

- Lin Yiwei
- Deng Changhui
- Cao Shiyu
