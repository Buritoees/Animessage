<?php 
  // Common setup and session start
  session_start();
  include_once "php/config.php";
  
  // Check authentication
  if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
    exit(); // Always use exit() after a header redirect
  }

  // Fetch current user details
  $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
  
  if(mysqli_num_rows($sql) > 0){
    $row = mysqli_fetch_assoc($sql);
  } else {
    // CRITICAL FIX: User ID in session does not exist in the database.
    // Destroy the invalid session and force a full logout.
    session_unset();
    session_destroy();
    header("location: login.php");
    exit(); // Ensure script stops execution
  }
  
  // --- NEW LOGIC: Truncate Header Name to 20 Chars ---
  // This code is now safe because $row is guaranteed to be set if we reached this point.
  $full_name = $row['fname']. " " . $row['lname'];
  $display_name = (strlen($full_name) > 20) ? substr($full_name, 0, 20) . '...' : $full_name;
?>

<?php include_once "header.php"; ?>
<link rel="stylesheet" href="style1.css"> 
<body>
  
  <div class="main-portal-wrapper">

    <section class="user-list-panel">
      
      <header>
        <div class="content">
          <img src="php/images/<?php echo $row['img']; ?>" alt="">
          <div class="details">
            <span><?php echo $display_name; ?></span>
            <p><?php echo $row['status']; ?></p>
          </div>
        </div>
        <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>
      </header>
      
      <div class="search">
        <span class="text">Select a contact to begin a conversation</span>
        <input type="text" placeholder="Search contacts...">
        <button><i class="fas fa-search"></i></button>
      </div>
      
      <div class="users-list">
        </div>
    </section>

    <section class="chat-content-area no-chat-selected" id="chat-content-area">
      
      <div id="dynamic-chat-container"></div>
      
      <div class="chat-placeholder" id="chat-placeholder">
        <i class="far fa-comment-dots"></i>
        <h1>Connect and Collaborate</h1>
        <p>Select a contact from the left sidebar to start chatting.</p>
      </div>

    </section>

  </div> 
  <script src="javascript/users.js"></script>
  <script src="javascript/chat.js"></script> 
</body>
</html>