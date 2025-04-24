<?php
session_start();
include 'components/connect.php';

// Initialize $user_id to prevent undefined variable error in user_header.php
$user_id = '';

// Check if already logged in as user
if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
   header('location:home.php');
   exit();
}

// Check if already logged in as tutor/admin
if(isset($_COOKIE['tutor_id'])){
   header('location:admin/dashboard.php');
   exit();
}

$message = [];

if(isset($_POST['submit'])){
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // First check if it's a tutor/admin
   $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? AND password = ? LIMIT 1");
   $select_tutor->execute([$email, $pass]);
   $row_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
   
   if($select_tutor->rowCount() > 0){
      // It's a tutor/admin
      setcookie('tutor_id', $row_tutor['id'], time() + 60*60*24*30, '/');
      header('location:admin/dashboard.php');
      exit();
   } else {
      // Check if it's a regular user
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
      $select_user->execute([$email, $pass]);
      $row_user = $select_user->fetch(PDO::FETCH_ASSOC);
      
      if($select_user->rowCount() > 0){
         // It's a regular user
         setcookie('user_id', $row_user['id'], time() + 60*60*24*30, '/');
         header('location:home.php');
         exit();
      } else {
         $message[] = 'incorrect email or password!';
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
   <title>Login - Virtu-Learn</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- display messages if any -->
<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">
   <form action="" method="post" enctype="multipart/form-data" class="login">
      <h3>Welcome Back!</h3>
      <p>Email <span>*</span></p>
      <input type="email" name="email" placeholder="Enter email" maxlength="50" required class="box">
      <p>Password <span>*</span></p>
      <input type="password" name="pass" placeholder="Enter password" maxlength="20" required class="box">
      <p class="link">Don't have an account? <a href="register.php">Register now</a></p>
      <input type="submit" name="submit" value="login now" class="btn">
   </form>
</section>

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>