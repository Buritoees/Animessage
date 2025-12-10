<?php
session_start();
include_once "config.php";

// Initialize a response array with a default error status
$response = ['status' => 'error', 'message' => 'Something went wrong. Please try again!'];

$fname = mysqli_real_escape_string($conn, $_POST['fname']);
$lname = mysqli_real_escape_string($conn, $_POST['lname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
// NOTE: Password should be hashed before insertion! 
$password = mysqli_real_escape_string($conn, $_POST['password']); 

if (!empty($fname) && !empty($lname) && !empty($email) && !empty($password)) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '{$email}'");
        if (mysqli_num_rows($sql) > 0) {
            // ❌ Email already exists error
            $response['message'] = "$email - This email already exists!";
        } else {
            if (isset($_FILES['image'])) {
                $img_name = $_FILES['image']['name'];
                $img_type = $_FILES['image']['type'];
                $tmp_name = $_FILES['image']['tmp_name'];

                $img_explode = explode('.', $img_name);
                $img_ext = end($img_explode);

                $extensions = ["jpeg", "png", "jpg"];
                if (in_array($img_ext, $extensions) === true) {
                    $types = ["image/jpeg", "image/jpg", "image/png"];
                    if (in_array($img_type, $types) === true) {
                        $time = time();
                        $new_img_name = $time . $img_name;
                        
                        // Hashing the password for security
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        if (move_uploaded_file($tmp_name, "images/" . $new_img_name)) {
                            $status = "Active now";
                            $random_id = rand(time(), 100000000);
                            
                            $insert_query = mysqli_query($conn, "INSERT INTO users (unique_id, fname, lname, email, password, img, status)
                                VALUES ({$random_id}, '{$fname}', '{$lname}', '{$email}', '{$hashed_password}', '{$new_img_name}', '{$status}')");
                            
                            if ($insert_query) {
                                // REMOVED: $_SESSION['unique_id'] = $random_id; 
                                // User must now explicitly log in after registration.

                                // ✅ SUCCESS POINT
                                $response['status'] = 'success';
                                $response['message'] = 'Registration successful! Please log in.'; // Updated message
                            } else {
                                $response['message'] = "Database insertion failed.";
                            }
                        } else {
                            $response['message'] = "File upload failed: Something went wrong moving the image.";
                        }
                    } else {
                        $response['message'] = "Please upload an image file - jpeg, png, jpg.";
                    }
                } else {
                    $response['message'] = "Please upload an image file - jpeg, png, jpg.";
                }
            } else {
                $response['message'] = "Please upload a profile image.";
            }
        }
    } else {
        $response['message'] = "$email is not a valid email address!";
    }
} else {
    $response['message'] = "All input fields are required!";
}


// Output the final JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>