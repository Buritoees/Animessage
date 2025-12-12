<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['unique_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

include_once "config.php";

$outgoing_id = $_SESSION['unique_id'];
$incoming_id = mysqli_real_escape_string($conn, htmlspecialchars($_POST['incoming_id']));
$response = ['header_html' => '', 'chat_messages_html' => ''];

// --- 1. Fetch Incoming User Details for the Header ---
$sql_user = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$incoming_id}");
if (mysqli_num_rows($sql_user) > 0) {
    $chat_row = mysqli_fetch_assoc($sql_user);

    // Generate the HTML for the Chat Header
    $response['header_html'] = '
        <a href="chat.php" class="back-icon"><i class="fas fa-arrow-left"></i></a> 
        <img src="php/images/' . htmlspecialchars($chat_row['img']) . '" alt="">
        <div class="details">
            <span>' . htmlspecialchars($chat_row['fname'] . " " . $chat_row['lname']) . '</span>
            <p>' . htmlspecialchars($chat_row['status']) . '</p>
        </div>
    ';
}

// --- 2. Fetch Chat Messages (Initial Load) ---
$messages_output = "";
$sql_chat = "SELECT m.*, u.img FROM messages m 
            LEFT JOIN users u ON u.unique_id = m.outgoing_msg_id
            WHERE (m.outgoing_msg_id = {$outgoing_id} AND m.incoming_msg_id = {$incoming_id})
            OR (m.outgoing_msg_id = {$incoming_id} AND m.incoming_msg_id = {$outgoing_id}) 
            ORDER BY m.msg_id";

$query_chat = mysqli_query($conn, $sql_chat);
if (mysqli_num_rows($query_chat) > 0) {
    while ($row = mysqli_fetch_assoc($query_chat)) {
        // Outgoing Bubble (Your Message) - ALIGNED RIGHT
        if ($row['outgoing_msg_id'] === $outgoing_id) {
            $messages_output .= '<div class="chat outgoing">
                                <div class="details">
                                    <p>' . htmlspecialchars($row['msg']) . '</p>
                                </div>
                                <img src="php/images/' . htmlspecialchars($row['img']) . '" alt=""> 
                                </div>'; // <--- Image added here
        }
        // Incoming Bubble (Other User's Message) - ALIGNED LEFT
        else {
            $messages_output .= '<div class="chat incoming">
                                <img src="php/images/' . htmlspecialchars($row['img']) . '" alt="">
                                <div class="details">
                                    <p>' . htmlspecialchars($row['msg']) . '</p>
                                </div>
                                </div>';
        }
    }
}

$response['chat_messages_html'] = $messages_output;

echo json_encode($response);
