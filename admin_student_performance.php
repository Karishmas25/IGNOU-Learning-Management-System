<?php

session_start();

require_once "db_connect.php";


/*
 * ADMIN STUDENT PERFORMANCE
 *
 * Purpose:
 * Allows the administrator to monitor
 * the quiz performance of all students.
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


/*
 * Fetch quiz performance of all students
 */
$performanceStmt = $conn->prepare(

    "SELECT
        u.username,
        q.quizTitle,
        q.totalMarks,
        r.score,
        r.timeStamp
     
     FROM result r
     
     INNER JOIN user u
        ON r.userId = u.userId
     
     INNER JOIN quiz q
        ON r.quizId = q.quizId
     
     WHERE u.role = 'Student'
     
     ORDER BY r.timeStamp DESC"

);


// Check whether query was prepared
if (!$performanceStmt) {

    die(
        "Unable to process student performance."
    );

}


// Execute query
if (!$performanceStmt->execute()) {

    $performanceStmt->close();

    die(
        "Unable to retrieve student performance."
    );

}


// Get result
$performanceResult =
    $performanceStmt->get_result();

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
        Student Performance
    </title>


    <link
        rel="stylesheet"
        href="style.css?v=2"
    >

</head>


<body class="admin-performance-page">


<!--
    ADMIN HEADER
-->

<div class="admin-performance-header">

    <h2>
        Learning Management System for IGNOU Students
    </h2>

</div>


<!--
    MAIN CONTAINER
-->

<div class="admin-performance-container">


    <!--
        PAGE TITLE
    -->

    <div class="admin-performance-title-row">

        <h1>
            Student Performance
        </h1>


        <a
            href="admin_dashboard.php"
            class="admin-performance-back"
        >
            Back to Dashboard
        </a>

    </div>


    <!--
        PERFORMANCE CARD
    -->

    <div class="admin-performance-card">

        <h2>
            Monitor Student Performance
        </h2>


        <?php if ($performanceResult->num_rows > 0): ?>


            <div class="admin-performance-table-wrapper">


                <table
                    class="admin-performance-table"
                >


                    <thead>

                        <tr>

                            <th>
                                Student
                            </th>

                            <th>
                                Quiz
                            </th>

                            <th>
                                Score
                            </th>

                            <th>
                                Percentage
                            </th>

                            <th>
                                Performance
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while (
                            $performance =
                            $performanceResult->fetch_assoc()
                        ): ?>


                            <?php

                            /*
                             * Convert database values
                             * into numeric values.
                             */

                            $score =
                                (float) $performance["score"];

                            $totalMarks =
                                (float) $performance["totalMarks"];


                            /*
                             * Calculate percentage.
                             */

                            if ($totalMarks > 0) {

                                $percentage = round(
                                    ($score / $totalMarks) * 100,
                                    1
                                );

                            } else {

                                $percentage = 0;

                            }


                            /*
                             * Determine performance level.
                             */

                            if ($percentage >= 80) {

                                $performanceText =
                                    "Excellent";

                            } elseif ($percentage >= 60) {

                                $performanceText =
                                    "Good";

                            } elseif ($percentage >= 40) {

                                $performanceText =
                                    "Needs Improvement";

                            } else {

                                $performanceText =
                                    "More Practice Required";

                            }

                            ?>


                            <tr>


                                <!-- STUDENT -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $performance["username"]
                                    );

                                    ?>

                                </td>


                                <!-- QUIZ -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $performance["quizTitle"]
                                    );

                                    ?>

                                </td>


                                <!-- SCORE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $score
                                    );

                                    ?>

                                    /

                                    <?php

                                    echo htmlspecialchars(
                                        $totalMarks
                                    );

                                    ?>

                                </td>


                                <!-- PERCENTAGE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $percentage
                                    );

                                    ?>%

                                </td>


                                <!-- PERFORMANCE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $performanceText
                                    );

                                    ?>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $performance["timeStamp"]
                                    );

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="admin-performance-message">

                No student quiz performance has been recorded yet.

            </div>


        <?php endif; ?>


    </div>


</div>


<?php

/*
 * Close the statement.
 */

$performanceStmt->close();

?>


</body>

</html>