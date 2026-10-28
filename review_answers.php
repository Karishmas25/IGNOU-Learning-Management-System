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
 * Check whether review data is available. 
 */ 
if ( 
    !isset($_SESSION["review_quiz_id"]) || 
    !isset($_SESSION["review_answers"]) 
) { 
 
    die("Review data is not available."); 
} 
 
$quizId = (int) $_SESSION["review_quiz_id"]; 
$reviewAnswers = $_SESSION["review_answers"]; 
 
 
/* 
 * Get quiz information. 
 */ 
$quizStmt = $conn->prepare( 
    "SELECT quizTitle 
     FROM quiz 
     WHERE quizId = ?" 
); 
 
if (!$quizStmt) { 
 
    die("Unable to load quiz."); 
} 
 
$quizStmt->bind_param("i", $quizId); 
 
if (!$quizStmt->execute()) { 
 
    $quizStmt->close(); 
 
    die("Unable to load quiz."); 
} 
 
$quizResult = $quizStmt->get_result(); 
 
if ($quizResult->num_rows !== 1) { 
 
    $quizStmt->close(); 
 
    die("Quiz not found."); 
} 
 
$quiz = $quizResult->fetch_assoc(); 
 
$quizStmt->close(); 
 
 
/* 
 * Get all questions of the selected quiz. 
 */ 
$questionStmt = $conn->prepare( 
    "SELECT quesId, questionText, 
            optionA, optionB, optionC, optionD, 
            correctAnswer 
     FROM question 
     WHERE quizId = ? 
     ORDER BY quesId ASC" 
); 
 
if (!$questionStmt) { 
 
    die("Unable to load questions."); 
} 
 
$questionStmt->bind_param("i", $quizId); 
 
if (!$questionStmt->execute()) { 
 
    $questionStmt->close(); 
 
    die("Unable to load questions."); 
} 
 
$questionResult = $questionStmt->get_result(); 
 
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
        Review Answers 
    </title> 
 
    <link rel="stylesheet" href="style.css"> 
 
</head> 
 
 
<body class="review-page"> 
 
<div class="review-header"> 
 
    <h2> 
        Learning Management System for IGNOU Students 
    </h2> 
 
</div> 
 
 
<div class="review-container"> 
 
    <div class="review-title-card"> 
 
        <h2> 
            Review Answers 
        </h2> 
 
        <p> 
 
            <?php 
            echo htmlspecialchars( 
                $quiz["quizTitle"] 
            ); 
            ?> 
 
        </p> 
 
    </div> 
 
 
    <?php 
 
    $questionNumber = 1; 
 
    while ($question = $questionResult->fetch_assoc()) { 
 
        $questionId = (int) $question["quesId"]; 
 
        $studentAnswer = 
            $reviewAnswers[$questionId]["studentAnswer"] 
            ?? ""; 
 
        $correctAnswer = 
            $reviewAnswers[$questionId]["correctAnswer"] 
            ?? strtoupper( 
                trim($question["correctAnswer"]) 
            ); 
 
        $status = 
            $reviewAnswers[$questionId]["status"] 
            ?? "unattempted"; 
 
    ?> 
 
        <div class="review-question-card"> 
 
            <div class="review-question-title"> 
 
                Q<?php echo $questionNumber; ?>. 
 
                <?php 
                echo htmlspecialchars( 
                    $question["questionText"] 
                ); 
                ?> 
 
            </div> 
 
 
            <?php 
 
            $options = [ 
 
                "A" => $question["optionA"], 
                "B" => $question["optionB"], 
                "C" => $question["optionC"], 
                "D" => $question["optionD"] 
 
            ]; 
 
            foreach ($options as $key => $value): 
 
                $class = "review-answer-normal"; 
 
                if ($key === $correctAnswer) { 
 
                    $class = "review-answer-correct"; 
 
                } elseif ( 
                    $key === $studentAnswer && 
                    $status === "wrong" 
                ) { 
 
                    $class = "review-answer-wrong"; 
                } 
 
            ?> 
 
                <div class="review-answer <?php echo $class; ?>"> 
 
                    <strong> 
                        <?php echo $key; ?>. 
                    </strong> 
 
                    <?php 
                    echo htmlspecialchars($value); 
                    ?> 
 
 
                    <?php if ($key === $studentAnswer): ?> 
 
                        <strong> 
                            - Your Answer 
                        </strong> 
 
                    <?php endif; ?> 
 
 
                    <?php if ($key === $correctAnswer): ?> 
 
                        <strong> 
                            - Correct Answer 
                        </strong> 
 
                    <?php endif; ?> 
 
                </div> 
 
            <?php endforeach; ?> 
 
 
            <div class="review-result"> 
 
                <?php if ($status === "correct"): ?> 
 
                    <span class="review-correct-text"> 
                        ✓ Correct Answer 
                    </span> 
 
 
                <?php elseif ($status === "wrong"): ?> 
 
                    <span class="review-wrong-text"> 
                        ✗ Wrong Answer 
                    </span> 
 
 
                <?php else: ?> 
 
                    <span class="review-unattempted-text"> 
                        Not Attempted 
                    </span> 
 
                <?php endif; ?> 
 
            </div> 
 
        </div> 
 
 
    <?php 
 
        $questionNumber++; 
    } 
 
    $questionStmt->close(); 
 
    ?> 
 
 
    <div class="review-buttons"> 
 
        <a 
            href="quiz_list.php" 
            class="review-button" 
        > 
            Back to Quizzes 
        </a> 
 
 
        <a 
            href="student_dashboard.php" 
            class="review-button" 
        > 
            Dashboard 
        </a> 
 
    </div> 
 
</div> 
 
</body> 
 
</html>