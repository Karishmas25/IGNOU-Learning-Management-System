<?php

session_start();

require_once "db_connect.php";


/*
 * Submit Quiz
 *
 * Purpose:
 * Processes the submitted quiz answers, calculates
 * the score and performance, saves the result in
 * the database and stores review information.
 */


/*
 * Check whether the student is logged in.
 */
if (!isset($_SESSION["userId"])) {

    header("Location: login.php");
    exit();
}


/*
 * Allow only POST requests.
 *
 * Quiz answers are submitted using the POST method.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: quiz_list.php");
    exit();
}


/*
 * Get the logged-in student's ID.
 */
$userId = (int) $_SESSION["userId"];


/*
 * Get the selected quiz ID.
 */
$quizId = filter_input(
    INPUT_POST,
    "quizId",
    FILTER_VALIDATE_INT
);


/*
 * Validate the quiz ID.
 */
if ($quizId === false || $quizId === null || $quizId <= 0) {

    die("Invalid quiz information.");
}


/*
 * Get the answers submitted by the student.
 */
$answers = $_POST["answer"] ?? [];


/*
 * Answers should be stored as an array.
 */
if (!is_array($answers)) {

    $answers = [];
}


/*
 * Fetch quiz details.
 */
$quizStmt = $conn->prepare(
    "SELECT quizId, quizTitle, totalMarks
     FROM quiz
     WHERE quizId = ?"
);


/*
 * Check whether the quiz query was prepared.
 */
if (!$quizStmt) {

    die("Unable to process the quiz.");
}


/*
 * Bind the quiz ID.
 */
$quizStmt->bind_param(
    "i",
    $quizId
);


/*
 * Execute the quiz query.
 */
if (!$quizStmt->execute()) {

    $quizStmt->close();

    die("Unable to retrieve quiz information.");
}


/*
 * Get the quiz result.
 */
$quizResult = $quizStmt->get_result();


/*
 * Check whether the quiz exists.
 */
if ($quizResult->num_rows !== 1) {

    $quizStmt->close();

    die("Quiz not found.");
}


/*
 * Store quiz information.
 */
$quiz = $quizResult->fetch_assoc();


/*
 * Close the quiz statement.
 */
$quizStmt->close();


/*
 * Fetch all questions and correct answers
 * for the selected quiz.
 */
$questionStmt = $conn->prepare(
    "SELECT quesId, questionText,
            optionA, optionB, optionC, optionD,
            correctAnswer
     FROM question
     WHERE quizId = ?
     ORDER BY quesId ASC"
);


/*
 * Check whether the question query
 * was prepared successfully.
 */
if (!$questionStmt) {

    die("Unable to retrieve quiz questions.");
}


/*
 * Bind the quiz ID.
 */
$questionStmt->bind_param(
    "i",
    $quizId
);


/*
 * Execute the question query.
 */
if (!$questionStmt->execute()) {

    $questionStmt->close();

    die("Unable to retrieve quiz questions.");
}


/*
 * Get the question result.
 */
$questionResult = $questionStmt->get_result();


/*
 * Check whether the quiz contains questions.
 */
if ($questionResult->num_rows === 0) {

    $questionStmt->close();

    die("No questions are available for this quiz.");
}


/*
 * Initialise quiz result variables.
 */
$score = 0;

$correctAnswers = 0;

$wrongAnswers = 0;

$unattempted = 0;


/*
 * Array used by the review page.
 */
$reviewData = [];


/*
 * Check each question submitted in the quiz.
 */
while ($question = $questionResult->fetch_assoc()) {


    /*
     * Get the question ID.
     */
    $questionId = (int) $question["quesId"];


   /*
 * Each correct answer carries 1 mark.
 */
$marks = 1;


    /*
     * Get the student's selected answer.
     *
     * If the student did not answer the question,
     * an empty value is used.
     */
    $studentAnswer = $answers[$questionId] ?? "";


    $studentAnswer = strtoupper(
        trim((string) $studentAnswer)
    );


    /*
     * Get the correct answer from the database.
     */
    $correctAnswer = strtoupper(
        trim($question["correctAnswer"])
    );


    /*
     * Compare the student's answer
     * with the correct answer.
     */
    if ($studentAnswer === "") {

        $unattempted++;

        $status = "unattempted";

    } elseif ($studentAnswer === $correctAnswer) {

        $score += $marks;

        $correctAnswers++;

        $status = "correct";

    } else {

        $wrongAnswers++;

        $status = "wrong";
    }


    /*
     * Store the answer details for the
     * review_answers.php page.
     */
    $reviewData[$questionId] = [

        "studentAnswer" => $studentAnswer,

        "correctAnswer" => $correctAnswer,

        "status" => $status
    ];
}


/*
 * Close the question statement.
 */
$questionStmt->close();


/*
 * Calculate the number of attempted questions.
 */
$attempted =
    $correctAnswers + $wrongAnswers;


