<?php

session_start();


/*
 * Student Dashboard
 *
 * Purpose:
 * Displays the dashboard of an authenticated student.
 */


/*
 * Check whether the student is logged in.
 */
if (!isset($_SESSION["userId"])) {

    header("Location: login.php");
    exit();
}
// Check whether the logged-in user is a student
if ($_SESSION["role"] !== "Student") {

    header("Location: login.php");
    exit();
}


/*
 * Get student details stored in the session.
 */
$userId = (int) $_SESSION["userId"];

$username = $_SESSION["username"] ?? "Student";


/*
 * Make sure the username is not empty.
 */
if (trim($username) === "") {

    $username = "Student";
}

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
        Student Dashboard - IGNOU Learning Management System
    </title>

    <!-- Common stylesheet -->
    <link
        rel="stylesheet"
        href="style.css?v=4"
    >

</head>


<body class="dashboard-page">


<!-- Header section -->

<div class="dashboard-header">

    <div class="dashboard-header-inner">

        <h2>
            Learning Management System for IGNOU Students
        </h2>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>


<div class="dashboard-container">


    <!-- Welcome section -->

    <div class="welcome">

        <h1>

            Welcome,

            <?php
            echo htmlspecialchars(
                $username,
                ENT_QUOTES,
                "UTF-8"
            );
            ?>!

        </h1>


        <p>

            Welcome to your Student Dashboard.

            You can access study resources,
            previous year papers and online quizzes
            from here.

        </p>

    </div>


    <!-- Dashboard cards -->

    <div class="cards">


        <!-- Study Materials -->

        <div class="card">

            <h3>
                Study Materials
            </h3>


            <p>
                Access subject-wise study notes,
                learning resources, previous year papers
                and solved papers.
            </p>


            <a
                href="study_material.php"
                class="card-button"
            >

                View Materials

            </a>

        </div>


        <!-- Online Quiz -->

        <div class="card">

            <h3>
                Online Quiz
            </h3>


            <p>

                Take online quizzes and
                check your performance.

            </p>


            <a
                href="quiz_list.php"
                class="card-button"
            >

                Take Quiz

            </a>

        </div>


        <!-- My Profile -->

        <div class="card">

            <h3>
                My Profile
            </h3>


            <p>

                View your student details
                and quiz performance.

            </p>


            <a
                href="profile.php"
                class="card-button"
            >

                View Profile

            </a>

        </div>


    </div>


</div>


</body>

</html>