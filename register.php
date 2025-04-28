<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}


// Flag to control where messages are displayed
$display_in_header = false;

// Check if form type is selected
$form_type = isset($_GET['type']) ? $_GET['type'] : '';

if(isset($_POST['submit'])){
    
    // Define JSON schema for different registration types
    $common_schema = [
        'type' => 'object',
        'properties' => [
            'name' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 50
            ],
            'email' => [
                'type' => 'string',
                'format' => 'email',
                'maxLength' => 50
            ],
            'pass' => [
                'type' => 'string',
                'minLength' => 3,
                'maxLength' => 20
            ],
            'cpass' => [
                'type' => 'string',
                'minLength' => 3,
                'maxLength' => 20
            ]
        ],
        'required' => ['name', 'email', 'pass', 'cpass']
    ];
    
    // Additional schema for tutor registration
    $tutor_schema = $common_schema;
    $tutor_schema['properties']['profession'] = [
        'type' => 'string',
        'enum' => [
            'developer', 'designer', 'engineer', 'lawyer', 'accountant',
            'doctor', 'journalist', 'photographer', 'IT Specialist'
        ]
    ];
    $tutor_schema['required'][] = 'profession';
    
    // Select appropriate schema based on registration type
    $schema = ($_POST['register_type'] == 'tutor') ? $tutor_schema : $common_schema;
    
    // Prepare data for validation
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'pass' => $_POST['pass'],
        'cpass' => $_POST['cpass']
    ];
    
    // Add profession for tutor registration
    if($_POST['register_type'] == 'tutor') {
        $data['profession'] = $_POST['profession'];
    }
    
    // Validate JSON data against schema
    $validation_errors = validateJsonData($data, $schema);
    
    // Additional validation for matching passwords
    if($data['pass'] !== $data['cpass']) {
        $validation_errors[] = 'Confirm password does not match!';
    }
    
    // File validation - only checking file type, not requiring it
    if(isset($_FILES['image']) && $_FILES['image']['name'] !== '') {
        $file_validation_errors = validateFileUpload($_FILES['image']);
        $validation_errors = array_merge($validation_errors, $file_validation_errors);
    }
    
    // Process registration if no validation errors
    if(empty($validation_errors)) {
        $id = unique_id();
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_STRING);
        $pass = sha1($data['pass']);
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);
        $cpass = sha1($data['cpass']);
        $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

        // Process image if it was uploaded
        $rename = '';
        if(isset($_FILES['image']) && $_FILES['image']['name'] !== '') {
            $image = $_FILES['image']['name'];
            $image = filter_var($image, FILTER_SANITIZE_STRING);
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $rename = unique_id().'.'.$ext;
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = 'uploaded_files/'.$rename;
            move_uploaded_file($image_tmp_name, $image_folder);
        }

        // Registration logic for student
        if($_POST['register_type'] == 'student') {
            $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
            $select_user->execute([$email]);
            
            if($select_user->rowCount() > 0){
                $message = ['Email already taken!'];
            } else {
                $insert_user = $conn->prepare("INSERT INTO `users`(id, name, email, password, image) VALUES(?,?,?,?,?)");
                $insert_user->execute([$id, $name, $email, $cpass, $rename]);
                
                $verify_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ? LIMIT 1");
                $verify_user->execute([$email, $pass]);
                $row = $verify_user->fetch(PDO::FETCH_ASSOC);
                
                if($verify_user->rowCount() > 0){
                    setcookie('user_id', $row['id'], time() + 60*60*24*30, '/');
                    header('location:home.php');
                    exit();
                }
            }
        }
        // Registration logic for tutor
        else if($_POST['register_type'] == 'tutor') {
            $profession = filter_var($data['profession'], FILTER_SANITIZE_STRING);
            
            $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
            $select_tutor->execute([$email]);
            
            if($select_tutor->rowCount() > 0){
                $message = ['Email already taken!'];
            } else {
                $insert_tutor = $conn->prepare("INSERT INTO `tutors`(id, name, profession, email, password, image) VALUES(?,?,?,?,?,?)");
                $insert_tutor->execute([$id, $name, $profession, $email, $cpass, $rename]);
                
                // Set cookie for the newly registered admin
                setcookie('tutor_id', $id, time() + 60*60*24*30, '/');
                
                // Redirect to dashboard directly after successful registration
                header('Location: admin/dashboard.php');
                exit();
            }
        }
    } else {
        // Set validation errors as messages
        $message = $validation_errors;
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
   
   // Basic type validation
   if(!is_array($data)) {
       $errors[] = 'Invalid data format';
       return $errors;
   }
   
   // Check required fields
   foreach($schema['required'] as $requiredField) {
       if(!isset($data[$requiredField]) || trim($data[$requiredField]) === '') {
           $errors[] = ucfirst($requiredField) . ' is required';
       }
   }
   
   // Validate properties
   foreach($schema['properties'] as $property => $rules) {
       if(isset($data[$property])) {
           // Check type
           if($rules['type'] === 'string' && !is_string($data[$property])) {
               $errors[] = ucfirst($property) . ' must be a string';
           }
           
           // Check format if specified
           if(isset($rules['format']) && $rules['format'] === 'email') {
               if(!filter_var($data[$property], FILTER_VALIDATE_EMAIL)) {
                   $errors[] = 'Invalid email format';
               }
           }
           
           // Check minLength if specified (only for password)
           if(isset($rules['minLength']) && $property === 'pass' && strlen($data[$property]) < $rules['minLength']) {
               $errors[] = 'Password must be at least ' . $rules['minLength'] . ' characters';
           }
           
           // Check maxLength if specified (only for password)
           if(isset($rules['maxLength']) && $property === 'pass' && strlen($data[$property]) > $rules['maxLength']) {
               $errors[] = 'Password must not exceed ' . $rules['maxLength'] . ' characters';
           }
           
           // Check enum if specified
           if(isset($rules['enum']) && !in_array($data[$property], $rules['enum'])) {
               $errors[] = ucfirst($property) . ' must be one of the allowed values';
           }
       }
   }
   
   return $errors;
}

