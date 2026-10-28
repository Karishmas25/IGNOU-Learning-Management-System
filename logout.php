<?php

session_start();


/*
 * Destroy the current user's session
 * after logout.
 */

session_unset();
session_destroy();


/*
 * Redirect the user to the login page.
 */

header("Location: login.php");
exit();

?>