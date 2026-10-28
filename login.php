<?php

/*
 * Login Page
 *
 * Purpose:
 * Authenticates a registered user and redirects the user
 * to the appropriate dashboard according to the assigned role.
 */

session_start();

require_once "db_connect.php";

$error_msg = "";

/*
 * Process the login form when the user submits it.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    /*
     * Validate the login fields.
     */
    if ($username === "" || $password === "") {

        $error_msg = "Please enter both username and password.";

    } else {

        /*
         * Fetch the user details using a prepared statement.
         */
        $sql = "SELECT userId, username, password, role
                FROM user
                WHERE username = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $username);

            /*
             * Execute the query and check for errors.
             */
            if ($stmt->execute()) {

                $result = $stmt->get_result();

                /*
                 * Check whether the entered username exists.
                 */
                if ($result->num_rows === 1) {

                    $user = $result->fetch_assoc();

                    /*
                     * Verify the entered password.
                     */
                    if (
                        password_verify($password, $user["password"])
                    ) {

                        /*
                         * Generate a new session ID after successful login.
                         */
                        session_regenerate_id(true);

                        $_SESSION["userId"] = $user["userId"];
                        $_SESSION["username"] = $user["username"];
                        $_SESSION["role"] = $user["role"];

                        /*
                         * Redirect the user according to the role.
                         */
                        if ($user["role"] === "Admin") {

                            $stmt->close();
                            header("Location: admin_dashboard.php");
                            exit();

                        } elseif ($user["role"] === "Student") {

                            $stmt->close();
                            header("Location: student_dashboard.php");
                            exit();

                        } else {

                            $error_msg = "Invalid user role.";

                            session_unset();
                            session_destroy();
                        }

                    } else {

                        $error_msg = "Invalid username or password.";
                    }

                } else {

                    $error_msg = "Invalid username or password.";
                }

            } else {

                $error_msg = "Unable to process login request.";
            }

            $stmt->close();

        } else {

            $error_msg = "Unable to process login request.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>LMS - Login</title>

    <link rel="stylesheet" href="style.css?v=6">

</head>

<body class="login-page">

<div class="auth-container">

    <!-- Main Login Area -->
    <div class="auth-wrapper">

        <!-- Project Title -->
        <div class="lms-header-title">
            Learning Management System<br>
            for IGNOU Students
        </div>


        <!-- Login Card -->
        <div class="auth-box">

            <h2>User Login</h2>


            <!-- Error Message -->
            <?php if ($error_msg !== ""): ?>

                <div class="error">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>

            <?php endif; ?>


            <!-- Login Form -->
            <form method="POST" action="">


                <!-- Username -->
                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-with-icon">

                        <!-- User Icon -->
                        <span class="input-icon">

                            <svg
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg">

                                <path
                                    d="M20 21C20 17.6863 17.3137 15 14 15H10C6.68629 15 4 17.6863 4 21"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />

                                <circle
                                    cx="12"
                                    cy="7"
                                    r="4"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                />

                            </svg>

                        </span>


                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?php echo htmlspecialchars($_POST["username"] ?? ""); ?>"
                            placeholder="Enter your username"
                            required
                        >

                    </div>

                </div>


                <!-- Password -->
                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-box">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >


                        <!-- Password Toggle -->
                        <button
                            type="button"
                            class="password-toggle"
                            onclick="togglePassword()"
                            aria-label="Show or hide password"
                        >

                            <span id="eye-icon">

                                <svg
                                    width="19"
                                    height="19"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg">

                                    <path
                                        d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linejoin="round"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    />

                                </svg>

                            </span>

                        </button>

                    </div>

                </div>


                <!-- Login Button -->
                <button
                    type="submit"
                    class="btn-submit"
                >
                    Login
                </button>

            </form>


            <!-- Registration Link -->
            <p class="login-footer">

                New student?
                <a href="register.php">
                    Register here
                </a>

            </p>

        </div>

    </div>

</div>


<script>

/*
 * Show or hide password.
 */
function togglePassword() {

    const password =
        document.getElementById("password");

    const eyeIcon =
        document.getElementById("eye-icon");


    if (password.type === "password") {

        password.type = "text";

        eyeIcon.innerHTML = `
            <svg
                width="19"
                height="19"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">

                <path
                    d="M3 3L21 21"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />

                <path
                    d="M10.6 10.6C10.2 11 10 11.5 10 12C10 13.1 10.9 14 12 14C12.5 14 13 13.8 13.4 13.4"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />

                <path
                    d="M9.9 5.2C10.6 5.1 11.3 5 12 5C18.5 5 22 12 22 12C22 12 20.7 14.5 18.3 16.4"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />

                <path
                    d="M6.2 7.6C3.4 9.5 2 12 2 12C2 12 5.5 19 12 19C13.8 19 15.4 18.5 16.8 17.8"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                />

            </svg>
        `;

    } else {

        password.type = "password";

        eyeIcon.innerHTML = `
            <svg
                width="19"
                height="19"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg">

                <path
                    d="M2 12C2 12 5.5 5 12 5C18.5 5 22 12 22 12C22 12 18.5 19 12 19C5.5 19 2 12 2 12Z"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linejoin="round"
                />

                <circle
                    cx="12"
                    cy="12"
                    r="3"
                    stroke="currentColor"
                    stroke-width="1.7"
                />

            </svg>
        `;
    }
}

</script>

</body>
</html>