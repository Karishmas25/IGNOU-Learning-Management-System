<?php 

session_start(); 

require_once "db_connect.php"; 


/* 
 * Student Profile
 * 
 * Purpose:
 * Displays the profile information of the logged-in 
 * student and their quiz performance. 
 */ 


// Check whether the student is logged in 
if (!isset($_SESSION["userId"])) { 

    header("Location: login.php"); 
    exit(); 
} 

// Get the logged-in student's ID 
$userId = (int) $_SESSION["userId"]; 


/* 
 * Fetch student profile details. 
 */ 
$profileStmt = $conn->prepare( 
    "SELECT userId, username, role 
     FROM user 
     WHERE userId = ?" 
); 


// Check whether the query was prepared successfully 
if (!$profileStmt) { 

    die("Unable to process profile information."); 
} 


// Bind the student ID 
$profileStmt->bind_param("i", $userId); 


// Execute the profile query 
if (!$profileStmt->execute()) { 

    $profileStmt->close(); 

    die("Unable to retrieve profile information."); 
} 


// Get the profile result 
$profileResult = $profileStmt->get_result(); 


// Check whether the student record exists 
if ($profileResult->num_rows !== 1) { 

    $profileStmt->close(); 

    die("Student profile not found."); 
} 


$student = $profileResult->fetch_assoc(); 

$profileStmt->close(); 


/* 
 * Fetch the quiz performance of the student. 
 * 
 * The result table stores the student's score, 
 * while the quiz table provides the quiz title 
 * and total marks. 
 */ 
$performanceStmt = $conn->prepare( 
    "SELECT 
        q.quizTitle, 
        q.totalMarks, 
        r.score, 
        r.timeStamp 
     FROM result r 
     INNER JOIN quiz q 
        ON r.quizId = q.quizId 
     WHERE r.userId = ? 
     ORDER BY r.timeStamp DESC" 
); 


// Check whether the performance query was prepared successfully 
if (!$performanceStmt) { 

    die("Unable to process quiz performance."); 
} 


// Bind the student ID 
$performanceStmt->bind_param("i", $userId); 


// Execute the performance query 
if (!$performanceStmt->execute()) { 

    $performanceStmt->close(); 

    die("Unable to retrieve quiz performance."); 
} 


// Get the performance result 
$performanceResult = $performanceStmt->get_result(); 

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
        Student Profile 
    </title> 

    <link rel="stylesheet" href="style.css"> 

</head> 


<body class="profile-page"> 


<!-- Header section --> 

<div class="profile-header"> 

    <h2> 
        Learning Management System for IGNOU Students 
    </h2> 

</div> 


<div class="profile-container"> 


    <!-- Student profile section --> 

    <div class="profile-card"> 

        <h1> 
            Student Profile 
        </h1> 


        <p> 

            <strong> 
                Username: 
            </strong> 

            <?php 
            echo htmlspecialchars( 
                $student["username"] 
            ); 
            ?> 

        </p> 


        <p> 

            <strong> 
                User ID: 
            </strong> 

            <?php 
            echo htmlspecialchars( 
                $student["userId"] 
            ); 
            ?> 

        </p> 


        <p> 

            <strong> 
                Role: 
            </strong> 

            <?php 
            echo htmlspecialchars( 
                $student["role"] 
            ); 
            ?> 

        </p> 

    </div> 


    <!-- Quiz performance section --> 

    <div class="profile-performance-card"> 

        <h2> 
            Quiz Performance 
        </h2> 


        <?php if ($performanceResult->num_rows > 0): ?> 

            <table class="profile-performance-table"> 

                <tr> 

                    <th> 
                        Quiz 
                    </th> 

                    <th> 
                        Score 
                    </th> 

                    <th> 
                        Date 
                    </th> 

                </tr> 


                <?php while ( 
                    $performance = 
                    $performanceResult->fetch_assoc() 
                ): ?> 

                    <tr> 

                        <td> 

                            <?php 
                            echo htmlspecialchars( 
                                $performance["quizTitle"] 
                            ); 
                            ?> 

                        </td> 


                        <td> 

                            <?php 
                            echo htmlspecialchars( 
                                $performance["score"] 
                            ); 
                            ?> 

                            / 

                            <?php 
                            echo htmlspecialchars( 
                                $performance["totalMarks"] 
                            ); 
                            ?> 

                        </td> 


                        <td> 

                            <?php 
                            echo htmlspecialchars( 
                                $performance["timeStamp"] 
                            ); 
                            ?> 

                        </td> 

                    </tr> 

                <?php endwhile; ?> 

            </table> 


        <?php else: ?> 

            <div class="profile-message"> 

                No quiz attempts have been recorded yet. 

            </div> 

        <?php endif; ?> 


        <br> 


        <a 
            href="student_dashboard.php" 
            class="profile-button" 
        > 
            Back to Dashboard 
        </a> 

    </div> 


</div> 


<?php 

/* 
 * Close the performance statement. 
 */ 

$performanceStmt->close(); 

?> 

</body> 

</html>