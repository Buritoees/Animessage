<?php 
  session_start();
  include_once "config.php"; 
  
  // Security check
  if(!isset($_SESSION['unique_id']) || !isset($_GET['user_id'])){
    http_response_code(403);
    exit();
  }
  
  $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
  
  // Fetch the chat partner's details
  $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$user_id}");
  
  if(mysqli_num_rows($sql) > 0){
    $row = mysqli_fetch_assoc($sql);
  } else {
    http_response_code(404);
    echo "<p>User not found.</p>";
    exit();
  }
?>

<section class="chat-area">
  <header>
    <a href="#" class="back-icon" 
       onclick="document.getElementById('chat-placeholder').style.display='flex'; 
                document.getElementById('dynamic-chat-container').innerHTML=''; 
                if (window.chatInterval) { clearInterval(window.chatInterval); } /* Use window.chatInterval for safety */
                document.getElementById('chat-content-area').classList.add('no-chat-selected'); 
                document.getElementById('chat-content-area').classList.remove('chat-active'); 
                document.querySelector('.main-portal-wrapper').classList.remove('chat-full-screen-active'); 
                document.querySelectorAll('.user-link').forEach(link => link.classList.remove('active'));
                return false;">
      <i class="fas fa-arrow-left"></i>
    </a>
    <img src="php/images/<?php echo $row['img']; ?>" alt="">
    <div class="details">
      <span><?php echo $row['fname']. " " . $row['lname'] ?></span>
      <p><?php echo $row['status']; ?></p>
    </div>
  </header>
  
  <div class="chat-box">
    </div>
  
  <form action="#" class="typing-area">
    <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $user_id; ?>" hidden> 
    <input type="text" name="message" class="input-field" placeholder="Type a message here..." autocomplete="off">
    <button><i class="fab fa-telegram-plane"></i></button>
  </form>
</section>