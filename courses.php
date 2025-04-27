<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

// JSON API endpoint for courses data
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
         
         // Get content count for this course
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

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- courses section starts  -->

<section class="courses">

   <h1 class="heading" style="position: relative; top: 50px;">All courses</h1>
   
   <!-- Login notification modal (initially hidden) -->
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

               // Get content count for this course
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

<!-- courses section ends -->



<!-- custom js file link  -->
<script src="js/script.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Get the modal
   const modal = document.getElementById('login-modal');
   
   // Get the close button
   const closeBtn = document.querySelector('.close-modal');
   
   // Get all view playlist buttons
   const viewPlaylistBtns = document.querySelectorAll('.view-playlist-btn');
   
   // Add click event to all view playlist buttons
   viewPlaylistBtns.forEach(function(btn) {
      btn.addEventListener('click', function(e) {
         e.preventDefault();
         
         // Show the login modal
         modal.style.display = 'block';
      });
   });
   
   // Close the modal when clicking the close button
   if (closeBtn) {
      closeBtn.addEventListener('click', function() {
         modal.style.display = 'none';
      });
   }
   
   // Close the modal when clicking outside of it
   window.addEventListener('click', function(event) {
      if (event.target == modal) {
         modal.style.display = 'none';
      }
   });
});
</script>

<style>
/* Modal styles */
.modal {
   display: none;
   position: fixed;
   z-index: 1000;
   left: 0;
   top: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0,0,0,0.5);
   overflow: auto;
}

.modal-content {
   background-color: #fff;
   margin: 15% auto;
   width: 90%;
   max-width: 500px;
   border-radius: 10px;
   box-shadow: 0 5px 15px rgba(0,0,0,0.3);
   animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
   from {opacity: 0; transform: translateY(-20px);}
   to {opacity: 1; transform: translateY(0);}
}

.modal-header {
   padding: 15px 20px;
   background-color:rgb(41, 3, 64);
   color: white;
   border-radius: 10px 10px 0 0;
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.modal-header h3 {
   margin: 0;
   font-size: 18px;
   display: flex;
   align-items: center;
}

.modal-header h3 i {
   margin-right: 10px;
}

.close-modal {
   color: white;
   font-size: 28px;
   font-weight: bold;
   cursor: pointer;
}

.modal-body {
   padding: 20px;
   text-align: center;
}

.modal-body p {
   margin-bottom: 20px;
   font-size: 16px;
}

.modal-buttons {
   display: flex;
   justify-content: center;
   gap: 15px;
   margin-top: 15px;
}

.modal-buttons .btn {
   padding: 10px 20px;
   background-color:rgb(41, 3, 64);
   color: white;
   text-decoration: none;
   border-radius: 5px;
   font-weight: 500;
   transition: background-color 0.3s;
}

.modal-buttons .btn:hover {
   background-color: #dfbbf2;;
}

/* Course styles */
.courses .box {
   position: relative;
}

.courses .box .desc {
   margin: 10px 0;
   line-height: 1.5;
   color: #666;
}

.courses .box .course-footer {
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.courses .box .lessons {
   color:rgb(255, 255, 255);
   font-size: 12px;
   margin-left: 70px;
   margin-top: 30px;
}

.course-footer .lessons {
   background-color:rgb(41, 3, 64);;
   padding: 5px 10px;
   border-radius: 20px;
   font-size: 12px;
   font-weight: 500;
}

/* Remove border radius from modal boxes */
.login-required-modal,
.modal-content,
.modal-dialog,
.modal-box,
.modal-wrapper,
/* Add any other modal-related class names */
[class*="modal"] {
    border-radius: 0 !important;
}

/* Target specific button elements */
.modal-content .btn,
.login-btn,
.register-btn,
button[class*="login"],
button[class*="register"] {
    border-radius: 0 !important;
}


</style>