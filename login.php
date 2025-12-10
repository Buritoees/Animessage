<?php
session_start();

// 🔑 Redirection Logic Check
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
  header("location: admin.php");
  exit();
}

// NOTE: unique_id should be an integer, 0 is the placeholder for 'admin'
if (isset($_SESSION['unique_id']) && $_SESSION['unique_id'] !== 0) {
  header("location: chatPage.php"); 
  exit();
}
// ... rest of the file
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Animessage Login</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
</head>

<body class="auth-page-body signup-design">
  <div class="wrapper">
    <img src="image/shinobu.png" alt="shinobu" class="shinobu">
    <div class="content-area">
      <section class="form login">

        <div class="signup-header">
          <h1>Animessage</h1>
          <h2>Welcome back<span class="accent-dot">.</span></h2>
        </div>

        <form action="php/login.php" method="POST" enctype="multipart/form-data" autocomplete="off">
          <div class="error-text"></div>

          <div class="field input input-no-label">
            <input type="text" name="email" placeholder="Email Address" required>
          </div>

          <div class="field input input-no-label password-field">
            <input type="password" name="password" placeholder="Password" required>
            <i class="fas fa-eye"></i>
          </div>

          <div class="auth-buttons-group">
            <div class="field button primary-btn full-width">
              <input type="submit" name="submit" value="Log In">
            </div>
          </div>

          <div class="link member-link">Not yet signed up? <a href="index.php">Create new account</a></div>

        </form>

      </section>
    </div>
  </div>

  <div class="modal-overlay" id="dynamicModalOverlay">
      <div class="registration-modal" id="dynamicModal">
          <button class="modal-close-btn" onclick="document.getElementById('dynamicModalOverlay').classList.remove('active');">
              <i class="fas fa-times"></i>
          </button>
          <i class="modal-icon fas" id="modalIcon"></i>
          <h2 id="modalTitle"></h2>
          <p id="modalMessage"></p>
          <a href="login.php" class="modal-action-link" id="modalActionLink">Go to Login</a>
      </div>
  </div>
  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/login.js"></script>

</body>

</html>