<?php
    session_start();
    include_once "config.php";
    
    $outgoing_id = $_SESSION['unique_id'];
    $searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm']);
    $output = "";
    
    // *** MODIFIED SQL QUERY FOR SORTING ***
    // 1. Filter by search term in both first name and last name
    // 2. ORDER BY status = 'Active now' DESC, fname ASC, lname ASC
    $sql = "SELECT * FROM users WHERE NOT unique_id = {$outgoing_id} AND (fname LIKE '%{$searchTerm}%' OR lname LIKE '%{$searchTerm}%') 
            ORDER BY status = 'Active now' DESC, fname ASC, lname ASC";
    
    $query = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($query) > 0){
        // Fetch all users and then iterate to build the list
        while($row = mysqli_fetch_assoc($query)){
            
            // Check for last message logic (if you have it)
            // ... (Your last message logic remains here) ...
            
            // Determine the status icon class
            $offline = ($row['status'] == "Offline now") ? "offline" : "";
            
            // Determine the message text
            // $you = ($outgoing_id == $msg_row['outgoing_msg_id']) ? "You: " : "";
            // $msg = (strlen($result) > 28) ? substr($result, 0, 28) . '...' : $result;

            $output .= '<a href="#" class="user-link" data-user-id="'.$row['unique_id'].'">
                        <div class="content">
                        <img src="php/images/'.$row['img'].'" alt="">
                        <div class="details">
                            <span>'.$row['fname'] . " " . $row['lname'].'</span>
                            <p>' . $row['status'] . '</p>
                        </div>
                        </div>
                        <div class="status-dot '.$offline.'"><i class="fas fa-circle"></i></div>
                    </a>';
        }
    }else{
        $output .= 'No user found related to your search term.';
    }
    
    echo $output;
?>