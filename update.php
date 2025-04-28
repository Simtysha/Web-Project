<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
   header('location:login.php');
}

// Initialize response array for JSON output
$response = [
    'status' => 'error',
    'message' => [],
    'data' => []
];

if(isset($_POST['submit'])){

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
   $select_user->execute([$user_id]);
   $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

   $prev_pass = $fetch_user['password'];
   $prev_image = $fetch_user['image'];
   $is_valid = true;

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   if(!empty($name)){
      $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
      $update_name->execute([$name, $user_id]);
      $response['message'][] = 'Username updated successfully!';
   }

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   if(!empty($email)){
      // Validate email format
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
         $response['message'][] = 'Invalid email format!';
         $is_valid = false;
      } else {
         $select_email = $conn->prepare("SELECT email FROM `users` WHERE email = ? AND id != ?");
         $select_email->execute([$email, $user_id]);
         if($select_email->rowCount() > 0){
            $response['message'][] = 'Email already taken!';
            $is_valid = false;
         }else{
            $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?");
            $update_email->execute([$email, $user_id]);
            $response['message'][] = 'Email updated successfully!';
         }
      }
   }

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   
   if(!empty($image)){
      $ext = pathinfo($image, PATHINFO_EXTENSION);
      $rename = unique_id().'.'.$ext;
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_files/'.$rename;
      
      // Validate image file type
      $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
      if(!in_array(strtolower($ext), $allowed_extensions)){
         $response['message'][] = 'Invalid image format! Allowed formats: JPG, JPEG, PNG, GIF';
         $is_valid = false;
      } else if($image_size > 2000000){
         $response['message'][] = 'Image size too large!';
         $is_valid = false;
      }else{
         $update_image = $conn->prepare("UPDATE `users` SET `image` = ? WHERE id = ?");
         $update_image->execute([$rename, $user_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if($prev_image != '' AND $prev_image != $rename){
            unlink('uploaded_files/'.$prev_image);
         }
         $response['message'][] = 'Image updated successfully!';
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = $_POST['new_pass'];
   $cpass = $_POST['cpass'];

   if(!empty($_POST['old_pass'])){
      if($old_pass != $prev_pass){
         $response['message'][] = 'Old password not matched!';
         $is_valid = false;
      } elseif(empty($new_pass)){
         $response['message'][] = 'Please enter a new password!';
         $is_valid = false;
      } elseif(strlen($new_pass) < 5){
         $response['message'][] = 'New password must be at least 5 characters!';
         $is_valid = false;
      } elseif($new_pass != $cpass){
         $response['message'][] = 'Confirm password not matched!';
         $is_valid = false;
      } else {
         $hashed_new_pass = sha1($new_pass);
         $hashed_new_pass = filter_var($hashed_new_pass, FILTER_SANITIZE_STRING);
         $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_pass->execute([$hashed_new_pass, $user_id]);
         $response['message'][] = 'Password updated successfully!';
      }
   }

   // Update response status if all validations pass
   if($is_valid){
      $response['status'] = 'success';
   }
   
   // Set message variable for PHP display
   $message = $response['message'];
}

// Fetch updated profile data
$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// AJAX request handling
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
   header('Content-Type: application/json');
   echo json_encode($response);
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container" style="min-height: calc(100vh - 19rem);">

   <form action="" method="post" enctype="multipart/form-data" id="update-form">
      <h3>Update profile</h3>
      
      <?php
         if(isset($message)){
            foreach($message as $msg){
               echo '<div class="message">
                  <span>'.$msg.'</span>
                  <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
               </div>';
            }
         }
      ?>
      
      <div class="flex">
         <div class="col">
            <p>Name</p>
            <input type="text" name="name" placeholder="<?= $fetch_profile['name']; ?>" maxlength="100" class="box">
            <p>Email</p>
            <input type="email" name="email" id="email" placeholder="<?= $fetch_profile['email']; ?>" maxlength="100" class="box">
            <span id="email-error" class="error"></span>
            <p>Update picture</p>
            <input type="file" name="image" accept="image/*" class="box">
         </div>
         <div class="col">
               <p>Old password</p>
               <input type="password" name="old_pass" id="old_pass" placeholder="Enter old password" maxlength="50" class="box">
               <p>New password</p>
               <input type="password" name="new_pass" id="new_pass" placeholder="Enter new password" maxlength="50" class="box">
               <span id="password-error" class="error"></span>
               <p>Confirm password</p>
               <input type="password" name="cpass" id="cpass" placeholder="Confirm password" maxlength="50" class="box">
               <span id="cpass-error" class="error"></span>
         </div>
      </div>
      <input type="submit" name="submit" value="update profile" class="btn">
   </form>

</section>

<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const form = document.getElementById('update-form');
   const emailInput = document.getElementById('email');
   const emailError = document.getElementById('email-error');
   const oldPassInput = document.getElementById('old_pass');
   const newPassInput = document.getElementById('new_pass');
   const cpassInput = document.getElementById('cpass');
   const passwordError = document.getElementById('password-error');
   const cpassError = document.getElementById('cpass-error');
   
   // Email validation
   emailInput.addEventListener('blur', function() {
      if(emailInput.value) {
         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
         if(!emailRegex.test(emailInput.value)) {
            emailError.textContent = 'Please enter a valid email address';
         } else {
            emailError.textContent = '';
         }
      }
   });
   
   // Password validation
   newPassInput.addEventListener('blur', function() {
      if(newPassInput.value && newPassInput.value.length < 5) {
         passwordError.textContent = 'Password must be at least 5 characters';
      } else {
         passwordError.textContent = '';
      }
   });
   
   // Confirm password validation
   cpassInput.addEventListener('blur', function() {
      if(cpassInput.value && cpassInput.value !== newPassInput.value) {
         cpassError.textContent = 'Passwords do not match';
      } else {
         cpassError.textContent = '';
      }
   });
   
   // Form submission validation
   form.addEventListener('submit', function(e) {
      let hasError = false;
      
      // Validate email if provided
      if(emailInput.value) {
         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
         if(!emailRegex.test(emailInput.value)) {
            emailError.textContent = 'Please enter a valid email address';
            hasError = true;
         }
      }
      
      // Validate password if old password provided
      if(oldPassInput.value) {
         if(newPassInput.value.length < 5) {
            passwordError.textContent = 'New password must be at least 5 characters';
            hasError = true;
         }
         
         if(newPassInput.value !== cpassInput.value) {
            cpassError.textContent = 'Passwords do not match';
            hasError = true;
         }
      }
      
      if(hasError) {
         e.preventDefault();
      }
   });
});
</script>
   
</body>
</html>
