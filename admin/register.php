<?php

include '../components/connect.php';

// Handle AJAX requests
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = ['status' => 'error', 'message' => ''];
    
    // Check email availability
    if(isset($_POST['check_email']) && !empty($_POST['check_email'])) {
        $email = filter_var($_POST['check_email'], FILTER_SANITIZE_STRING);
        
        $check_email = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
        $check_email->execute([$email]);
        
        if($check_email->rowCount() > 0) {
            $response['status'] = 'error';
            $response['message'] = 'Email already taken!';
            $response['available'] = false;
        } else {
            $response['status'] = 'success';
            $response['message'] = 'Email available';
            $response['available'] = true;
        }
        
        echo json_encode($response);
        exit;
    }
    
    
    // Process AJAX registration
    if(isset($_POST['ajax_register'])) {
        // Validate inputs
        if(empty($_POST['name']) || empty($_POST['profession']) || empty($_POST['email']) || 
           empty($_POST['pass']) || empty($_POST['cpass'])) {
            $response['message'] = 'All fields are required!';
            echo json_encode($response);
            exit;
        }
        
        // Get and sanitize form data
        $id = unique_id();
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $profession = filter_var($_POST['profession'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $pass = sha1($_POST['pass']);
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        $cpass = sha1($_POST['cpass']);
        $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);
        
        // Check if email already exists
        $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
        $select_tutor->execute([$email]);
        
        if($select_tutor->rowCount() > 0) {
            $response['message'] = 'Email already taken!';
            echo json_encode($response);
            exit;
        }
        
        // Check if passwords match
        if($pass != $cpass) {
            $response['message'] = 'Confirm password not matched!';
            echo json_encode($response);
            exit;
        }
        
        // Handle file upload via AJAX (base64)
        if(!isset($_POST['image_data']) || empty($_POST['image_data'])) {
            $response['message'] = 'Profile picture is required!';
            echo json_encode($response);
            exit;
        }
        
        // Process base64 image
        $image_data = $_POST['image_data'];
        $image_array = explode(';', $image_data);
        $image_type = explode('/', $image_array[0])[1];
        $image_base64 = explode(',', $image_array[1])[1];
        $image_decoded = base64_decode($image_base64);
        
        // Create unique filename
        $rename = unique_id() . '.' . $image_type;
        $image_folder = '../uploaded_files/' . $rename;
        
        // Save image file
        if(file_put_contents($image_folder, $image_decoded)) {
            // Insert new tutor record
            try {
                $insert_tutor = $conn->prepare("INSERT INTO `tutors`(id, name, profession, email, password, image) VALUES(?,?,?,?,?,?)");
                $insert_tutor->execute([$id, $name, $profession, $email, $cpass, $rename]);
                
                // Set cookie for auto-login
                setcookie('tutor_id', $id, time() + 60*60*24*30, '/');
                
                $response['status'] = 'success';
                $response['message'] = 'Registration successful!';
                $response['redirect'] = 'dashboard.php';
            } catch(PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        } else {
            $response['message'] = 'Failed to save profile image!';
        }
        
        echo json_encode($response);
        exit;
    }
}

// Traditional form submission (fallback)
if(isset($_POST['submit'])){

   $id = unique_id();
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $profession = $_POST['profession'];
   $profession = filter_var($profession, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = unique_id().'.'.$ext;
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_files/'.$rename;

   $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
   $select_tutor->execute([$email]);
   
   if($select_tutor->rowCount() > 0){
      $message[] = 'email already taken!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         $insert_tutor = $conn->prepare("INSERT INTO `tutors`(id, name, profession, email, password, image) VALUES(?,?,?,?,?,?)");
         $insert_tutor->execute([$id, $name, $profession, $email, $cpass, $rename]);
         move_uploaded_file($image_tmp_name, $image_folder);
         
         // Set cookie for the newly registered admin
         setcookie('tutor_id', $id, time() + 60*60*24*30, '/');
         
         // Redirect to dashboard directly after successful registration
         header('Location: dashboard.php');
         exit();
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- jQuery CDN link -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body style="padding-left: 0;">

<div id="message-container">
<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message form">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
</div>

<!-- register section starts  -->

<section class="form-container" style="margin-top:25px;">

<form id="registerForm" class="register" action="" method="post" enctype="multipart/form-data">
      <h3>Create Tutor Account</h3>
      <input type="hidden" name="register_type" value="tutor">
      <div class="flex">
         <div class="col">
            <p>Name <span>*</span></p>
            <input type="text" name="name" id="name" placeholder="Enter name" maxlength="50" required class="box">
            <p>Profession <span>*</span></p>
            <select name="profession" id="profession" class="box" required>
               <option value="" disabled selected>Select profession</option>
               <option value="developer">Developer</option>
               <option value="desginer">Designer</option>
               <option value="engineer">Engineer</option>
               <option value="lawyer">Lawyer</option>
               <option value="accountant">Accountant</option>
               <option value="doctor">Doctor</option>
               <option value="journalist">Journalist</option>
               <option value="photographer">Photographer</option>
               <option value="IT Specialist">IT Specialist</option>
            </select>
            <p>Email <span>*</span></p>
            <input type="email" name="email" id="email" placeholder="Enter email" maxlength="20" required class="box">
            <small id="emailFeedback"></small>
         </div>
         <div class="col">
            <p>Password <span>*</span></p>
            <input type="password" name="pass" id="pass" placeholder="Enter password" maxlength="20" required class="box">
            <p>Confirm Password <span>*</span></p>
            <input type="password" name="cpass" id="cpass" placeholder="Confirm password" maxlength="20" required class="box">
            <small id="passwordFeedback"></small>
            <p>Select Picture <span>*</span></p>
            <input type="file" name="image" id="image" accept="image/*" required class="box">
            <div id="imagePreview" style="margin-top: 10px; display: none;">
                <img id="previewImg" src="" alt="Preview" style="max-width: 100px; max-height: 100px;">
            </div>
         </div>
      </div>
      <p class="link">Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" name="submit" id="submit" value="Register now" class="btn">
   </form>

</section>

<!-- register section ends -->

<script>
$(document).ready(function() {
    // Check email availability dynamically
    $('#email').on('blur', function() {
        let email = $(this).val();
        if(email.length > 0) {
            $.ajax({
                url: window.location.href,
                method: 'POST',
                dataType: 'json',
                data: { check_email: email },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if(response.available) {
                        $('#emailFeedback').html('<span style="color:green">Email available</span>');
                    } else {
                        $('#emailFeedback').html('<span style="color:red">Email already taken!</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error checking email:", error);
                }
            });
        }
    });

    // Check password match
    $('#cpass').on('keyup', function() {
        if($('#pass').val() != $('#cpass').val()) {
            $('#passwordFeedback').html('<span style="color:red">Passwords do not match!</span>');
        } else {
            $('#passwordFeedback').html('<span style="color:green">Passwords match</span>');
        }
    });
    
    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if(file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        }
    });

    // Handle form submission via AJAX
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let name = $('#name').val();
        let profession = $('#profession').val();
        let email = $('#email').val();
        let pass = $('#pass').val();
        let cpass = $('#cpass').val();
        let imageFile = $('#image')[0].files[0];
        
        if(!name || !profession || !email || !pass || !cpass || !imageFile) {
            showMessage('Please fill all required fields', 'error');
            return;
        }
        
        if(pass !== cpass) {
            showMessage('Confirm password not matched!', 'error');
            return;
        }
        
        // Read image file as base64
        let reader = new FileReader();
        reader.onload = function(e) {
            let imageData = e.target.result;
            
            // Submit form data via AJAX
            $.ajax({
                url: window.location.href,
                method: 'POST',
                dataType: 'json',
                data: {
                    ajax_register: true,
                    name: name,
                    profession: profession,
                    email: email,
                    pass: pass,
                    cpass: cpass,
                    image_data: imageData
                },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                beforeSend: function() {
                    $('#submit').val('Processing...');
                    $('#submit').attr('disabled', true);
                },
                success: function(response) {
                    if(response.status === 'success') {
                        showMessage(response.message, 'success');
                        // Redirect to dashboard after successful registration
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else {
                        showMessage(response.message, 'error');
                        $('#submit').val('Register now');
                        $('#submit').attr('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Registration error:", error);
                    showMessage('Registration failed. Please try again.', 'error');
                    $('#submit').val('Register now');
                    $('#submit').attr('disabled', false);
                }
            });
        };
        reader.readAsDataURL(imageFile);
    });
    
    // Function to display messages
    function showMessage(message, type) {
        let className = 'message form';
        if(type === 'error') {
            className += ' error';
        } else if(type === 'success') {
            className += ' success';
        }
        
        let messageHtml = `
        <div class="${className}">
            <span>${message}</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        `;
        
        $('#message-container').html(messageHtml);
        
        // Auto-remove message after 5 seconds
        setTimeout(function() {
            $('.message').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
});

</script>
   
</body>
</html>