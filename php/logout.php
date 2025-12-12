<?php
session_start();
if (isset($_SESSION['unique_id'])) {
    include_once "config.php";

    // 🔑 Improvement: Use the session's unique ID as a fallback, 
    // but prioritize the logout_id from the URL as intended by chatPage.php.
    $logout_id = isset($_GET['logout_id']) ? mysqli_real_escape_string($conn, $_GET['logout_id']) : $_SESSION['unique_id'];

    // Check if we have an ID to log out
    if ($logout_id) {
        // 🔑 FIX: Correctly update the user's status in the database
        $status = "Offline now";

        // NOTE: It is critical that $conn is a valid database connection object.
        $sql = mysqli_query($conn, "UPDATE users SET status = '{$status}' WHERE unique_id={$logout_id}");

        if ($sql) {
            session_unset();
            session_destroy();
            header("location: ../login.php");
            exit();
        } else {
            // If the query failed (e.g., db error), still redirect
            session_unset();
            session_destroy();
            header("location: ../login.php");
            exit();
        }
    } else {
        // If somehow logged in but no ID is found, redirect to chat page
        header("location: ../chatPage.php");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}
