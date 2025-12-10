<?php 
// This file serves the dynamic HTML content for the chat area

  session_start();
  include_once "config.php";
  
  // 1. Validate session and incoming user ID
  if(!isset($_SESSION['unique_id']) || !isset($_GET['user_id'])){
    http_response_code(403);
    exit();
  }

  $chat_user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
  
  // 2. Fetch the details of the user being chatted with
  $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$chat_user_id}");
  $chat_row = null;
  if(mysqli_num_rows($sql) > 0){
    $chat_row = mysqli_fetch_assoc($sql);
  } else {
    exit();
  }
?>

<section class="chat-area"> 

  <header>
    <a href="#" class="back-icon" 
       onclick="document.getElementById('chat-placeholder').style.display='flex'; 
                document.getElementById('dynamic-chat-container').innerHTML=''; 
                if (window.chatInterval) { clearInterval(window.chatInterval); }
                document.getElementById('chat-content-area').classList.remove('chat-active'); 
                document.getElementById('chat-content-area').classList.add('no-chat-selected'); 
                document.querySelector('.main-portal-wrapper').classList.remove('chat-full-screen-active'); 
                document.querySelectorAll('.user-link').forEach(link => link.classList.remove('active'));
                return false;">
      <i class="fas fa-arrow-left"></i>
    </a> 
    <img src="php/images/<?php echo $chat_row['img']; ?>" alt="">
    <div class="details">
      <span><?php echo $chat_row['fname']. " " . $chat_row['lname'] ?></span>
      <p><?php echo $chat_row['status']; ?></p>
    </div>
  </header>

  <div class="chat-box">
      </div>
  <form action="#" class="typing-area">
    <input type="text" class="incoming_id" name="incoming_id" value="<?php echo $chat_user_id; ?>" hidden>
    <input type="text" name="message" class="input-field" placeholder="Type a message..." autocomplete="off">
    <button><i class="fab fa-telegram-plane"></i></button>
  </form>

</section>