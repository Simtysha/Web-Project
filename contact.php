<?php
include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}


if(isset($_POST['submit'])){
   $name = $_POST['name']; 
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email']; 
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number']; 
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $msg = $_POST['msg']; 
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

   $select_contact = $conn->prepare("SELECT * FROM `contact` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_contact->execute([$name, $email, $number, $msg]);

   if($select_contact->rowCount() > 0){
      $message[] = 'message sent already!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO `contact`(name, email, number, message) VALUES(?,?,?,?)");
      $insert_message->execute([$name, $email, $number, $msg]);
      $message[] = 'message sent successfully!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact</title>

   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/contact.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>



<section class="contact">

   <div class="row">

      <div class="image">
         <img src="images/contact.png" alt="">
      </div>

      <form id="contactForm" action="" method="post">
         <h3>Get in Touch</h3>
         
        
         <div id="formResponse" style="display: none;"></div>
         
         <div class="form-group">
           <input type="text" placeholder="Enter Name" required maxlength="50" name="name" id="name" class="box">
           <div class="form-error" id="nameError"></div>
         </div>
         
         <div class="form-group">
           <input type="email" placeholder="Enter Email" required maxlength="50" name="email" id="email" class="box">
           <div class="form-error" id="emailError"></div>
         </div>
         
         <div class="form-group">
           <input type="number" min="0" max="99999999" placeholder="Enter Number" required maxlength="8" name="number" id="number" class="box">
           <div class="form-error" id="numberError"></div>
         </div>
         
         <div class="form-group">
           <textarea name="msg" id="msg" class="box" placeholder="Enter Message" required cols="30" rows="10" maxlength="1000"></textarea>
           <div class="form-error" id="msgError"></div>
         </div>
         
         <div class="form-loading" id="formLoading">
           <div class="spinner"></div>
           <p>Sending your message...</p>
         </div>
         
         <input type="submit" value="Send Message" class="inline-btn" name="submit" id="submitBtn">
      </form>

   </div>

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>Phone Number</h3>
         <a href="tel:5767-6983">5767-6983</a>
         <a href="tel:5828-1909">5828-1909</a>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>Email Address</h3>
         <a href="mailto:tejalbissessur@gmail.com">tejalbissessur@gmail.com</a>
         <a href="mailto:becceeasimtysha@gmail.com">becceeasimtysha@gmail.com</a>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>Office Address</h3>
         <a href="#">Reduit, Mauritius</a>
      </div>

   </div>

</section>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const formResponse = document.getElementById('formResponse');
    const formLoading = document.getElementById('formLoading');
    const submitBtn = document.getElementById('submitBtn');
    
  
    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const numberError = document.getElementById('numberError');
    const msgError = document.getElementById('msgError');
    
    function validateName(name) {
        return name.trim().length > 0 && name.trim().length <= 50;
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePhone(phone) {
        const re = /^\d{8}$/;
        return re.test(phone);
    }
    
    function validateMessage(msg) {
        return msg.trim().length > 0 && msg.trim().length <= 1000;
    }
    
    
    function resetErrors() {
        nameError.style.display = 'none';
        emailError.style.display = 'none';
        numberError.style.display = 'none';
        msgError.style.display = 'none';
        formResponse.style.display = 'none';
    }
    
    
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        
        resetErrors();
        
     
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const number = document.getElementById('number').value;
        const msg = document.getElementById('msg').value;
        
      
        let isValid = true;
        
        if (!validateName(name)) {
            nameError.textContent = 'Please enter a valid name (up to 50 characters)';
            nameError.style.display = 'block';
            isValid = false;
        }
        
        if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            emailError.style.display = 'block';
            isValid = false;
        }
        
        if (!validatePhone(number)) {
            numberError.textContent = 'Please enter a valid phone number (8 digits)';
            numberError.style.display = 'block';
            isValid = false;
        }
        
        if (!validateMessage(msg)) {
            msgError.textContent = 'Please enter a message (up to 1000 characters)';
            msgError.style.display = 'block';
            isValid = false;
        }
        
    
        if (isValid) {
          
            formLoading.style.display = 'block';
            submitBtn.disabled = true;
            
       
            const formData = new FormData();
            formData.append('name', name);
            formData.append('email', email);
            formData.append('number', number);
            formData.append('msg', msg);
            
         
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api/send_message.php', true);
            
            xhr.onload = function() {
              
                formLoading.style.display = 'none';
                submitBtn.disabled = false;
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                   
                        formResponse.style.display = 'block';
                        
                        if (response.status === 'success') {
                            formResponse.className = 'alert alert-success';
                            formResponse.innerHTML = '<p>' + response.message + '</p>';
                           
                            contactForm.reset();
                        } else if (response.status === 'warning') {
                            formResponse.className = 'alert alert-warning';
                            formResponse.innerHTML = '<p>' + response.message + '</p>';
                        } else if (response.status === 'error') {
                            formResponse.className = 'alert alert-danger';
                            
                            if (response.errors) {
                                const errorList = document.createElement('ul');
                                response.errors.forEach(function(error) {
                                    const li = document.createElement('li');
                                    li.textContent = error;
                                    errorList.appendChild(li);
                                });
                                formResponse.innerHTML = errorList.outerHTML;
                            } else {
                                formResponse.innerHTML = '<p>' + response.message + '</p>';
                            }
                        }
                    } catch (e) {
                       
                        formResponse.className = 'alert alert-danger';
                        formResponse.innerHTML = '<p>An unexpected error occurred. Please try again later.</p>';
                        formResponse.style.display = 'block';
                    }
                } else {
                
                    formResponse.className = 'alert alert-danger';
                    formResponse.innerHTML = '<p>Server error: ' + xhr.status + '. Please try again later.</p>';
                    formResponse.style.display = 'block';
                }
                
                
                formResponse.scrollIntoView({ behavior: 'smooth' });
            };
            
            xhr.onerror = function() {
            
                formLoading.style.display = 'none';
                submitBtn.disabled = false;
                formResponse.className = 'alert alert-danger';
                formResponse.innerHTML = '<p>Network error. Please check your internet connection and try again.</p>';
                formResponse.style.display = 'block';
            };
            
       
            xhr.send(formData);
        }
    });
});
</script>

>
<script src="js/script.js"></script>
   
</body>
</html>