<?php

include 'components/connect.php';

if (!isset($_COOKIE['user_id'])) {
   header('location:home.php');
   exit();
}

$user_id = $_COOKIE['user_id'];
$get_id = isset($_GET['get_id']) ? filter_var($_GET['get_id'], FILTER_SANITIZE_STRING) : '';

if (!$get_id) {
   header('location:home.php');
   exit();
}


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
   $response = array('status' => 'error', 'message' => 'Invalid action');

   if (isset($_POST['action']) && $_POST['action'] === 'toggle_bookmark') {
      try {
         $list_id = filter_var($_POST['list_id'], FILTER_SANITIZE_STRING);

        
         $verify_playlist = $conn->prepare("SELECT id FROM `playlist` WHERE id = ? AND status = 'active' LIMIT 1");
         $verify_playlist->execute([$list_id]);

         if ($verify_playlist->rowCount() == 0) {
            $response = array('status' => 'error', 'message' => 'Invalid playlist or playlist is not active');
         } else {
            $select_list = $conn->prepare("SELECT 1 FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
            $select_list->execute([$user_id, $list_id]);

            if ($select_list->rowCount() > 0) {
               $remove_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
               $remove_bookmark->execute([$user_id, $list_id]);
               $response = array('status' => 'success', 'message' => 'Playlist removed from bookmarks!', 'bookmarked' => false);
            } else {
               $insert_bookmark = $conn->prepare("INSERT INTO `bookmark`(user_id, playlist_id) VALUES(?,?)");
               $insert_bookmark->execute([$user_id, $list_id]);
               $response = array('status' => 'success', 'message' => 'Playlist saved to bookmarks!', 'bookmarked' => true);
            }
         }
      } catch (PDOException $e) {
         $response = array('status' => 'error', 'message' => 'Database error occurred');
         error_log("Database Error: " . $e->getMessage());
      }
   }

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
   <title>Playlist</title>

   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/playlist.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>


   <div id="notification" class="notification"></div>



   <section class="playlist">

      <h1 class="heading">Playlist Details</h1>

      <div class="row">

         <?php
         $select_playlist = $conn->prepare("
            SELECT p.*, t.name as tutor_name, t.profession, t.image as tutor_image,
                   (SELECT COUNT(*) FROM `content` WHERE playlist_id = p.id AND status = 'active') as total_videos
            FROM `playlist` p
            JOIN `tutors` t ON p.tutor_id = t.id
            WHERE p.id = ? AND p.status = ?
            LIMIT 1
         ");
         $select_playlist->execute([$get_id, 'active']);

         if ($select_playlist->rowCount() > 0) {
            $fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);
            $playlist_id = $fetch_playlist['id'];

            $select_bookmark = $conn->prepare("SELECT 1 FROM `bookmark` WHERE user_id = ? AND playlist_id = ?");
            $select_bookmark->execute([$user_id, $playlist_id]);
            $is_bookmarked = $select_bookmark->rowCount() > 0;
         ?>

            <div class="col">
               <div class="save-list">
                  <button type="button" class="bookmark-btn" data-id="<?= $playlist_id; ?>">
                     <i class="<?= $is_bookmarked ? 'fas' : 'far'; ?> fa-bookmark"></i>
                     <span><?= $is_bookmarked ? 'Saved' : 'Save Playlist'; ?></span>
                  </button>
               </div>
               <div class="thumb">
                  <span class="video-count"><?= $fetch_playlist['total_videos']; ?> videos</span>
                  <img src="uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="Playlist Thumbnail">
               </div>
            </div>

            <div class="col">
               <div class="tutor">
                  <img src="uploaded_files/<?= $fetch_playlist['tutor_image']; ?>" alt="Tutor Image">
                  <div>
                     <h3><?= $fetch_playlist['tutor_name']; ?></h3>
                     <span><?= $fetch_playlist['profession']; ?></span>
                  </div>
               </div>
               <div class="details">
                  <h3><?= $fetch_playlist['title']; ?></h3>
                  <p><?= $fetch_playlist['description']; ?></p>
                  <div class="date"><i class="fas fa-calendar"></i><span><?= $fetch_playlist['date']; ?></span></div>
               </div>
            </div>

         <?php
         } else {
            echo '<p class="empty">this playlist was not found!</p>';
         }
         ?>

      </div>

   </section>



   <section class="videos-container">

      <h1 class="heading">Playlist Videos</h1>

      <div class="box-container">

         <?php
         if (isset($playlist_id)) {
            $select_content = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ? AND status = ? ORDER BY date DESC");
            $select_content->execute([$playlist_id, 'active']);

            if ($select_content->rowCount() > 0) {
               while ($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)) {
         ?>
                  <a href="watch_video.php?get_id=<?= $fetch_content['id']; ?>" class="box">
                     <i class="fas fa-play"></i>
                     <img src="uploaded_files/<?= $fetch_content['thumb']; ?>" alt="Video Thumbnail">
                     <h3><?= $fetch_content['title']; ?></h3>
                  </a>
         <?php
               }
            } else {
               echo '<p class="empty">no videos added yet!</p>';
            }
         }
         ?>

      </div>

   </section>


   










 
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
   <script src="js/script.js"></script>

   <script>
      $(document).ready(function() {
         function showNotification(message, isError = false) {
            const notification = $('#notification');
            notification.text(message).toggleClass('error', isError).fadeIn();
            setTimeout(() => notification.fadeOut(), 3000);
         }

         $(document).on('click', '.bookmark-btn', function() {
            const button = $(this);
            const listId = button.data('id');

          
            button.prop('disabled', true);

            $.ajax({
               url: window.location.href,
               type: 'POST',
               data: {
                  action: 'toggle_bookmark',
                  list_id: listId
               },
               dataType: 'json',
               success: function(response) {
                  if (response.status === 'success') {
                    
                     const icon = button.find('i');
                     const text = button.find('span');

                     if (response.bookmarked) {
                        icon.removeClass('far').addClass('fas');
                        text.text('Saved');
                     } else {
                        icon.removeClass('fas').addClass('far');
                        text.text('Save Playlist');
                     }

                     showNotification(response.message);
                  } else {
                     showNotification(response.message || 'Error saving playlist', true);
                  }
               },
               error: function(xhr, status, error) {
                  console.error('AJAX Error:', error);
                  showNotification('Error processing your request. Please try again.', true);
               },
               complete: function() {
              
                  button.prop('disabled', false);
               }
            });
         });
      });
   </script>

</body>

</html>