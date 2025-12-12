<?php
session_start();
// Assuming 'config.php' establishes the $conn database connection
// and is required in the main chat page, or in the index file
include_once "config.php";

// 1. Check if the user is logged in
if (!isset($_SESSION['unique_id'])) {
    // Redirect to login if session is invalid
    header("location: ../login.php");
    exit();
}

// 2. Get the IDs
$outgoing_id = $_SESSION['unique_id'];

// NOTE: Using mysqli_real_escape_string for POST input, but prepared statements 
// are the best practice for security. This matches the style of the original files.
$incoming_id = mysqli_real_escape_string($conn, $_POST['incoming_id']);
$output = "";

// 3. Query the database
// Select messages and JOIN with users table to get the receiver's image for incoming messages
$sql = "SELECT * FROM messages 
            LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
            WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
            OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) 
            ORDER BY msg_id";

$query = mysqli_query($conn, $sql);

// 4. Process the results
if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {

        // Check if the message was sent by the logged-in user ($outgoing_id)
        if ($row['outgoing_msg_id'] == $outgoing_id) {
            // Render as OUTGOING (aligned to the right)
            $output .= '<div class="chat outgoing">
                            <div class="details">
                                <p>' . htmlspecialchars($row['msg']) . '</p>
                            </div>
                            </div>';
        } else {
            // Render as INCOMING (aligned to the left, includes profile image)
            $output .= '<div class="chat incoming">
                            <img src="php/images/' . htmlspecialchars($row['img']) . '" alt="">
                            <div class="details">
                                <p>' . htmlspecialchars($row['msg']) . '</p>
                            </div>
                            </div>';
        }
    }
} else {
    $output .= '<div class="text" style="padding-left: 25rem; font-size: 25px;">No messages are available. Start a conversation!</div>';
}

// 5. Output the HTML to the AJAX request
echo $output;
