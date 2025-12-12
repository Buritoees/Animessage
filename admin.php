<?php
// CRITICAL FIX: session_start() MUST be at the very top before any HTML or output
session_start();

// --- ADMIN ACCESS CHECK ---
// If the admin flag is missing or false, redirect to login.php
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("location: login.php");
    exit();
}
// --------------------------


// 1. Database Connection 
$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "chatappv1";

$conn = mysqli_connect($hostname, $username, $password, $dbname);
if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}

// 2. POST Handling (DELETE Logic)
if (isset($_POST['submit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);

    $sql_delete = "DELETE FROM `users` WHERE user_id=$id";
    if (mysqli_query($conn, $sql_delete)) {
        header("location: admin.php");
        exit();
    } else {
        echo "<script>alert('Error deleting user: " . mysqli_error($conn) . "');</script>";
    }
}

// 3. Data Fetching Logic
$sql = "SELECT `user_id`, `unique_id`, `fname`, `lname`, `email` FROM `users`";
$result = mysqli_query($conn, $sql);

// 4. Logout Logic
if (isset($_GET['logout_id'])) {
    // Clear admin and unique_id sessions
    unset($_SESSION['is_admin']);
    unset($_SESSION['unique_id']);
    session_destroy();

    header("location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
</head>

<body class="admin-page-body">
    <div class="wrapper">
        <section class="admin-panel">
            <header>
                <div class="header-text">Welcome to Admin Panel</div>
                <a href="admin.php?logout_id=ADMIN" class="logout-btn">Logout</a>
            </header>

            <div class="admin-table-container">
                <table class="adminTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['fname']; ?></td>
                                <td><?php echo $row['lname']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <form method="post" action="admin.php" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                        <input type="hidden" name="id" value="<?php echo $row['user_id']; ?>">
                                        <input type="submit" name="submit" value="Remove" class="remove-btn">
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>

</html>
<?php
mysqli_close($conn);
?>