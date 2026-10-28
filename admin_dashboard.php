<?php

error_reporting(E_ALL);

ini_set("display_errors", 1);

session_start();

require_once "db_connect.php";


/*
 * Admin Dashboard
 *
 * Displays the dashboard for a logged-in admin.
 */


// Check whether the user is logged in

if (!isset($_SESSION["userId"])) {

    header("Location: login.php");

    exit();

}


// Check whether the logged-in user is an admin

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {

    header("Location: student_dashboard.php");

    exit();

}


// Get admin username

$username = $_SESSION["username"] ?? "Admin";

?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        Admin Dashboard - IGNOU Learning Management System
    </title>


    <link
        rel="stylesheet"
        href="style.css?v=2"
    >

</head>


<body class="admin-dashboard-page">


<!--
    HEADER SECTION
-->

<div class="admin-dashboard-header">


    <h2>

        Learning Management System for IGNOU Students

    </h2>


    <a
        href="logout.php"
        class="admin-dashboard-logout"
    >

        Logout

    </a>


</div>



<div class="admin-dashboard-container">


    <!--
        WELCOME SECTION
    -->

    <div class="admin-dashboard-welcome">


        <h1>

            Welcome,
            <?php echo htmlspecialchars($username); ?>!

        </h1>


        <p>

            Welcome to the Admin Dashboard.
            You can manage study materials,
            quizzes and monitor student performance
            from here.

        </p>


    </div>



    <!--
        ADMIN DASHBOARD OPTIONS
    -->

    <div class="admin-dashboard-cards">



        <!--
            STUDY MATERIALS
        -->

        <div class="admin-dashboard-card">


            <h3>

                Study Materials

            </h3>


            <p>

                Add and manage study materials
                for students.

            </p>


            <a
                href="admin_materials.php"
                class="admin-dashboard-button"
            >

                Manage Materials

            </a>


        </div>



        <!--
            QUIZ MANAGEMENT
        -->

        <div class="admin-dashboard-card">


            <h3>

                Quiz Management

            </h3>


            <p>

                Add and manage online quizzes
                for students.

            </p>


            <a
                href="admin_quizzes.php"
                class="admin-dashboard-button"
            >

                Manage Quizzes

            </a>


        </div>



        <!--
            STUDENT PERFORMANCE
        -->

        <div class="admin-dashboard-card">


            <h3>

                Student Performance

            </h3>


            <p>

                Monitor quiz performance
                of students.

            </p>


            <a
                href="admin_student_performance.php"
                class="admin-dashboard-button"
            >

                Monitor Performance

            </a>


        </div>



    </div>



</div>


</body>

</html>