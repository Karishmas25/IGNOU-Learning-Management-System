<?php

session_start();

require_once "db_connect.php";


/*
 * Check whether the student is logged in.
 */
if (!isset($_SESSION["userId"])) {

    header("Location: login.php");
    exit();

}


/*
 * Fetch all available quizzes from the database.
 */
$sql = "SELECT quizId, quizTitle, totalMarks, timeLimit
        FROM quiz
        ORDER BY quizId ASC";


$result = mysqli_query($conn, $sql);


/*
 * Check whether the quiz query was executed successfully.
 */
if ($result === false) {

    die("Unable to load quizzes. Please try again later.");

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
        Available Quizzes
    </title>


    <!-- Common stylesheet -->

    <link
        rel="stylesheet"
        href="style.css?v=2"
    >

</head>


<body class="quiz-page">


<!-- Page header -->

<div class="quiz-header">

    <div class="quiz-header-inner">

        <h2>
            Learning Management System for IGNOU Students
        </h2>

    </div>

</div>


<div class="quiz-container">


    <!-- Available quizzes introduction -->

    <div class="quiz-box">

        <h2>
            Available Quizzes
        </h2>


        <p>
            Select a quiz below to start your online assessment.
        </p>

    </div>


    <?php

    /*
     * Check whether quizzes are available.
     */
    if (mysqli_num_rows($result) > 0) {

        while ($quiz = mysqli_fetch_assoc($result)) {

    ?>


            <!-- Quiz information -->

            <div class="quiz-question-card">

                <h3>

                    <?php
                    echo htmlspecialchars(
                        $quiz["quizTitle"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </h3>


                <p>

                    <strong>
                        Total Marks:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $quiz["totalMarks"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </p>


                <p>

                    <strong>
                        Time Limit:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $quiz["timeLimit"],
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                    minutes

                </p>


                <!-- Open the selected quiz -->

                <a
                    class="quiz-button"
                    href="quiz.php?quizId=<?php
                    echo urlencode($quiz["quizId"]);
                    ?>"
                >
                    Start Quiz
                </a>

            </div>


    <?php

        }

    } else {

    ?>


        <!-- Message when no quizzes are available -->

        <div class="quiz-box">

            <p>
                No quizzes are available at the moment.
            </p>

        </div>


    <?php

    }


    /*
     * Provide a link to return to the student dashboard.
     */

    ?>


    <a
        href="student_dashboard.php"
        class="quiz-back-button"
    >
        Back to Dashboard
    </a>


</div>


</body>

</html>