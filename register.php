<?php
/* Register Page */

require_once "db_connect.php";

$message = "";
$messageType = "";


/* Process the registration form. */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    /* Validate required fields. */
    if ($username === "" || $password === "") {

        $message = "Username and password are required.";
        $messageType = "error";

    } else {

        /* Check whether the username already exists. */
        $sql = "SELECT userId
                FROM user
                WHERE username = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $username);

            if ($stmt->execute()) {

                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $message = "Username already exists.";
                    $messageType = "error";

                } else {

                    /* Hash the password before storing it. */
                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    /* New users are registered as students. */
                    $role = "Student";


                    /* Insert the new student into the database. */
                    $insertSql = "INSERT INTO user
                                  (username, password, role)
                                  VALUES (?, ?, ?)";

                    $insertStmt = $conn->prepare($insertSql);

                    if ($insertStmt) {

                        $insertStmt->bind_param(
                            "sss",
                            $username,
                            $hashedPassword,
                            $role
                        );

                        if ($insertStmt->execute()) {

                            $message =
                                "Registration successful! Redirecting to login.";

                            $messageType = "success";

                            /*
                             * Redirect to login page
                             * after successful registration.
                             */
                            header("refresh:2;url=login.php");

                        } else {

                            $message =
                                "Unable to complete registration.";

                            $messageType = "error";
                        }

                        $insertStmt->close();

                    } else {

                        $message =
                            "Unable to process registration.";

                        $messageType = "error";
                    }
                }

                $stmt->close();

            } else {

                $message =
                    "Unable to check username.";

                $messageType = "error";

                $stmt->close();
            }

        } else {

            $message =
                "Unable to process registration request.";

            $messageType = "error";
        }
    }
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
        Student Registration
    </title>

    <link
        rel="stylesheet"
        href="style.css?v=6"
    >

</head>


<body class="register-page">


<div class="auth-container">

    <div class="auth-wrapper">


        <!-- PROJECT TITLE -->

        <div class="lms-header-title">
            Learning Management System
            for IGNOU Students
         </div>


        <!-- REGISTRATION CARD -->

        <div class="auth-box">


            <h2>
                Student Registration
            </h2>


            <?php if ($message !== ""): ?>

                <div
                    class="<?php
                        echo $messageType === "success"
                            ? "message"
                            : "error";
                    ?>"
                >

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($messageType !== "success"): ?>


                <form
                    method="POST"
                    action=""
                >


                    <!-- USERNAME -->

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>


                        <div class="input-with-icon">

                            <span class="input-icon">

                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >

                                    <path
                                        d="M20 21a8 8 0 0 0-16 0"
                                    />

                                    <circle
                                        cx="12"
                                        cy="7"
                                        r="4"
                                    />

                                </svg>

                            </span>


                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                required
                                autocomplete="username"
                            >

                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>


                        <div class="password-box">


                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword()"
                                aria-label="Show password"
                            >

                                <svg
                                    id="eyeIcon"
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >

                                    <path
                                        d="M2 12s3.5-7 10-7
                                           10 7 10 7-3.5 7-10 7
                                           S2 12 2 12z"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />

                                </svg>

                            </button>


                        </div>

                    </div>


                    <!-- REGISTER BUTTON -->

                    <button
                        type="submit"
                        class="btn-submit"
                    >
                        Register
                    </button>


                </form>


                <!-- LOGIN LINK -->

                <p class="login-footer">

                    Already have an account?

                    <a href="login.php">
                        Login here
                    </a>

                </p>


            <?php endif; ?>


        </div>

    </div>

</div>


<!-- PASSWORD SHOW / HIDE -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const eyeIcon =
        document.getElementById("eyeIcon");


    if (password.type === "password") {

        password.type = "text";

        eyeIcon.innerHTML = `
            <path
                d="M2 12s3.5-7 10-7
                   10 7 10 7-3.5 7-10 7
                   S2 12 2 12z"
            />
            <circle
                cx="12"
                cy="12"
                r="3"
            />
            <line
                x1="4"
                y1="4"
                x2="20"
                y2="20"
            />
        `;

    } else {

        password.type = "password";

        eyeIcon.innerHTML = `
            <path
                d="M2 12s3.5-7 10-7
                   10 7 10 7-3.5 7-10 7
                   S2 12 2 12z"
            />
            <circle
                cx="12"
                cy="12"
                r="3"
            />
        `;
    }
}

</script>


</body>

</html>