/*
 * Calculate accuracy.
 */
if ($attempted > 0) {

    $accuracy = round(
        ($correctAnswers / $attempted) * 100,
        1
    );

} else {

    $accuracy = 0;
}


/*
 * Store quiz review information in the session.
 *
 * review_answers.php uses this information
 * to display the student's answers.
 */
$_SESSION["review_quiz_id"] = $quizId;

$_SESSION["review_answers"] = $reviewData;


/*
 * Save the quiz result in the database.
 */
$resultStmt = $conn->prepare(
    "INSERT INTO result
        (userId, quizId, score, timeStamp)
     VALUES
        (?, ?, ?, NOW())"
);


/*
 * Check whether the result query was prepared.
 */
if (!$resultStmt) {

    die("Unable to save the quiz result.");
}


/*
 * Bind student ID, quiz ID and score.
 */
$resultStmt->bind_param(
    "iid",
    $userId,
    $quizId,
    $score
);


/*
 * Execute the result query.
 */
if (!$resultStmt->execute()) {

    $resultStmt->close();

    die("Unable to save your quiz result.");
}


/*
 * Close the result statement.
 */
$resultStmt->close();


/*
 * Get the total marks of the quiz.
 */
$totalMarks = (float) $quiz["totalMarks"];


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
 * Prepare a performance message according
 * to the percentage obtained.
 */
if ($percentage >= 80) {

    $performance = "Excellent Performance";

    $performanceMessage =
        "You have demonstrated excellent understanding of the concepts.";

} elseif ($percentage >= 60) {

    $performance = "Good Performance";

    $performanceMessage =
        "You have demonstrated a good understanding of the concepts.";

} elseif ($percentage >= 40) {

    $performance = "Needs Improvement";

    $performanceMessage =
        "Some improvement is needed. Review the incorrect answers.";

} else {

    $performance = "More Practice Required";

    $performanceMessage =
        "More practice is required. Review the answers and try again.";
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
        Quiz Result
    </title>


    <!-- Common stylesheet used by the LMS pages -->
    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body class="result-page">


<div class="result-header">

    <h2>
        Learning Management System for IGNOU Students
    </h2>

</div>


<div class="result-container">


    <div class="result-card">


        <h2>
            Quiz Submitted Successfully
        </h2>


        <p>

            <strong>
                <?php

                echo htmlspecialchars(
                    $quiz["quizTitle"]
                );

                ?>
            </strong>

        </p>


        <div class="result-score">

            Score:

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

        </div>


        <div class="result-percentage">

            Percentage:

            <?php
            echo htmlspecialchars(
                $percentage
            );
            ?>%

        </div>


        <h3>
            Performance
        </h3>


        <div class="result-progress-box">

            <div
                class="result-progress-bar"
                style="width: <?php echo $percentage; ?>%;"
            ></div>

        </div>


        <div class="result-stats">


            <div class="result-stat">

                <div class="result-stat-number">

                    <?php
                    echo htmlspecialchars(
                        $correctAnswers
                    );
                    ?>

                </div>

                <div class="result-stat-label">
                    Correct Answers
                </div>

            </div>


            <div class="result-stat">

                <div class="result-stat-number">

                    <?php
                    echo htmlspecialchars(
                        $wrongAnswers
                    );
                    ?>

                </div>

                <div class="result-stat-label">
                    Wrong Answers
                </div>

            </div>


            <div class="result-stat">

                <div class="result-stat-number">

                    <?php
                    echo htmlspecialchars(
                        $unattempted
                    );
                    ?>

                </div>

                <div class="result-stat-label">
                    Unattempted
                </div>

            </div>


            <div class="result-stat">

                <div class="result-stat-number">

                    <?php

                    echo htmlspecialchars(
                        $correctAnswers
                        + $wrongAnswers
                        + $unattempted
                    );

                    ?>

                </div>

                <div class="result-stat-label">
                    Total Questions
                </div>

            </div>


            <div class="result-stat">

                <div class="result-stat-number">

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

                </div>

                <div class="result-stat-label">
                    Marks Obtained
                </div>

            </div>


            <div class="result-stat">

                <div class="result-stat-number">

                    <?php
                    echo htmlspecialchars(
                        $accuracy
                    );
                    ?>%

                </div>

                <div class="result-stat-label">
                    Accuracy
                </div>

            </div>


        </div>


        <div class="result-message">

            <strong>

                <?php
                echo htmlspecialchars(
                    $performance
                );
                ?>

            </strong>

            <br><br>

            <?php

            echo htmlspecialchars(
                $performanceMessage
            );

            ?>

        </div>


        <a
            href="review_answers.php"
            class="result-button"
        >
            Review Answers
        </a>


        <a
            href="quiz_list.php"
            class="result-button"
        >
            Back to Quizzes
        </a>


        <a
            href="student_dashboard.php"
            class="result-button"
        >
            Dashboard
        </a>


    </div>


</div>


</body>

</html>