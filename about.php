<?php

include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Us</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>


   <section class="about">
      <div class="row">
         <div class="image">
            <div class="carousel">
               <div class="carousel-slide">
                  <img src="images/sim.png" alt="Sim Image">
               </div>
               <div class="carousel-slide">
                  <img src="images/tejal.png" alt="Tejal Image">
               </div>
            </div>
         </div>

         <div class="content">

            <h3>Co-Founders</h3>
            <p>We're Tejal and Simtysha, co-founders of Virtu-Learn, an online course platform designed to make education accessible to everyone. Our mission is to create an engaging learning experience that helps people gain new skills and grow both personally and professionally. Whether you're looking to start a new journey or advance your knowledge, Virtu-Learn is here to support you every step of the way!</p>
            <br>

            <h4>Why choose us?</h4>
            <p>Virtu-Learn combines personalized tutoring with expert educators and flexible scheduling to support every learner. Our passionate tutors create engaging learning experiences, making education effective and enjoyable. Whether you need homework help or exam preparation, we provide a safe and supportive environment to help you achieve your academic goals. Unlock your potential with Virtu-Learn!</p>
         </div>

         <a href="courses.php">
            <button class="view-courses-btn">View Our Courses</button>
         </a>
      </div>


      <div class="box-container">
         <h1 class="heading">Statistics</h1>

         <div class="box">
            <i class="fas fa-graduation-cap"></i>
            <div>
               <h3>+1k</h3>
               <span>online courses</span>
            </div>
         </div>

         <div class="box">
            <i class="fas fa-user-graduate"></i>
            <div>
               <h3>+25k</h3>
               <span>brilliant students</span>
            </div>
         </div>

         <div class="box">
            <i class="fas fa-chalkboard-user"></i>
            <div>
               <h3>+5k</h3>
               <span>expert teachers</span>
            </div>
         </div>

         <div class="box">
            <i class="fas fa-briefcase"></i>
            <div>
               <h3>100%</h3>
               <span>job placement</span>
            </div>
         </div>
      </div>
   </section>
   <!-- about section ends -->

   <!-- reviews section starts  -->

   <section class="reviews">

      <h1 class="heading">student's reviews</h1>

      <div class="box-container">

         <div class="box">
            <p>Virtu-Learn offers a seamless online tutoring experience with flexible scheduling, expert tutors, and a user-friendly platform. Perfect for students and professionals alike!</p>
            <div class="user">
               <img src="images/ReviewProfile1.jpg" alt="">
               <div>
                  <h3>Aria Thompson</h3>
                  <div class="stars">
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
         </div>

         <div class="box">
            <p>The structured courses and interactive features on Virtu-Learn make learning engaging and effective. Highly recommend for anyone looking to upskill conveniently!</p>
            <div class="user">
               <img src="images/ReviewProfile2.jpg" alt="">
               <div>
                  <h3>Noah Sullivan</h3>
                  <div class="stars">
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
         </div>

         <div class="box">
            <p>A highly engaging and well-designed platform! Virtu-Learn’s personalized learning approach ensures students grasp concepts effectively while enjoying the learning process.</p>
            <div class="user">
               <img src="images/ReviewProfile3.jpg" alt="">
               <div>
                  <h3>Caleb Anderson</h3>
                  <div class="stars">
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
         </div>

         <div class="box">
            <p>Virtu-Learn provides top-notch education with knowledgeable tutors, easy navigation, and great flexibility. Learning at your own pace has never been this effortless!</p>
            <div class="user">
               <img src="images/ReviewProfile4.jpg" alt="">
               <div>
                  <h3>Elena Reynolds</h3>
                  <div class="stars">
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star"></i>
                     <i class="fas fa-star-half-alt"></i>
                  </div>
               </div>
            </div>
         </div>

      </div>

   </section>

   <!-- reviews section ends -->

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>