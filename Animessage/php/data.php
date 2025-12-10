<?php
    while($row = mysqli_fetch_assoc($query)){
        $sql2 = "SELECT * FROM messages WHERE (incoming_msg_id = {$row['unique_id']}
                OR outgoing_msg_id = {$row['unique_id']}) AND (outgoing_msg_id = {$outgoing_id} 
                OR incoming_msg_id = {$outgoing_id}) ORDER BY msg_id DESC LIMIT 1";
        $query2 = mysqli_query($conn, $sql2);
        $row2 = mysqli_fetch_assoc($query2);
        
        (mysqli_num_rows($query2) > 0) ? $result = $row2['msg'] : $result ="No message available";
        
        if(isset($row2['outgoing_msg_id'])){
            ($outgoing_id == $row2['outgoing_msg_id']) ? $you = "You: " : $you = "";
        }else{
            $you = "";
        }
        
        // --- NEW LOGIC: Truncate Contact Name to 13 Chars ---
        $full_name = $row['fname']. " " . $row['lname'];
        $display_name = (strlen($full_name) > 13) ? substr($full_name, 0, 13) . '...' : $full_name;

        // --- NEW LOGIC: Truncate Recent Message to 25 Chars ---
        $full_message = $you . $result;
        $display_msg = (strlen($full_message) > 25) ? substr($full_message, 0, 25) . '...' : $full_message;
        
        ($row['status'] == "Offline now") ? $offline = "offline" : $offline = "";
        ($outgoing_id == $row['unique_id']) ? $hid_me = "hide" : $hid_me = "";

        $output .= '<a href="chat.php?user_id='. $row['unique_id'] .'">
                    <div class="content">
                    <img src="php/images/'. $row['img'] .'" alt="">
                    <div class="details">
                        <span>'. $display_name .'</span>
                        <p>'. $display_msg .'</p>
                    </div>
                    </div>
                    <div class="status-dot '. $offline .'"><i class="fas fa-circle"></i></div>
                </a>';
    }
?>