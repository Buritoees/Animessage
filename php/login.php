<?php
// CRITICAL: Ensure NO characters (not even a space or newline) are BEFORE this tag!
session_start();
include_once "config.php"; // Database connection assumed

// Using prepared statements is a HUGE security improvement (SQL Injection fix)
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!empty($email) && !empty($password)) {

    // --- 🔑 ADMIN LOGIN OVERRIDE ---
    // **SECURITY NOTE**: This is a major security flaw for production. Remove this code block!
    if ($email === 'admin' && $password === 'admin') {
        $_SESSION['is_admin'] = true;
        $_SESSION['unique_id'] = 0; // Placeholder ID

        echo "success_admin";

        // 🔑 FIX: Clear any output buffer and exit immediately
        if (ob_get_level()) ob_end_clean();
        exit();
    }
    // --- END ADMIN LOGIN OVERRIDE ---


    // --- REGULAR USER LOGIN LOGIC (DB Check) ---
    // 🔒 SECURITY FIX: Use prepared statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT unique_id, password, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        // 🔑 SECURITY FIX: Use password_verify for secure password checking
        // This is the **recommended modern standard**.
        // You MUST ensure your registration script uses password_hash() for this to work.
        if (password_verify($password, $hashed_password)) {
            // --- Fallback for old MD5 hashes (only use for migration, REMOVE later) ---
            // } elseif (md5($password) === $hashed_password) { 
            // --------------------------------------------------------------------------

            $status = "Active now";
            $unique_id = $row['unique_id'];

            // 🔒 SECURITY FIX: Use prepared statement for update
            $stmt2 = $conn->prepare("UPDATE users SET status = ? WHERE unique_id = ?");
            $stmt2->bind_param("si", $status, $unique_id);

            if ($stmt2->execute()) {
                $_SESSION['unique_id'] = $unique_id;
                echo "success";
            } else {
                echo "Something went wrong. Please try again!";
            }
        } else {
            echo "Email or Password incorrect!";
        }
    } else {
        echo "Email or Password incorrect!";
    }
} else {
    echo "All input fields are required!";
}
