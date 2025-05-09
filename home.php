<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

$select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ?");
$select_likes->execute([$user_id]);
$total_likes = $select_likes->rowCount();

$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE user_id = ?");
$select_comments->execute([$user_id]);
$total_comments = $select_comments->rowCount();

$select_bookmark = $conn->prepare("SELECT * FROM `bookmark` WHERE user_id = ?");
$select_bookmark->execute([$user_id]);
$total_bookmarked = $select_bookmark->rowCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/home.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>


<div id="login-modal" class="modal">
   <div class="modal-content">
      <div class="modal-header">
         <h3><i class="fas fa-exclamation-circle"></i> Login Required</h3>
         <span class="close-modal">&times;</span>
      </div>
      <div class="modal-body">
         <p>Log in to view playlists</p>
         <div class="modal-buttons">
            <a href="login.php" class="btn">Login</a>
            <a href="register.php" class="btn">Register</a>
         </div>
      </div>
   </div>
</div>


<section class="who-we-are">
      <h1 class="heading">Who Are We</h1>
      <p>Virtu-Learn is an innovative online tutoring platform designed to connect students and professionals with expert tutors across various subjects. Our mission is to make quality education accessible, personalized, and engaging for learners of all levels.</p>
      <br>
      <p>We believe that every individual has the right to a unique, engaging, and enriching educational experience. Our mission is to create personalized learning pathways that are thoughtfully designed to meet the needs of learners from all walks of life, regardless of age, background, or skill level. </p>
      <br>
      <p>By focusing on flexibility, inclusivity, and continuous growth, we are committed to empowering learners to thrive at every step of their educational pursuits, equipping them with the tools and knowledge they need for success in an ever-evolving world. </p>
      <br>
      <p>We offer a wide variety of courses and learning materials aimed at making education exciting and accessible. Whether you're a high school student getting ready for important exams, a professional looking to learn new skills, or someone simply curious about the world, we've got something for you.</p>
   </section>

<section class="quick-select full-width">

   <h1 class="heading">quick options</h1>

   <div class="box-container">

     
      
      <?php
      
      ?>

      <div class="box">
         <h3 class="title">Top Categories</h3>
         <div class="flex">
            <a href="search_course.php?search=Web+Design"><i class="fas fa-code"></i><span>Web Design</span></a>
            <a href="search_course.php?search=Taxation"><i class="fas fa-chart-simple"></i><span>Taxation</span></a>
            <a href="search_course.php?search=Branding"><i class="fas fa-pen"></i><span>Branding</span></a>
            <a href="search_course.php?search=Accounting"><i class="fas fa-chart-line"></i><span>Accounting</span></a>
            <a href="search_course.php?search=News"><i class="fa-solid fa-newspaper"></i><span>News</span></a>
            <a href="search_course.php?search=Journalism"><i class="fas fa-camera"></i><span>Journalism</span></a>
            <a href="search_course.php?search=Graphic"><i class="fa-regular fa-object-group"></i><span>Graphics</span></a>
         </div>
      </div>
      </section>


      <section class="popular-topics">
   <div class="box-container">
      <div class="box">
         <h3 class="title">Popular Topics</h3>
         <div class="flex">
            <a href="search_course.php?search=Web"><i class="fab fa-html5"></i><span>HTML</span></a>
            <a href="search_course.php?search=Advanced+CSS"><i class="fab fa-css3"></i><span>CSS</span></a>
            <a href="search_course.php?search=Advanced+CSS+&+JavaScript"><i class="fab fa-js"></i><span>JavaScript</span></a>
            <a href="search_course.php?search=Ethical+Hacking"><i class="fa-solid fa-computer"></i><span>Hacking</span></a>
            <a href="search_course.php?search=Introduction+to+Corporate+Law"><i class="fa-solid fa-gavel"></i><span>Law</span></a>
            <a href="search_course.php?search=Cybersecurity"><i class="fa-solid fa-shield-halved"></i><span>Cybersecurity</span></a>
         </div>
      </div>
   </div>
</section>

   


<section class="courses">

   <h1 class="heading">latest courses</h1>

   <div class="box-container">

      <?php
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC LIMIT 6");
         $select_courses->execute(['active']);
         if($select_courses->rowCount() > 0){
            while($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)){
               $course_id = $fetch_course['id'];

               $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
               $select_tutor->execute([$fetch_course['tutor_id']]);
               $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);

               $select_content = $conn->prepare("SELECT COUNT(*) as content_count FROM `content` WHERE playlist_id = ? AND status = ?");
               $select_content->execute([$course_id, 'active']);
               $content_count = $select_content->fetch(PDO::FETCH_ASSOC)['content_count'];
      ?>
      <div class="box">
         <div class="tutor">
            <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" alt="">
            <div>
               <h3><?= $fetch_tutor['name']; ?></h3>
               <span><?= $fetch_course['date']; ?></span>
            </div>
         </div>
         <img src="uploaded_files/<?= $fetch_course['thumb']; ?>" class="thumb" alt="">
         <h3 class="title"><?= $fetch_course['title']; ?></h3>
         <div class="course-footer">
            <?php if(!empty($user_id)): ?>
               <a href="playlist.php?get_id=<?= $course_id; ?>" class="inline-btn">view playlist</a>
            <?php else: ?>
               <a href="javascript:void(0);" class="inline-btn view-playlist-btn" data-id="<?= $course_id; ?>">view playlist</a>
            <?php endif; ?>
            
            <?php if($content_count > 0): ?>
            <span class="lessons"><?= $content_count; ?> Videos</span>
            <?php endif; ?>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no courses added yet!</p>';
      }
      ?>

   </div>


   

</section>

<section class="become-tutor full-width">
      <h1 class="heading">Become a Tutor</h1>
      <p>Join Virtu-Learn as a tutor and share your expertise with students worldwide. Inspire and educate the next generation of learners.</p>
      <a href="admin/register.php" class="inline-btn">Get Started</a>
   </section>


<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

   const modal = document.getElementById('login-modal');
   

   const closeBtn = document.querySelector('.close-modal');
   
 
   const viewPlaylistBtns = document.querySelectorAll('.view-playlist-btn');
   
   
   viewPlaylistBtns.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
         e.preventDefault();
         
         
         modal.style.display = 'block';
      });
   });

   if (closeBtn) {
      closeBtn.addEventListener('click', function() {
         modal.style.display = 'none';
      });
   }
   
  
   window.addEventListener('click', function(event) {
      if (event.target == modal) {
         modal.style.display = 'none';
      }
   });
});
</script>

</body>
</html>