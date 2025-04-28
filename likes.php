<?php
include 'components/connect.php';


if (!isset($_COOKIE['user_id'])) {
   header('location:home.php');
   exit();
}

$user_id = $_COOKIE['user_id'];


if (isset($_POST['ajax_remove'])) {
   $content_id = filter_var($_POST['content_id'], FILTER_SANITIZE_STRING);

   $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
   $remove_likes->execute([$user_id, $content_id]);

   $response = [
      'status' => ($remove_likes->rowCount() > 0) ? 'success' : 'error'
   ];


   header('Content-Type: application/json');
   echo json_encode($response);
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Liked Videos</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/likes.css">
</head>

<body>

   <?php include 'components/user_header.php'; ?>


   <div id="notification" class="notification"></div>

   <section class="liked-videos">
      <h1 class="heading">liked videos</h1>

      <div class="box-container">
         <?php

         $query = "SELECT c.*, t.name as tutor_name, t.image as tutor_image 
                FROM `likes` l 
                JOIN `content` c ON l.content_id = c.id 
                JOIN `tutors` t ON c.tutor_id = t.id 
                WHERE l.user_id = ? 
                ORDER BY c.date DESC";

         $select_content = $conn->prepare($query);
         $select_content->execute([$user_id]);

         if ($select_content->rowCount() > 0) {
            while ($row = $select_content->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="box" id="content-<?= $row['id']; ?>">
                  <div class="tutor">
                     <img src="uploaded_files/<?= $row['tutor_image']; ?>" alt="">
                     <div>
                        <h3><?= $row['tutor_name']; ?></h3>
                        <span><?= $row['date']; ?></span>
                     </div>
                  </div>
                  <img src="uploaded_files/<?= $row['thumb']; ?>" alt="" class="thumb">
                  <h3 class="title"><?= $row['title']; ?></h3>
                  <div class="flex-btn">
                     <a href="watch_video.php?get_id=<?= $row['id']; ?>" class="inline-btn">watch video</a>
                     <button type="button" class="inline-delete-btn remove-like-btn" data-id="<?= $row['id']; ?>">remove</button>
                  </div>
               </div>
            <?php
            }
         } else {
            ?>
            <div class="empty-container">
               <p class="empty">Nothing added to likes yet!</p>
            </div>
         <?php
         }
         ?>
      </div>
   </section>

  
   <script src="js/script.js"></script>

   <script>
      $(document).ready(function() {
    
         function showNotification(message, isError = false) {
            const notification = $('#notification');
            notification.text(message).toggleClass('error', isError).fadeIn();
            setTimeout(() => notification.fadeOut(), 3000);
         }

    
         $(document).on('click', '.remove-like-btn', function(e) {
            e.preventDefault();

            const button = $(this);
            const contentId = button.data('id');
            const contentBox = $('#content-' + contentId);

         
            button.prop('disabled', true);

            $.ajax({
               url: 'likes.php',
               type: 'POST',
               data: {
                  ajax_remove: 1,
                  content_id: contentId
               },
               dataType: 'json',
               cache: false,
               success: function(response) {
                  if (response.status === 'success') {
                   
                     button.text('removed').addClass('removed');

                     
                     showNotification('Video removed from likes.');

                    
                     setTimeout(function() {
                        contentBox.fadeOut(300, function() {
                           $(this).remove();

                          
                           if ($('.box-container .box').length === 0) {
                              $('.box-container').html('<div class="empty-container"><p class="empty">Nothing added to likes yet!</p></div>');
                           }
                        });
                     }, 500);
                  } else {
                  
                     showNotification('Error removing video from likes', true);
                 
                     button.prop('disabled', false);
                  }
               },
               error: function() {
            
                  showNotification('Error processing your request', true);
              
                  button.prop('disabled', false);
               }
            });
         });
      });
   </script>

</body>

</html>
