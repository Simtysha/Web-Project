<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}


if(isset($_GET['api']) && $_GET['api'] == 'courses') {
   header('Content-Type: application/json');
   
   $courses = [];
   $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC");
   $select_courses->execute(['active']);
   
   if($select_courses->rowCount() > 0){
      while($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)){
         $course_id = $fetch_course['id'];
         
         $select_tutor = $conn->prepare("SELECT id, name, image FROM `tutors` WHERE id = ?");
         $select_tutor->execute([$fetch_course['tutor_id']]);
         $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
         

         $select_content = $conn->prepare("SELECT COUNT(*) as content_count FROM `content` WHERE playlist_id = ? AND status = ?");
         $select_content->execute([$course_id, 'active']);
         $content_count = $select_content->fetch(PDO::FETCH_ASSOC)['content_count'];
         
         $courses[] = [
            'id' => $course_id,
            'title' => $fetch_course['title'],
            'description' => $fetch_course['description'],
            'thumb' => $fetch_course['thumb'],
            'date' => $fetch_course['date'],
            'tutor' => [
               'id' => $fetch_tutor['id'],
               'name' => $fetch_tutor['name'],
               'image' => $fetch_tutor['image']
            ],
            'content_count' => $content_count
         ];
      }
   }
   
   echo json_encode(['status' => 'success', 'courses' => $courses]);
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Courses</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/courses.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>



<section class="courses">

   <h1 class="heading" style="position: relative; top: 50px;">All courses</h1>
>
   <div id="login-modal" class="modal">
      <div class="modal-content">
         <div class="modal-header">
            <h3><i class="fas fa-exclamation-circle"></i> Login Required</h3>
            <span class="close-modal">&times;</span>
         </div>
         <div class="modal-body">
            <p>Log in to view playlists   </p>
            <div class="modal-buttons">
               <a href="login.php" class="btn">Login</a>
               <a href="register.php" class="btn">Register</a>
            </div>
         </div>
      </div>
   </div>

   <div class="box-container">
      <?php
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC");
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
