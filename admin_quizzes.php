<?php

session_start();

require_once "db_connect.php";


/*
 * Quiz Management
 *
 * Purpose:
 * Allows an authenticated admin to add and delete quizzes
 * and to add and delete questions for the quizzes.
 *
 * Each question carries 1 mark.
 * Total marks are automatically calculated from question count.
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


$message = "";


// Get the action submitted by the form
$action = $_POST["action"] ?? "";


/*
 * Function to update total marks
 *
 * Each question carries 1 mark.
 * Therefore, total marks = number of questions.
 */
function updateTotalMarks($conn, $quizId)
{
    $stmt = $conn->prepare(
        "UPDATE quiz
         SET totalMarks = (
             SELECT COUNT(*)
             FROM question
             WHERE question.quizId = ?
         )
         WHERE quizId = ?"
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $quizId, $quizId);

    $success = $stmt->execute();

    $stmt->close();

    return $success;
}


/*
 * Add Quiz
 */
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $action === "add_quiz"
) {

    $quizTitle = trim($_POST["quizTitle"] ?? "");
    $timeLimit = (int) ($_POST["timeLimit"] ?? 0);


    // Validate quiz details
    if (
        $quizTitle === ""
        || $timeLimit <= 0
    ) {

        $message = "Please enter valid quiz details.";

    } else {

        // A new quiz initially has zero questions and zero marks.
        $totalMarks = 0;

        $stmt = $conn->prepare(
            "INSERT INTO quiz
             (quizTitle, totalMarks, timeLimit)
             VALUES (?, ?, ?)"
        );


        if ($stmt) {

            $stmt->bind_param(
                "sii",
                $quizTitle,
                $totalMarks,
                $timeLimit
            );


            if ($stmt->execute()) {

                $message = "Quiz added successfully. Now add questions.";

            } else {

                $message = "Unable to add quiz.";
            }


            $stmt->close();

        } else {

            $message = "Unable to process the request.";
        }
    }
}


/*
 * Add Question
 */
elseif (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $action === "add_question"
) {

    $quizId = (int) ($_POST["quizId"] ?? 0);

    $questionText = trim($_POST["questionText"] ?? "");
    $optionA = trim($_POST["optionA"] ?? "");
    $optionB = trim($_POST["optionB"] ?? "");
    $optionC = trim($_POST["optionC"] ?? "");
    $optionD = trim($_POST["optionD"] ?? "");
    $correctAnswer = trim($_POST["correctAnswer"] ?? "");


    // Validate question details
    if (
        $quizId <= 0
        || $questionText === ""
        || $optionA === ""
        || $optionB === ""
        || $optionC === ""
        || $optionD === ""
        || !in_array($correctAnswer, ["A", "B", "C", "D"], true)
    ) {

        $message = "Please enter all question details correctly.";

    } else {

        // Check whether the selected quiz exists
        $checkQuiz = $conn->prepare(
            "SELECT quizId
             FROM quiz
             WHERE quizId = ?"
        );


        if (!$checkQuiz) {

            $message = "Unable to process the question.";

        } else {

            $checkQuiz->bind_param("i", $quizId);
            $checkQuiz->execute();

            $quizExists = $checkQuiz->get_result()->num_rows > 0;

            $checkQuiz->close();


            if (!$quizExists) {

                $message = "Selected quiz does not exist.";

            } else {

                $stmt = $conn->prepare(
                    "INSERT INTO question
                     (
                         quizId,
                         questionText,
                         optionA,
                         optionB,
                         optionC,
                         optionD,
                         correctAnswer
                     )
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );


                if ($stmt) {

                    $stmt->bind_param(
                        "issssss",
                        $quizId,
                        $questionText,
                        $optionA,
                        $optionB,
                        $optionC,
                        $optionD,
                        $correctAnswer
                    );


                    if ($stmt->execute()) {

                        // Recalculate total marks after adding a question
                        if (updateTotalMarks($conn, $quizId)) {

                            $message = "Question added successfully. Total marks updated.";

                        } else {

                            $message = "Question added, but total marks could not be updated. Please check the database.";
                        }

                    } else {

                        $message = "Unable to add question.";
                    }


                    $stmt->close();

                } else {

                    $message = "Unable to process the question.";
                }
            }
        }
    }
}


/*
 * Delete Question
 */
