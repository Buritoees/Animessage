<?php
    session_start();
    include_once "config.php";
    
    // Get the unique_id of the currently logged-in user
    $outgoing_id = $_SESSION['unique_id'];
    
    // *** SQL QUERY FOR SORTING ***
    // Orders by 'Active now' first, then by name alphabetically.
    $sql = "SELECT * FROM users 
            WHERE NOT unique_id = {$outgoing_id} 
            ORDER BY status = 'Active now' DESC, fname ASC, lname ASC";

    $query = mysqli_query($conn, $sql);
    $output = "";

    if(mysqli_num_rows($query) == 0){
        $output .= "No users are available to chat";
    }elseif(mysqli_num_rows($query) > 0){
        
        while($row = mysqli_fetch_assoc($query)){
            
            // 🔑 Last Message Logic - Fetch the last message for the conversation
            $sql2 = "SELECT * FROM messages 
                     WHERE (incoming_msg_id = {$row['unique_id']} OR outgoing_msg_id = {$row['unique_id']})
                     AND (outgoing_msg_id = {$outgoing_id} OR incoming_msg_id = {$outgoing_id})
                     ORDER BY msg_id DESC LIMIT 1";
            $query2 = mysqli_query($conn, $sql2);
            $msg_row = mysqli_fetch_assoc($query2);
            
            // Determine the message text
            if(mysqli_num_rows($query2) > 0){
                $result = $msg_row['msg'];
            }else{
                $result = "No message available";
            }
            
            // Determine the message prefix
            $you = (isset($msg_row['outgoing_msg_id']) && $outgoing_id == $msg_row['outgoing_msg_id']) ? "You: " : "";
            
            // --- NEW LOGIC: Truncate Contact Name to 13 Chars ---
            $full_name = $row['fname'] . " " . $row['lname'];
            $display_name = (strlen($full_name) > 13) ? substr($full_name, 0, 13) . '...' : $full_name;

            // --- NEW LOGIC: Truncate Recent Message to 25 Chars ---
            $full_message = $you . $result;
            $display_msg = (strlen($full_message) > 25) ? substr($full_message, 0, 25) . '...' : $full_message;
            
            // 🔑 Determine the status icon class ('offline' if status is 'Offline now')
            $offline = ($row['status'] == "Offline now") ? "offline" : "";
            
            // Determine the 'unread' class for the text (if needed, relies on a 'read_status' column)
            $unread_class = ""; 

            // Insert the truncated name and message
            $output .= '<a href="#" class="user-link" data-user-id="'.$row['unique_id'].'">
                        <div class="content">
                        <img src="php/images/'.$row['img'].'" alt="">
                        <div class="details">
                            <span>'.$display_name.'</span>
                            <p class="'.$unread_class.'">' . $display_msg . '</p>
                        </div>
                        </div>
                        <div class="status-dot '.$offline.'">
                            <i class="fas fa-circle"></i>
                        </div>
                    </a>';
        }
    }
    
    echo $output;
?>