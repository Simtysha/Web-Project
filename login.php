<?php
session_start();
include 'components/connect.php';


$user_id = '';


$message = [];


if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
   header('location:home.php');
   exit();
}



if(isset($_COOKIE['tutor_id'])){
   header('location:admin/dashboard.php');
   exit();
}

if(isset($_POST['submit'])){
 
   $schema = [
      'type' => 'object',
      'properties' => [
         'email' => [
            'type' => 'string',
            'format' => 'email',
            'maxLength' => 50
         ],
         'pass' => [
            'type' => 'string',
            'minLength' => 2,
            'maxLength' => 20
         ]
      ],
      'required' => ['email', 'pass']
   ];
   

   $data = [
      'email' => $_POST['email'],
      'pass' => $_POST['pass'] 
   ];
   

   $validation_errors = validateJsonData($data, $schema);
   
   if(empty($validation_errors)) {
      $email = filter_var($data['email'], FILTER_SANITIZE_STRING);
      $pass = sha1($data['pass']);
      $pass = filter_var($pass, FILTER_SANITIZE_STRING);


      $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ? AND password = ? LIMIT 1");
      $select_tutor->execute([$email, $pass]);
      $row_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
      
      if($select_tutor->rowCount() > 0){
  
         setcookie('tutor_id', $row_tutor['id'], time() + 60*60*24*30, '/');
         header('location:admin/dashboard.php');
         exit();
      } else {

         $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
         $select_user->execute([$email, $pass]);
         $row_user = $select_user->fetch(PDO::FETCH_ASSOC);
         
         if($select_user->rowCount() > 0){

            setcookie('user_id', $row_user['id'], time() + 60*60*24*30, '/');
            header('location:home.php');
            exit();
         } else {
            $message[] = 'Incorrect email or password!';
         }
      }
   } else {

      $message = array_merge($message, $validation_errors);
   }
}

/**
 * Validate JSON data against schema
 * @param array $data The data to validate
 * @param array $schema The schema to validate against
 * @return array Array of validation errors
 */
function validateJsonData($data, $schema) {
   $errors = [];
   

   if(!is_array($data)) {
      $errors[] = 'Invalid data format';
      return $errors;
   }
   

   foreach($schema['required'] as $requiredField) {
      if(!isset($data[$requiredField]) || trim($data[$requiredField]) === '') {
         $errors[] = ucfirst($requiredField) . ' is required';
      }
   }
   

   foreach($schema['properties'] as $property => $rules) {
      if(isset($data[$property])) {

         if($rules['type'] === 'string' && !is_string($data[$property])) {
            $errors[] = ucfirst($property) . ' must be a string';
         }
         

         if(isset($rules['format']) && $rules['format'] === 'email') {
            if(!filter_var($data[$property], FILTER_VALIDATE_EMAIL)) {
               $errors[] = 'Invalid email format';
            }
         }

         if(isset($rules['minLength']) && strlen($data[$property]) < $rules['minLength']) {
            $errors[] = 'Password must be at least ' . $rules['minLength'] . ' characters';
         }

         if(isset($rules['maxLength']) && strlen($data[$property]) > $rules['maxLength']) {
            $errors[] = 'Password must not exceed ' . $rules['maxLength'] . ' characters';
         }
      }
   }
   
   return $errors;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login - Virtu-Learn</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php
if(isset($message) && is_array($message) && count($message) > 0){
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


<script src="js/script.js"></script>
   
</body>
</html>