<?php
include '../components/connect.php';

// Check login status
if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
   exit;
}

// Get current tutor data
$select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
$select_tutor->execute([$tutor_id]);
$fetch_profile = $select_tutor->fetch(PDO::FETCH_ASSOC);

// Handle AJAX request
if(isset($_POST['ajax_submit']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
   header('Content-Type: application/json');
   $response = ['status' => 'error', 'message' => '', 'errors' => []];
   
   // Validate and sanitize input data
   $data = json_decode(file_get_contents('php://input'), true);
   if(!$data) {
      $data = $_POST; // Fallback to regular POST if no JSON
   }
   
   // Schema validation
   $schema = [
      'name' => ['required' => false, 'min_length' => 3, 'max_length' => 50],
      'profession' => ['required' => false, 'options' => [
         'designer', 'musician', 'biologist', 'teacher', 'engineer', 
         'lawyer', 'accountant', 'doctor', 'journalist', 'photographer'
      ]],
      'email' => ['required' => false, 'email' => true, 'max_length' => 100],
      'old_pass' => ['required' => false],
      'new_pass' => ['required' => false, 'min_length' => 6],
      'cpass' => ['required' => false, 'match' => 'new_pass']
   ];
   
   // Validate against schema
   foreach($schema as $field => $rules) {
      if(isset($data[$field]) && !empty($data[$field])) {
         // Check min length
         if(isset($rules['min_length']) && strlen($data[$field]) < $rules['min_length']) {
            $response['errors'][$field] = "Field must be at least {$rules['min_length']} characters";
            continue;
         }
         
         // Check max length
         if(isset($rules['max_length']) && strlen($data[$field]) > $rules['max_length']) {
            $response['errors'][$field] = "Field cannot exceed {$rules['max_length']} characters";
            continue;
         }
         
         // Check email format
         if(isset($rules['email']) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $response['errors'][$field] = "Please enter a valid email address";
            continue;
         }
         
         // Check options
         if(isset($rules['options']) && !in_array($data[$field], $rules['options']) && $data[$field] != '') {
            $response['errors'][$field] = "Invalid selection";
            continue;
         }
         
         // Check password match
         if(isset($rules['match']) && $data[$field] != $data[$rules['match']]) {
            $response['errors'][$field] = "Passwords do not match";
            continue;
         }
      } elseif(isset($rules['required']) && $rules['required']) {
         $response['errors'][$field] = "This field is required";
      }
   }
   
   // If validation passed, process the update
   if(empty($response['errors'])) {
      $prev_pass = $fetch_profile['password'];
      $prev_image = $fetch_profile['image'];
      $updated = false;
      $success_messages = [];
      
      $name = isset($data['name']) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : '';
      $profession = isset($data['profession']) ? filter_var($data['profession'], FILTER_SANITIZE_STRING) : '';
      $email = isset($data['email']) ? filter_var($data['email'], FILTER_SANITIZE_STRING) : '';
      
      // Update name
      if(!empty($name)){
         $update_name = $conn->prepare("UPDATE `tutors` SET name = ? WHERE id = ?");
         $update_name->execute([$name, $tutor_id]);
         $success_messages[] = 'Username updated successfully!';
         $updated = true;
      }
      
      // Update profession
      if(!empty($profession)){
         $update_profession = $conn->prepare("UPDATE `tutors` SET profession = ? WHERE id = ?");
         $update_profession->execute([$profession, $tutor_id]);
         $success_messages[] = 'Profession updated successfully!';
         $updated = true;
      }
      
      // Update email
      if(!empty($email)){
         $select_email = $conn->prepare("SELECT email FROM `tutors` WHERE id != ? AND email = ?");
         $select_email->execute([$tutor_id, $email]);
         if($select_email->rowCount() > 0){
            $response['errors']['email'] = 'Email already taken!';
         }else{
            $update_email = $conn->prepare("UPDATE `tutors` SET email = ? WHERE id = ?");
            $update_email->execute([$email, $tutor_id]);
            $success_messages[] = 'Email updated successfully!';
            $updated = true;
         }
      }
      
      // Handle image upload
      if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
         $image = $_FILES['image']['name'];
         $image = filter_var($image, FILTER_SANITIZE_STRING);
         $ext = pathinfo($image, PATHINFO_EXTENSION);
         $rename = unique_id().'.'.$ext;
         $image_size = $_FILES['image']['size'];
         $image_tmp_name = $_FILES['image']['tmp_name'];
         $image_folder = '../uploaded_files/'.$rename;
         
         if($image_size > 2000000){
            $response['errors']['image'] = 'Image size too large!';
         }else{
            $update_image = $conn->prepare("UPDATE `tutors` SET `image` = ? WHERE id = ?");
            $update_image->execute([$rename, $tutor_id]);
            move_uploaded_file($image_tmp_name, $image_folder);
            if($prev_image != '' && $prev_image != $rename){
               unlink('../uploaded_files/'.$prev_image);
            }
            $success_messages[] = 'Image updated successfully!';
            $updated = true;
         }
      }
      
      // Update password
      if(!empty($data['old_pass'])){
         $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
         $old_pass = sha1($data['old_pass']);
         $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
         $new_pass = sha1($data['new_pass']);
         $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
         $cpass = sha1($data['cpass']);
         $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
         
         if($old_pass != $prev_pass){
            $response['errors']['old_pass'] = 'Old password not matched!';
         }elseif($new_pass != $cpass){
            $response['errors']['cpass'] = 'Confirm password not matched!';
         }else{
            if($new_pass != $empty_pass){
               $update_pass = $conn->prepare("UPDATE `tutors` SET password = ? WHERE id = ?");
               $update_pass->execute([$cpass, $tutor_id]);
               $success_messages[] = 'Password updated successfully!';
               $updated = true;
            }else{
               $response['errors']['new_pass'] = 'Please enter a new password!';
            }
         }
      }
      
      if(empty($response['errors'])) {
         $response['status'] = 'success';
         $response['message'] = 'Profile updated successfully!';
         if($updated) {
            $response['redirect'] = true;
         }
      }
   } else {
      $response['message'] = 'Please correct the errors below';
   }
   
   echo json_encode($response);
   exit;
}

