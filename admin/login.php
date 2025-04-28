<?php
include '../components/connect.php';

// This section handles AJAX requests
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = ['status' => 'error', 'message' => ''];
    
    // Check email availability
    if(isset($_POST['check_email']) && !empty($_POST['check_email'])) {
        $email = filter_var($_POST['check_email'], FILTER_SANITIZE_STRING);
        
        $check_email = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
        $check_email->execute([$email]);
        
        if($check_email->rowCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Email exists';
            $response['exists'] = true;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Email not found';
            $response['exists'] = false;
        }
        
        echo json_encode($response);
        exit;
    }
    
    // Process login
    if(isset($_POST['ajax_login'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
        $pass = sha1($_POST['pass']);
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        
        $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? AND password = ? LIMIT 1");
        $select_tutor->execute([$email, $pass]);
        $row = $select_tutor->fetch(PDO::FETCH_ASSOC);
        
        if($select_tutor->rowCount() > 0) {
            setcookie('tutor_id', $row['id'], time() + 60*60*24*30, '/');
            $response['status'] = 'success';
            $response['message'] = 'Login successful';
            $response['redirect'] = 'dashboard.php';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Incorrect email or password!';
        }
        
        echo json_encode($response);
        exit;
    }
}

// Traditional form submission as fallback
if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? AND password = ? LIMIT 1");
   $select_tutor->execute([$email, $pass]);
   $row = $select_tutor->fetch(PDO::FETCH_ASSOC);
   
   if($select_tutor->rowCount() > 0){
     setcookie('tutor_id', $row['id'], time() + 60*60*24*30, '/');
     header('location:dashboard.php');
   }else{
      $message[] = 'incorrect email or password!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

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

<section class="form-container">

   <form id="loginForm" action="" method="post" enctype="multipart/form-data" class="login">
      <h3>welcome back!</h3>
      <p>Email <span>*</span></p>
      <input type="email" name="email" id="email" placeholder="Enter email" maxlength="20" required class="box">
      <small id="emailFeedback"></small>
      
      <p>Password <span>*</span></p>
      <input type="password" name="pass" id="pass" placeholder="Enter password" maxlength="20" required class="box">
      
      <p class="link">Don't have an account? <a href="register.php">Register new</a></p>
      <input type="submit" name="submit" id="submit" value="login now" class="btn">
   </form>

</section>

<script>
$(document).ready(function() {
    // Check email dynamically
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
                    if(response.exists) {
                        $('#emailFeedback').html('<span style="color:green">Email found</span>');
                    } else {
                        $('#emailFeedback').html('<span style="color:red">Email not found</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error checking email:", error);
                }
            });
        }
    });

    
    // AJAX login submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        let email = $('#email').val();
        let pass = $('#pass').val();
        
        // Basic validation
        if(!email || !pass) {
            showMessage('Please fill all required fields', 'error');
            return;
        }
        
        // Submit form data via AJAX
        $.ajax({
            url: window.location.href,
            method: 'POST',
            dataType: 'json',
            data: {
                ajax_login: true,
                email: email,
                pass: pass
            },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if(response.status === 'success') {
                    showMessage(response.message, 'success');
                    // Redirect to dashboard after successful login
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    showMessage(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Login error:", error);
                showMessage('Login failed. Please try again.', 'error');
            }
        });
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