elseif (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $action === "delete_question"
) {

    $quesId = (int) ($_POST["quesId"] ?? 0);


    if ($quesId > 0) {

        // Find the quiz to which this question belongs
        $findQuestion = $conn->prepare(
            "SELECT quizId
             FROM question
             WHERE quesId = ?"
        );


        if (!$findQuestion) {

            $message = "Unable to process the request.";

        } else {

            $findQuestion->bind_param("i", $quesId);
            $findQuestion->execute();

            $questionData = $findQuestion->get_result()->fetch_assoc();

            $findQuestion->close();


            if (!$questionData) {

                $message = "Question not found.";

            } else {

                $quizId = (int) $questionData["quizId"];

                $stmt = $conn->prepare(
                    "DELETE FROM question
                     WHERE quesId = ?"
                );


                if ($stmt) {

                    $stmt->bind_param("i", $quesId);


                    if ($stmt->execute()) {

                        // Recalculate total marks after deleting a question
                        if (updateTotalMarks($conn, $quizId)) {

                            $message = "Question deleted successfully. Total marks updated.";

                        } else {

                            $message = "Question deleted, but total marks could not be updated. Please check the database.";
                        }

                    } else {

                        $message = "Unable to delete question.";
                    }


                    $stmt->close();

                } else {

                    $message = "Unable to process the request.";
                }
            }
        }

    } else {

        $message = "Invalid question selected.";
    }
}


/*
 * Delete Quiz
 *
 * Deletes the quiz's questions before deleting the quiz.
 */
elseif (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && $action === "delete_quiz"
) {

    $quizId = (int) ($_POST["quizId"] ?? 0);


    if ($quizId > 0) {

        // Start a transaction so the deletion stays consistent
        $conn->begin_transaction();


        try {

            // Delete questions belonging to the quiz
            $stmt = $conn->prepare(
                "DELETE FROM question
                 WHERE quizId = ?"
            );

            if (!$stmt) {
                throw new Exception("Unable to prepare question deletion.");
            }

            $stmt->bind_param("i", $quizId);

            if (!$stmt->execute()) {
                throw new Exception("Unable to delete quiz questions.");
            }

            $stmt->close();


            // Delete the quiz
            $stmt = $conn->prepare(
                "DELETE FROM quiz
                 WHERE quizId = ?"
            );

            if (!$stmt) {
                throw new Exception("Unable to prepare quiz deletion.");
            }

            $stmt->bind_param("i", $quizId);

            if (!$stmt->execute()) {
                throw new Exception("Unable to delete quiz.");
            }

            $deletedRows = $stmt->affected_rows;

            $stmt->close();


            if ($deletedRows === 0) {
                throw new Exception("Quiz not found.");
            }


            $conn->commit();

            $message = "Quiz deleted successfully.";

        } catch (Throwable $e) {

            $conn->rollback();

            $message = "Unable to delete quiz. Please check whether it is linked to existing results.";
        }

    } else {

        $message = "Invalid quiz selected.";
    }
}


/*
 * Get all quizzes
 */
$result = $conn->query(
    "SELECT quizId, quizTitle, totalMarks, timeLimit
     FROM quiz
     ORDER BY quizId DESC"
);


if (!$result) {

    die("Unable to retrieve quizzes.");
}


/*
 * Get all questions with their quiz titles
 */
$questionResult = $conn->query(
    "SELECT
        q.quesId,
        q.quizId,
        q.questionText,
        q.optionA,
        q.optionB,
        q.optionC,
        q.optionD,
        q.correctAnswer,
        z.quizTitle
     FROM question q
     INNER JOIN quiz z
     ON q.quizId = z.quizId
     ORDER BY q.quesId DESC"
);