// Handle regular form submission (non-AJAX fallback)
if(isset($_POST['submit']) && !isset($_POST['ajax_submit'])){
   $update_made = false;
   // Original code preserved for fallback
   $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
   $select_tutor->execute([$tutor_id]);
   $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);

   $prev_pass = $fetch_tutor['password'];
   $prev_image = $fetch_tutor['image'];

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $profession = $_POST['profession'];
   $profession = filter_var($profession, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   if(!empty($name)){
      $update_name = $conn->prepare("UPDATE `tutors` SET name = ? WHERE id = ?");
      $update_name->execute([$name, $tutor_id]);
      $message[] = 'Username updated successfully!';
      $update_made = true;
   }

   if(!empty($profession)){
      $update_profession = $conn->prepare("UPDATE `tutors` SET profession = ? WHERE id = ?");
      $update_profession->execute([$profession, $tutor_id]);
      $message[] = 'Profession updated successfully!';
      $update_made = true;
   }

   if(!empty($email)){
      $select_email = $conn->prepare("SELECT email FROM `tutors` WHERE id != ? AND email = ?");
      $select_email->execute([$tutor_id, $email]);
      if($select_email->rowCount() > 0){
         $message[] = 'Email already taken!';
      }else{
         $update_email = $conn->prepare("UPDATE `tutors` SET email = ? WHERE id = ?");
         $update_email->execute([$email, $tutor_id]);
         $message[] = 'Email updated successfully!';
         $update_made = true;
      }
   }

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   
   if(!empty($image)){
      $ext = pathinfo($image, PATHINFO_EXTENSION);
      $rename = unique_id().'.'.$ext;
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = '../uploaded_files/'.$rename;
      
      if($image_size > 2000000){
         $message[] = 'Image size too large!';
      }else{
         $update_image = $conn->prepare("UPDATE `tutors` SET `image` = ? WHERE id = ?");
         $update_image->execute([$rename, $tutor_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if($prev_image != '' AND $prev_image != $rename){
            unlink('../uploaded_files/'.$prev_image);
         }
         $message[] = 'Image updated successfully!';
         $update_made = true;
      }
   }

   $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
   $old_pass = sha1($_POST['old_pass']);
   $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
   $new_pass = sha1($_POST['new_pass']);
   $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   if($old_pass != $empty_pass){
      if($old_pass != $prev_pass){
         $message[] = 'Old password not matched!';
      }elseif($new_pass != $cpass){
         $message[] = 'Confirm password not matched!';
      }else{
         if($new_pass != $empty_pass){
            $update_pass = $conn->prepare("UPDATE `tutors` SET password = ? WHERE id = ?");
            $update_pass->execute([$cpass, $tutor_id]);
            $message[] = 'Password updated successfully!';
            $update_made = true;
         }else{
            $message[] = 'Please enter a new password!';
         }
      }
   }
   
   // Redirect to profile.php if any update was successful
   if($update_made) {
      // Set message in session to display after redirect
      session_start();
      $_SESSION['success_message'] = 'Profile updated successfully!';
      header('Location: profile.php');
      exit;
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   
   <!-- jQuery CDN link -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .error-message {
         color: #e74c3c;
         font-size: 14px;
         margin-top: 5px;
      }
      .success-message {
         color: #2ecc71;
         font-size: 14px;
         margin-top: 5px;
      }
      .form-feedback {
         margin-top: 20px;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<!-- register section starts  -->

<section class="form-container" style="min-height: calc(100vh - 19rem);position:relative;right:150px;">

   <form id="update-form" class="register" action="" method="post" enctype="multipart/form-data">
      <h3>Update profile</h3>
      <div class="flex">
         <div class="col">
            <p>Your name </p>
            <input type="text" id="name" name="name" placeholder="<?= $fetch_profile['name']; ?>" maxlength="50" class="box">
            <div id="name-error" class="error-message"></div>
            
            <p>Your profession </p>
            <select id="profession" name="profession" class="box">
               <option value="" selected><?= $fetch_profile['profession']; ?></option>
               <option value="designer">Designer</option>
               <option value="musician">Musician</option>
               <option value="biologist">Biologist</option>
               <option value="teacher">Teacher</option>
               <option value="engineer">Engineer</option>
               <option value="lawyer">Lawyer</option>
               <option value="accountant">Accountant</option>
               <option value="doctor">Doctor</option>
               <option value="journalist">Journalist</option>
               <option value="photographer">Photographer</option>
            </select>
            <div id="profession-error" class="error-message"></div>
            
            <p>Your email </p>
            <input type="email" id="email" name="email" placeholder="<?= $fetch_profile['email']; ?>" maxlength="100" class="box">
            <div id="email-error" class="error-message"></div>
         </div>
         <div class="col">
            <p>Old password :</p>
            <input type="password" id="old_pass" name="old_pass" placeholder="Enter your old password" maxlength="20" class="box">
            <div id="old_pass-error" class="error-message"></div>
            
            <p>New password :</p>
            <input type="password" id="new_pass" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box">
            <div id="new_pass-error" class="error-message"></div>
            
            <p>Confirm password :</p>
            <input type="password" id="cpass" name="cpass" placeholder="Confirm your new password" maxlength="20" class="box">
            <div id="cpass-error" class="error-message"></div>
         </div>
      </div>
      <p>Update pic :</p>
      <input type="file" id="image" name="image" accept="image/*" class="box">
      <div id="image-error" class="error-message"></div>
      
      <input type="hidden" name="ajax_submit" value="1">
      <button type="submit" id="submit-btn" name="submit" class="btn">Update now</button>
      
      <div id="form-feedback" class="form-feedback">
         <?php
         if(isset($message)){
            foreach($message as $msg){
               echo '<p class="success-message">'.$msg.'</p>';
            }
         }
         ?>
      </div>
   </form>

</section>

<script>
$(document).ready(function(){
   // Form submission with jQuery AJAX
   $("#update-form").on("submit", function(e){
      e.preventDefault();
      
      // Clear previous error messages
      $(".error-message").text("");
      $("#form-feedback").html("");
      
      // Prepare form data for AJAX
      var formData = new FormData(this);
      
      // Send AJAX request
      $.ajax({
         url: window.location.href,
         method: 'POST',
         data: formData,
         dataType: 'json',
         contentType: false,
         processData: false,
         success: function(response){
            if(response.status === 'success'){
               // Show success message in alert
               alert(response.message);
               
               // Redirect to profile page on success
               if(response.redirect){
                  window.location.href = 'profile.php';
               }
            } else {
               // Display validation errors
               if(response.errors){
                  $.each(response.errors, function(field, message){
                     $("#" + field + "-error").text(message);
                  });
               }
               
               // Display general error message
               if(response.message){
                  $("#form-feedback").html('<p class="error-message">' + response.message + '</p>');
                  $("#form-feedback")[0].scrollIntoView({ behavior: 'smooth' });
               }
            }
         },
         error: function(xhr, status, error){
            // Handle AJAX errors
            $("#form-feedback").html('<p class="error-message">An error occurred while processing your request. Please try again later.</p>');
            console.error("AJAX Error:", status, error);
         }
      });
   });
});
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>