/**
 * Validate file upload
 * @param array $file The file data from $_FILES
 * @return array Array of validation errors
 */
function validateFileUpload($file) {
    $errors = [];
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if(!in_array($file['type'], $allowed_types)) {
        $errors[] = 'Only JPG, PNG, GIF, and WEBP files are allowed';
    }
    
    return $errors;
}

// Function to generate unique ID (assuming this exists in your application)
if(!function_exists('unique_id')) {
    function unique_id() {
        return uniqid();
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

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- Display error messages only here, not in the header -->
<?php
if(isset($message)){
   if(is_array($message)){
      foreach($message as $msg){
         echo '
         <div class="message">
            <span>'.$msg.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   } else {
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container" style="margin-top:25px;">

   <?php if($form_type == ''): ?>
   
   <!-- Selection form for registration type -->
   <div class="register" style="text-align: center;">
   <h3 style="font-size: 2.5rem; color:rgb(0, 0, 0); margin-bottom: 1rem; text-transform: capitalize;">Create Account</h3>
   <p style="font-size: 1.8rem; color: #666; margin-bottom: 2rem;">Please select your registration type</p>
      <div class="flex" style="justify-content: center; margin-top: 20px;">
         <a href="register.php?type=student" class="btn" style="margin-right: 10px;">Register as Student</a>
         <a href="register.php?type=tutor" class="btn">Register as Tutor</a>
      </div>
      <p class="link" style="margin-top: 20px;">Already have an account? <a href="login.php">Login now</a></p>
   </div>

   <?php elseif($form_type == 'student'): ?>
   
   <!-- Student registration form -->
   <form class="register" action="" method="post" enctype="multipart/form-data">
      <h3>Create Student Account</h3>
      <input type="hidden" name="register_type" value="student">
      <div class="flex">
         <div class="col">
            <p>Name <span>*</span></p>
            <input type="text" name="name" placeholder="Enter Name" maxlength="50" required class="box">
            <p>Email <span>*</span></p>
            <input type="email" name="email" placeholder="Enter Email" maxlength="50" required class="box">
         </div>
         <div class="col">
            <p>Password <span>*</span></p>
            <input type="password" name="pass" placeholder="Enter Password" maxlength="20" required class="box">
            <p>Confirm Password <span>*</span></p>
            <input type="password" name="cpass" placeholder="Confirm Password" maxlength="20" required class="box">
         </div>
      </div>
      <p>Select Picture <span>*</span></p>
      <input type="file" name="image" accept="image/*" class="box">
      <p class="link">Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" name="submit" value="Register now" class="btn">
      <p class="link"><a href="register.php">Back to registration options</a></p>
   </form>

   <?php elseif($form_type == 'tutor'): ?>
   
   <!-- Tutor registration form -->
   <form class="register" action="" method="post" enctype="multipart/form-data">
      <h3>Create Tutor Account</h3>
      <input type="hidden" name="register_type" value="tutor">
      <div class="flex">
         <div class="col">
            <p>Name <span>*</span></p>
            <input type="text" name="name" placeholder="Enter name" maxlength="50" required class="box">
            <p>Profession <span>*</span></p>
            <select name="profession" class="box" required>
               <option value="" disabled selected>Select profession</option>
               <option value="developer">Developer</option>
               <option value="designer">Designer</option>
               <option value="engineer">Engineer</option>
               <option value="lawyer">Lawyer</option>
               <option value="accountant">Accountant</option>
               <option value="doctor">Doctor</option>
               <option value="journalist">Journalist</option>
               <option value="photographer">Photographer</option>
               <option value="IT Specialist">IT Specialist</option>
            </select>
            <p>Email <span>*</span></p>
            <input type="email" name="email" placeholder="Enter email" maxlength="50" required class="box">
         </div>
         <div class="col">
            <p>Password <span>*</span></p>
            <input type="password" name="pass" placeholder="Enter password" maxlength="20" required class="box">
            <p>Confirm Password <span>*</span></p>
            <input type="password" name="cpass" placeholder="Confirm password" maxlength="20" required class="box">
            <p>Select Picture <span>*</span></p>
            <input type="file" name="image" accept="image/*" class="box">
         </div>
      </div>
      <p class="link">Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" name="submit" value="Register now" class="btn">
      <p class="link"><a href="register.php">Back to registration options</a></p>
   </form>

   <?php endif; ?>

</section>

<!-- custom js file link  -->
<script src="js/script.js"></script>
   
</body>
</html>