if (!$questionResult) {

    die("Unable to retrieve questions.");
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

    <title>Quiz Management</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body class="quiz-management-page">


<!-- Main LMS Header -->

<div class="quiz-management-header">

    <h2>
        Learning Management System for IGNOU Students
    </h2>

</div>


<!-- Main Content -->

<div class="quiz-management-container">


    <!-- Page heading and dashboard link -->

    <div class="quiz-management-page-heading">

        <h1>
            Quiz Management
        </h1>


        <a
            href="admin_dashboard.php"
            class="quiz-management-back"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- Display message -->

    <?php if ($message !== ""): ?>

        <div class="quiz-management-message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- Add Quiz -->

    <div class="quiz-management-box">

        <h3>
            Add Quiz
        </h3>


        <form
            method="POST"
            class="quiz-management-form"
        >

            <input
                type="hidden"
                name="action"
                value="add_quiz"
            >


            <label>
                Quiz Title:
            </label>

            <input
                type="text"
                name="quizTitle"
                required
            >


            <p>
                Total marks will be calculated automatically
                according to the number of questions.
                Each question carries 1 mark.
            </p>


            <label>
                Time Limit (minutes):
            </label>

            <input
                type="number"
                name="timeLimit"
                min="1"
                required
            >


            <button
                type="submit"
                class="quiz-management-button"
            >
                Add Quiz
            </button>

        </form>

    </div>


    <!-- Available Quizzes -->

    <div class="quiz-management-box">

        <h3>
            Available Quizzes
        </h3>


        <div class="quiz-management-table-wrapper">

            <table class="quiz-management-table">

                <tr>

                    <th>Quiz Title</th>

                    <th>Total Marks</th>

                    <th>Time Limit</th>

                    <th>Action</th>

                </tr>


                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $row["quizTitle"]
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo (int) $row["totalMarks"];
                            ?>
                        </td>


                        <td>
                            <?php
                            echo (int) $row["timeLimit"];
                            ?>
                            minutes
                        </td>


                        <td>

                            <form
                                method="POST"
                                class="quiz-management-delete-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete_quiz"
                                >


                                <input
                                    type="hidden"
                                    name="quizId"
                                    value="<?php
                                    echo (int) $row["quizId"];
                                    ?>"
                                >


                                <button
                                    type="submit"
                                    class="quiz-management-delete-button"
                                    onclick="return confirm(
                                        'Delete this quiz and its questions?'
                                    );"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

    </div>


    <!-- Add Question -->

    <div class="quiz-management-box">

        <h3>
            Add Question
        </h3>


        <form
            method="POST"
            class="quiz-management-form"
        >

            <input
                type="hidden"
                name="action"
                value="add_question"
            >


            <label>
                Select Quiz:
            </label>

            <select
                name="quizId"
                class="quiz-management-select"
                required
            >

                <option value="">
                    Select Quiz
                </option>


                <?php

                $quizOptions = $conn->query(
                    "SELECT quizId, quizTitle
                     FROM quiz
                     ORDER BY quizTitle"
                );

                ?>


                <?php if ($quizOptions): ?>

                    <?php while (
                        $quiz = $quizOptions->fetch_assoc()
                    ): ?>

                        <option
                            value="<?php
                            echo (int) $quiz["quizId"];
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $quiz["quizTitle"]
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                <?php endif; ?>

            </select>


            <label>Question:</label>

            <textarea
                name="questionText"
                class="quiz-management-textarea"
                required
            ></textarea>


            <label>Option A:</label>

            <input
                type="text"
                name="optionA"
                required
            >


            <label>Option B:</label>

            <input
                type="text"
                name="optionB"
                required
            >


            <label>Option C:</label>

            <input
                type="text"
                name="optionC"
                required
            >


            <label>Option D:</label>

            <input
                type="text"
                name="optionD"
                required
            >


            <label>Correct Answer:</label>

            <select
                name="correctAnswer"
                class="quiz-management-select"
                required
            >

                <option value="">
                    Select Correct Answer
                </option>

                <option value="A">A</option>

                <option value="B">B</option>

                <option value="C">C</option>

                <option value="D">D</option>

            </select>


            <button
                type="submit"
                class="quiz-management-button"
            >
                Add Question
            </button>

        </form>

    </div>


    <!-- Available Questions -->

    <div class="quiz-management-box">

        <h3>
            Available Questions
        </h3>


        <div class="quiz-management-table-wrapper">

            <table
                class="quiz-management-table quiz-management-question-table"
            >

                <tr>

                    <th>Quiz</th>

                    <th>Question</th>

                    <th>Options</th>

                    <th>Correct Answer</th>

                    <th>Action</th>

                </tr>


                <?php while (
                    $question = $questionResult->fetch_assoc()
                ): ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $question["quizTitle"]
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $question["questionText"]
                            );
                            ?>
                        </td>


                        <td class="quiz-management-options-cell">

                            A.
                            <?php
                            echo htmlspecialchars(
                                $question["optionA"]
                            );
                            ?>

                            <br>

                            B.
                            <?php
                            echo htmlspecialchars(
                                $question["optionB"]
                            );
                            ?>

                            <br>

                            C.
                            <?php
                            echo htmlspecialchars(
                                $question["optionC"]
                            );
                            ?>

                            <br>

                            D.
                            <?php
                            echo htmlspecialchars(
                                $question["optionD"]
                            );
                            ?>

                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $question["correctAnswer"]
                            );
                            ?>
                        </td>


                        <td>

                            <form
                                method="POST"
                                class="quiz-management-delete-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete_question"
                                >


                                <input
                                    type="hidden"
                                    name="quesId"
                                    value="<?php
                                    echo (int) $question["quesId"];
                                    ?>"
                                >


                                <button
                                    type="submit"
                                    class="quiz-management-delete-button"
                                    onclick="return confirm(
                                        'Delete this question?'
                                    );"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

    </div>


</div>

</body>

</html>

