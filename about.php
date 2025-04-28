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


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
   <script>
   $(document).ready(function() {
      let currentSlide = 0;
      const slides = $('.carousel-slide');
      const totalSlides = slides.length;

 
      slides.hide();
      slides.first().show();

      function showSlide(index) {
         slides.fadeOut(500);
         slides.eq(index).fadeIn(500);
      }

      function nextSlide() {
         currentSlide = (currentSlide + 1) % totalSlides;
         showSlide(currentSlide);
      }


      setInterval(nextSlide, 3000);
   });
   </script>

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


      <div class="box-container" id="statistics-container">
         <h1 class="heading">Statistics</h1>
      </div>
   </section>


   <section class="reviews">
      <h1 class="heading">student's reviews</h1>
      <div class="box-container" id="reviews-container">
  
      </div>
   </section>

   <script>
   $(document).ready(function() {

      $.ajax({
         url: 'ajax/statistics_handler.php',
         type: 'GET',
         dataType: 'json',
         success: function(response) {
            if(response.status === 'success') {
               const stats = response.data;
               const container = $('#statistics-container');
               
               Object.values(stats).forEach(stat => {
                  const box = `
                     <div class="box">
                        <i class="${stat.icon}"></i>
                        <div>
                           <h3>${stat.count}</h3>
                           <span>${stat.text}</span>
                        </div>
                     </div>
                  `;
                  container.append(box);
               });
            }
         },
         error: function() {
            console.error('Error loading statistics');
         }
      });

      $.ajax({
         url: 'ajax/reviews_handler.php',
         type: 'GET',
         dataType: 'json',
         success: function(response) {
            if(response.status === 'success') {
               const reviews = response.data;
               const container = $('#reviews-container');
               
               reviews.forEach(review => {
                  const starsHtml = Array(5).fill().map((_, index) => {
                     if (index < Math.floor(review.rating)) {
                        return '<i class="fas fa-star"></i>';
                     } else if (index === Math.floor(review.rating) && review.rating % 1 !== 0) {
                        return '<i class="fas fa-star-half-alt"></i>';
                     } else {
                        return '<i class="far fa-star"></i>';
                     }
                  }).join('');

                  const box = `
                     <div class="box">
                        <p>${review.text}</p>
                        <div class="user">
                           <img src="images/${review.image}" alt="">
                           <div>
                              <h3>${review.name}</h3>
                              <div class="stars">
                                 ${starsHtml}
                              </div>
                           </div>
                        </div>
                     </div>
                  `;
                  container.append(box);
               });
            }
         },
         error: function() {
            console.error('Error loading reviews');
         }
      });
   });
   </script>

</body>
</html>