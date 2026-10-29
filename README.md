# Learning Management System for IGNOU Students

## 📌 Project Overview

The **Learning Management System (LMS) for IGNOU Students** is a web-based application developed to help students access academic resources and practice their knowledge through online quizzes.

The system provides a centralized platform where students can access study materials, previous-year question papers, and solved papers. It also allows students to attempt online quizzes, check their scores, and review their performance.

The application includes separate functionalities for students and administrators to make learning resources and academic activities easier to manage.

## 🎯 Project Objectives

* To provide a centralized platform for IGNOU study materials.
* To make study notes, previous-year question papers, and solved papers easily accessible.
* To provide an online quiz and assessment facility.
* To automatically evaluate quiz answers and calculate scores.
* To allow students to view their quiz results and performance.
* To enable administrators to manage study materials, quizzes, and student performance.

## ✨ Key Features

### 👨‍🎓 Student Module

* Student registration and login.
* Student dashboard.
* Access to study materials.
* View available online quizzes.
* Attempt quizzes with a time limit.
* Submit quiz answers and view results.
* Review quiz answers.
* View profile information.

### 🛠️ Admin Module

* Admin dashboard.
* Manage study materials.
* Manage quizzes and questions.
* Monitor student performance.

### 🔐 Authentication

* Separate student and administrator functionalities.
* Password hashing and login verification.
* Session-based access management.

## 🧰 Technologies Used

| Component       | Technology         |
| --------------- | ------------------ |
| Frontend        | HTML, CSS          |
| Backend         | PHP                |
| Database        | MySQL              |
| Local Server    | XAMPP (Apache)     |
| Code Editor     | Visual Studio Code |
| Version Control | Git and GitHub     |

## 🗄️ Database

The application uses a MySQL database named `lms_db`.

The database includes the following tables:

* **User** — Stores user account information and roles.
* **StudyMaterial** — Stores study material details.
* **Quiz** — Stores quiz information.
* **Question** — Stores quiz questions, options, and correct answers.
* **Result** — Stores quiz results and related information.

The `database.sql` file contains the SQL statements for creating the database and its tables.

## 📂 Project Structure

```text
LMS/
├── .gitignore
├── register.php
├── login.php
├── logout.php
├── hash_password_convert.php
├── hash_password_verify.php
├── db_connect.php
├── database.sql
├── student_dashboard.php
├── admin_dashboard.php
├── profile.php
├── study_material.php
├── admin_materials.php
├── quiz_list.php
├── quiz.php
├── submit_quiz.php
├── review_answers.php
├── admin_quizzes.php
├── admin_student_performance.php
├── semester_config.php
└── style.css
```

## ⚙️ Installation and Setup

Follow these steps to run the project locally:

1. Install XAMPP on your computer.
2. Start the Apache and MySQL services from the XAMPP Control Panel.
3. Copy the project folder into the `htdocs` directory.
4. Open phpMyAdmin in your browser.
5. Import the `database.sql` file to create the `lms_db` database and its tables.
6. Open `db_connect.php` and verify the database connection settings.
7. Open the project in your browser using:

   `http://localhost/LMS/`

**Note:** The folder name in the URL must match your actual project folder name. Database connection settings must match your local XAMPP configuration.

## 🎓 Project Purpose

This project has been developed as part of the Bachelor of Computer Applications (BCA) final-year project. It demonstrates the development of a database-driven web application using PHP and MySQL.

## 👩‍💻 Author

**Karishma Soni**
BCA Student
Indira Gandhi National Open University (IGNOU)

---

*This project is intended for academic and educational purposes.*
