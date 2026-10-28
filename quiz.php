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
 * Reset quiz session when the student quits the quiz.
 */
if (
    isset($_GET["quit"]) &&
    $_GET["quit"] == "1"
) {

    unset($_SESSION["quiz_start_time"]);
    unset($_SESSION["quiz_start_quiz_id"]);

    header("Location: quiz_list.php");
    exit();

}
/*
 * Get Quiz ID from URL.
 */
$quizId = isset($_GET["quizId"])
    ? intval($_GET["quizId"])
    : 0;


/*
 * Validate Quiz ID.
 */
if ($quizId <= 0) {

    die("Invalid quiz selected.");

}


/*
 * Check whether Start Quiz was clicked.
 */
$startQuiz =
    isset($_GET["start"]) &&
    $_GET["start"] == "1";


/*
 * Fetch quiz details.
 */
$quizSql = "SELECT quizId, quizTitle, totalMarks, timeLimit
            FROM quiz
            WHERE quizId = ?";


$stmt = mysqli_prepare(
    $conn,
    $quizSql
);


if (!$stmt) {

    die("Unable to load quiz. Please try again later.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $quizId
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    die("Unable to load quiz. Please try again later.");

}


$quizResult = mysqli_stmt_get_result($stmt);


if (
    !$quizResult ||
    mysqli_num_rows($quizResult) === 0
) {

    mysqli_stmt_close($stmt);

    die("Quiz not found.");

}


$quiz = mysqli_fetch_assoc($quizResult);


mysqli_stmt_close($stmt);


/*
 * Store questions.
 */
$questions = [];


if ($startQuiz) {


    /*
     * Create quiz start time only once.
     */
    if (
        !isset($_SESSION["quiz_start_time"]) ||
        !isset($_SESSION["quiz_start_quiz_id"]) ||
        $_SESSION["quiz_start_quiz_id"] != $quizId
    ) {

        $_SESSION["quiz_start_time"] = time();

        $_SESSION["quiz_start_quiz_id"] = $quizId;

    }


    /*
     * Fetch quiz questions.
     */
    $questionSql = "SELECT quesId,
                           questionText,
                           optionA,
                           optionB,
                           optionC,
                           optionD
                    FROM question
                    WHERE quizId = ?
                    ORDER BY quesId ASC";


    $questionStmt = mysqli_prepare(
        $conn,
        $questionSql
    );


    if (!$questionStmt) {

        die(
            "Unable to load quiz questions. Please try again later."
        );

    }


    mysqli_stmt_bind_param(
        $questionStmt,
        "i",
        $quizId
    );


    if (!mysqli_stmt_execute($questionStmt)) {

        mysqli_stmt_close($questionStmt);

        die(
            "Unable to load quiz questions. Please try again later."
        );

    }


    $questionResult =
        mysqli_stmt_get_result($questionStmt);


    if (
        !$questionResult ||
        mysqli_num_rows($questionResult) === 0
    ) {

        mysqli_stmt_close($questionStmt);

        die(
            "No questions are available for this quiz."
        );

    }


    while ($row = mysqli_fetch_assoc($questionResult)) {


    
/*
 * Each correct answer carries 1 mark.
 */
$row["marks"] = 1;




        $questions[] = $row;

    }


    mysqli_stmt_close($questionStmt);


    /*
     * Randomise question order.
     */
    shuffle($questions);

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

        <?php

        echo htmlspecialchars(
            $quiz["quizTitle"],
            ENT_QUOTES,
            "UTF-8"
        );

        ?>

    </title>


    <link
        rel="stylesheet"
        href="style.css?v=3"
    >

</head>


<body class="quiz-page">


<!-- QUIZ HEADER -->

<div class="quiz-header">

    <div class="quiz-header-inner">

        <h2>
            Learning Management System for IGNOU Students
        </h2>


        <div class="quiz-title">

            <?php

            echo htmlspecialchars(
                $quiz["quizTitle"],
                ENT_QUOTES,
                "UTF-8"
            );

            ?>

        </div>

    </div>

</div>


<!-- MAIN CONTAINER -->

<div class="quiz-container">


<?php if (!$startQuiz): ?>


    <!-- QUIZ INTRODUCTION -->

    <div class="quiz-box">

        <h2>

            <?php

            echo htmlspecialchars(
                $quiz["quizTitle"],
                ENT_QUOTES,
                "UTF-8"
            );

            ?>

        </h2>


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


        <p>
            Please click Start Quiz to begin the test.
        </p>


        <a
            class="quiz-button"
            href="quiz.php?quizId=<?php
            echo $quizId;
            ?>&start=1"
        >
            Start Quiz
        </a>


        <br><br>


        <a
            class="quiz-back-button"
            href="quiz_list.php"
        >
            Back to Quiz List
        </a>

    </div>


<?php else: ?>


    <!-- QUIZ LAYOUT -->

    <div class="quiz-layout">


        <!-- QUESTIONS -->

        <main class="quiz-main-content">


            <form
                action="submit_quiz.php"
                method="POST"
                id="quizForm"
            >


                <input
                    type="hidden"
                    name="quizId"
                    value="<?php echo $quizId; ?>"
                >


                <?php

                $questionNumber = 1;

                foreach (
                    $questions
                    as $question
                ):

                ?>


                    <!-- QUESTION CARD -->

                    <div
                        class="quiz-question-card"
                        id="question-<?php
                        echo $questionNumber;
                        ?>"
                        data-question-number="<?php
                        echo $questionNumber;
                        ?>"
                    >


                        <h3>

                            Q<?php
                            echo $questionNumber;
                            ?>.

                            <?php

                            echo htmlspecialchars(
                                $question["questionText"],
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </h3>


                        <p>

                            <strong>
                                Marks:
                            </strong>

                            <?php
                            echo $question["marks"];
                            ?>

                        </p>


                        <!-- OPTION A -->

                        <label class="quiz-option">

                            <input
                                type="radio"
                                name="answer[<?php
                                echo $question["quesId"];
                                ?>]"
                                value="A"
                            >

                            <span>

                                <strong>A.</strong>

                                <?php

                                echo htmlspecialchars(
                                    $question["optionA"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </span>

                        </label>


                        <!-- OPTION B -->

                        <label class="quiz-option">

                            <input
                                type="radio"
                                name="answer[<?php
                                echo $question["quesId"];
                                ?>]"
                                value="B"
                            >

                            <span>

                                <strong>B.</strong>

                                <?php

                                echo htmlspecialchars(
                                    $question["optionB"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </span>

                        </label>


                        <!-- OPTION C -->

                        <label class="quiz-option">

                            <input
                                type="radio"
                                name="answer[<?php
                                echo $question["quesId"];
                                ?>]"
                                value="C"
                            >

                            <span>

                                <strong>C.</strong>

                                <?php

                                echo htmlspecialchars(
                                    $question["optionC"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </span>

                        </label>


                        <!-- OPTION D -->

                        <label class="quiz-option">

                            <input
                                type="radio"
                                name="answer[<?php
                                echo $question["quesId"];
                                ?>]"
                                value="D"
                            >

                            <span>

                                <strong>D.</strong>

                                <?php

                                echo htmlspecialchars(
                                    $question["optionD"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </span>

                        </label>


                    </div>


                <?php

                    $questionNumber++;

                endforeach;

                ?>


                <!-- FIXED BOTTOM BUTTONS -->

                <div class="quiz-bottom-buttons">

                    <button type="submit">
                        Submit Quiz
                    </button>


                    <button
                        type="button"
                        onclick="quitQuiz()"
                    >
                        Quit Quiz
                    </button>

                </div>


            </form>

        </main>


        <!-- SIDEBAR -->

        <aside class="quiz-sidebar">


            <!-- TIMER -->

            <div class="quiz-sidebar-section quiz-timer-section">

                <h3>
                    Time Remaining
                </h3>


                <div
                    id="timer"
                    class="quiz-sidebar-timer"
                >

                    <?php

                    $quizDuration =
                        intval($quiz["timeLimit"]) * 60;


                    $elapsedTime =
                        time() -
                        $_SESSION["quiz_start_time"];


                    $remainingTime =
                        $quizDuration -
                        $elapsedTime;


                    if ($remainingTime < 0) {

                        $remainingTime = 0;

                    }


                    echo sprintf(
                        "%02d:%02d",
                        floor($remainingTime / 60),
                        $remainingTime % 60
                    );

                    ?>

                </div>

            </div>


            <!-- QUESTION STATUS -->

            <div class="quiz-sidebar-section">

                <h3>
                    Question Status
                </h3>


                <div class="quiz-status-summary">


                    <!-- CURRENT -->

                    <div class="quiz-status-item">

                        <span
                            class="quiz-status-dot quiz-current-dot"
                        ></span>

                        <span>
                            Current
                        </span>

                        <span id="currentCount">
                            1
                        </span>

                    </div>


                    <!-- ANSWERED -->

                    <div class="quiz-status-item">

                        <span
                            class="quiz-status-dot quiz-answered-dot"
                        ></span>

                        <span>
                            Answered
                        </span>

                        <span id="answeredCount">
                            0
                        </span>

                    </div>


                    <!-- NOT ANSWERED -->

                    <div class="quiz-status-item">

                        <span
                            class="quiz-status-dot quiz-unanswered-dot"
                        ></span>

                        <span>
                            Not Answered
                        </span>

                        <span id="unansweredCount">

                            <?php
                            echo count($questions);
                            ?>

                        </span>

                    </div>


                </div>


                <!-- QUESTION NAVIGATOR -->

                <div
                    class="quiz-question-navigator"
                    id="questionNavigator"
                >


                    <?php

                    $navigatorNumber = 1;

                    foreach (
                        $questions
                        as $navigatorQuestion
                    ):

                    ?>


                        <button
                            type="button"
                            class="quiz-nav-number"
                            data-target="question-<?php
                            echo $navigatorNumber;
                            ?>"
                        >

                            <?php
                            echo $navigatorNumber;
                            ?>

                        </button>


                    <?php

                        $navigatorNumber++;

                    endforeach;

                    ?>


                </div>

            </div>


            <!-- LEGEND -->

            <div class="quiz-sidebar-section quiz-legend">


                <div class="quiz-legend-item">

                    <span
                        class="quiz-status-dot quiz-current-dot"
                    ></span>

                    Current

                </div>


                <div class="quiz-legend-item">

                    <span
                        class="quiz-status-dot quiz-answered-dot"
                    ></span>

                    Answered

                </div>


                <div class="quiz-legend-item">

                    <span
                        class="quiz-status-dot quiz-unanswered-dot"
                    ></span>

                    Not Answered

                </div>


            </div>


        </aside>

    </div>


<?php endif; ?>


</div>


<?php if ($startQuiz): ?>


<script>

/* QUIZ TIMER */

let timeRemaining =
    <?php
    echo max(
        0,
        ($quizDuration - $elapsedTime)
    );
    ?>;


const quizForm =
    document.getElementById("quizForm");


let quizSubmitted = false;


function displayTimer() {

    let minutes =
        Math.floor(timeRemaining / 60);


    let seconds =
        timeRemaining % 60;


    seconds =
        seconds < 10
        ? "0" + seconds
        : seconds;


    document.getElementById("timer").textContent =
        minutes + ":" + seconds;

}


function submitQuizAutomatically() {

    if (quizSubmitted) {

        return;

    }


    quizSubmitted = true;


    clearInterval(timer);


    alert(
        "Time is over. Your quiz will be submitted automatically."
    );


    quizForm.submit();

}


displayTimer();


let timer =
    setInterval(function() {


        if (timeRemaining <= 0) {

            displayTimer();

            submitQuizAutomatically();

            return;

        }


        timeRemaining--;


        displayTimer();


    }, 1000);


/* QUESTION STATUS SYSTEM */

const questionCards =
    document.querySelectorAll(
        ".quiz-question-card"
    );


const navigatorButtons =
    document.querySelectorAll(
        ".quiz-nav-number"
    );


const answeredCountElement =
    document.getElementById(
        "answeredCount"
    );


const unansweredCountElement =
    document.getElementById(
        "unansweredCount"
    );


const currentCountElement =
    document.getElementById(
        "currentCount"
    );


/*
 * Current question.
 */
let currentQuestionNumber = 1;


/*
 * Questions that have actually been visited.
 *
 * Example:
 *
 * visitedQuestions = 1,2,3,4,5...
 *
 * A question can become RED only if it
 * exists in this Set.
 */
const visitedQuestions = new Set();


/*
 * Mark question as visited.
 */
function markQuestionAsVisited(number) {

    visitedQuestions.add(
        String(number)
    );

}


/*
 * Check whether question has an answer.
 */
function isQuestionAnswered(card) {

    return (
        card.querySelector(
            'input[type="radio"]:checked'
        ) !== null
    );

}


/* UPDATE NAVIGATOR */

function updateNavigatorStates() {


    let answered = 0;


    questionCards.forEach(function(card) {


        const number =
            card.dataset.questionNumber;


        const navButton =
            document.querySelector(
                '.quiz-nav-number[data-target="question-' +
                number +
                '"]'
            );


        if (!navButton) {

            return;

        }


        const isAnswered =
            isQuestionAnswered(card);


        /*
         * Count answered questions.
         */
        if (isAnswered) {

            answered++;

        }


        /*
         * Remove ALL previous status classes.
         */
        navButton.classList.remove(
            "current",
            "answered",
            "visited-unanswered",
            "unanswered"
        );


        /*
         * CURRENT
         *
         * Current is ALWAYS BLUE.
         */
        if (
            parseInt(number) ===
            currentQuestionNumber
        ) {

            navButton.classList.add(
                "current"
            );

        }


        /*
         * ANSWERED
         *
         * Answered = GREEN.
         */
        else if (isAnswered) {

            navButton.classList.add(
                "answered"
            );

        }


        /*
         * VISITED BUT NOT ANSWERED
         *
         * Visited + no answer = RED.
         */
        else if (
            visitedQuestions.has(
                String(number)
            )
        ) {

            navButton.classList.add(
                "visited-unanswered"
            );

        }


        /*
         * NOT VISITED
         *
         * Not visited = GREY.
         */
        else {

            navButton.classList.add(
                "unanswered"
            );

        }

    });


    /*
     * Not Answered includes:
     *
     * RED questions
     * +
     * GREY questions
     *
     * So:
     *
     * Total 40
     * Answered 3
     * Not Answered 37
     */
    const unanswered =
        questionCards.length - answered;


    answeredCountElement.textContent =
        answered;


    unansweredCountElement.textContent =
        unanswered;


    /*
     * Current displays the QUESTION NUMBER.
     *
     * Example:
     * Current = 19
     */
    currentCountElement.textContent =
        currentQuestionNumber;

}


/* CHANGE CURRENT QUESTION */

function setCurrentQuestion(number) {


    const parsedNumber =
        parseInt(number);


    if (
        isNaN(parsedNumber) ||
        parsedNumber < 1 ||
        parsedNumber > questionCards.length
    ) {

        return;

    }


    /*
     * IMPORTANT:
     *
     * When leaving the previous current question,
     * mark that question as visited.
     *
     * This is what makes:
     *
     * Q1 -> Q2
     *
     * Q1 RED if Q1 has no answer.
     */
    if (
        currentQuestionNumber !==
        parsedNumber
    ) {

        markQuestionAsVisited(
            currentQuestionNumber
        );

    }


    /*
     * Mark new question as visited.
     */
    markQuestionAsVisited(
        parsedNumber
    );


    /*
     * Set new current question.
     */
    currentQuestionNumber =
        parsedNumber;


    /*
     * Immediately update colours.
     */
    updateNavigatorStates();

}


/* ANSWER SELECTION */

document
    .querySelectorAll(
        '.quiz-option input[type="radio"]'
    )
    .forEach(function(radio) {


        radio.addEventListener(
            "change",
            function() {


                const card =
                    this.closest(
                        ".quiz-question-card"
                    );


                if (card) {


                    const number =
                        card.dataset.questionNumber;


                    /*
                     * Selecting an answer means
                     * question has been visited.
                     */
                    markQuestionAsVisited(
                        number
                    );

                }


                updateNavigatorStates();

            }
        );

    });


/* INTERSECTION OBSERVER */

/*
 * Instead of relying only on the browser's
 * intersection entries, determine the question
 * closest to the top of the viewport.
 *
 * This prevents incorrect current-question
 * detection when multiple cards are visible.
 */

function detectCurrentQuestion() {


    let closestQuestion = null;

    let smallestDistance = Infinity;


    questionCards.forEach(function(card) {


        const rect =
            card.getBoundingClientRect();


        /*
         * Ignore questions completely below
         * the viewport.
         */
        if (
            rect.bottom <= 0 ||
            rect.top >= window.innerHeight
        ) {

            return;

        }


        /*
         * Distance from viewport top.
         */
        const distance =
            Math.abs(rect.top - 100);


        if (
            distance <
            smallestDistance
        ) {

            smallestDistance =
                distance;

            closestQuestion =
                card;

        }

    });


    if (closestQuestion) {


        const number =
            parseInt(
                closestQuestion.dataset.questionNumber
            );


        if (
            number !==
            currentQuestionNumber
        ) {

            setCurrentQuestion(number);

        }

    }

}


/*
 * Detect current question while scrolling.
 */
let scrollTimeout = null;


window.addEventListener(
    "scroll",
    function() {


        if (scrollTimeout !== null) {

            return;

        }


        scrollTimeout =
            setTimeout(function() {


                detectCurrentQuestion();


                scrollTimeout = null;


            }, 50);

    },
    {
        passive: true
    }
);


/* NAVIGATOR BUTTONS */

navigatorButtons.forEach(
    function(button) {


        button.addEventListener(
            "click",
            function() {


                const targetId =
                    this.dataset.target;


                const targetQuestion =
                    document.getElementById(
                        targetId
                    );


                if (!targetQuestion) {

                    return;

                }


                const number =
                    parseInt(
                        targetQuestion.dataset
                            .questionNumber
                    );


                /*
                 * Change current question.
                 */
                setCurrentQuestion(
                    number
                );


                /*
                 * Scroll to question.
                 */
                targetQuestion.scrollIntoView({

                    behavior: "smooth",

                    block: "start"

                });

            }
        );

    }
);


/* INITIAL STATE */

/*
 * Only Q1 is initially visited.
 *
 * Q1 = BLUE
 * Q2-Q40 = GREY
 */
markQuestionAsVisited(1);


currentQuestionNumber = 1;


updateNavigatorStates();


/* QUIT QUIZ */

function quitQuiz() {


    const confirmQuit =
        confirm(
            "Are you sure you want to quit this quiz? Your answers will not be submitted."
        );


    if (confirmQuit) {


        clearInterval(timer);


        window.location.href =
            "quiz.php?quizId=<?php echo $quizId; ?>&quit=1";

    }

}


/* FORM SUBMISSION */

quizForm.addEventListener(
    "submit",
    function() {

        quizSubmitted = true;

        clearInterval(timer);

    }
);

</script>


<?php endif; ?>


</body>

</html>