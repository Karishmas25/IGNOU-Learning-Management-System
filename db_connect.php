<?php

/*
 * Database Connection
 *
 * Purpose:
 * Establishes a connection between the PHP application
 * and the MySQL database.
 */


/*
 * Database configuration.
 */
$host = "localhost";
$username = "root";
$password = "";
$database = "lms_db";


/*
 * Enable MySQLi exceptions for database errors.
 */
mysqli_report(
    MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT
);


try {

    /*
     * Create database connection.
     */
    $conn = new mysqli(
        $host,
        $username,
        $password,
        $database
    );


    /*
     * Set character encoding.
     */
    $conn->set_charset("utf8mb4");


} catch (mysqli_sql_exception $e) {

    /*
     * Display a user-friendly error message
     * instead of showing database details.
     */
    die(
        "Unable to connect to the database. " .
        "Please try again later."
    );
}

?>