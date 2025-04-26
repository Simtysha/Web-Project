<?php
include 'components/connect.php';

// Check login status once at the beginning
if (!isset($_COOKIE['user_id'])) {
   header('location:home.php');
   exit();
}

$user_id = $_COOKIE['user_id'];

// Handle AJAX request for removing likes
if (isset($_POST['ajax_remove'])) {
   $content_id = filter_var($_POST['content_id'], FILTER_SANITIZE_STRING);

   // Directly attempt to delete - no need to verify first since we'll get a success/error based on rows affected
   $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
   $remove_likes->execute([$user_id, $content_id]);

   $response = [
      'status' => ($remove_likes->rowCount() > 0) ? 'success' : 'error'
   ];

   // Return JSON response
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

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- jQuery CDN -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .removed {
         background-color: #ccc !important;
         cursor: default !important;
      }

      .hidden {
         display: none;
      }

      .empty-container {
         text-align: center;
         padding: 2rem;
         font-size: 1.2rem;
      }

      .notification {
         position: fixed;
         bottom: 20px;
         right: 20px;
         padding: 15px 25px;
         border-radius: 8px;
         background-color: #4CAF50;
         color: white;
         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
         z-index: 1000;
         display: none;
         font-size: 1.6rem;
         transform: translateY(100%);
         animation: slideUp 0.3s forwards;
      }

      @keyframes slideUp {
         to {
            transform: translateY(0);
         }
      }

      .notification.error {
         background-color: #f44336;
      }
   </style>
</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <!-- Notification area -->
   <div id="notification" class="notification"></div>

   <section class="liked-videos">
      <h1 class="heading">liked videos</h1>

      <div class="box-container">
         <?php
         // Optimized query using JOIN to fetch all data at once
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

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

   <script>
      $(document).ready(function() {
         // Notification function
         function showNotification(message, isError = false) {
            const notification = $('#notification');
            notification.text(message).toggleClass('error', isError).fadeIn();
            setTimeout(() => notification.fadeOut(), 3000);
         }

         // Handle remove like button click with AJAX
         $(document).on('click', '.remove-like-btn', function(e) {
            e.preventDefault();

            const button = $(this);
            const contentId = button.data('id');
            const contentBox = $('#content-' + contentId);

            // Disable button to prevent multiple clicks
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
                     // Visual indication of removal
                     button.text('removed').addClass('removed');

                     // Show success notification
                     showNotification('Video removed from likes.');

                     // Remove item after a short delay
                     setTimeout(function() {
                        contentBox.fadeOut(300, function() {
                           $(this).remove();

                           // If no more likes exist, show empty message
                           if ($('.box-container .box').length === 0) {
                              $('.box-container').html('<div class="empty-container"><p class="empty">Nothing added to likes yet!</p></div>');
                           }
                        });
                     }, 500);
                  } else {
                     // Show error notification
                     showNotification('Error removing video from likes', true);
                     // Re-enable button if error
                     button.prop('disabled', false);
                  }
               },
               error: function() {
                  // Show error notification
                  showNotification('Error processing your request', true);
                  // Re-enable button if error
                  button.prop('disabled', false);
               }
            });
         });
      });
   </script>

</body>

</html>