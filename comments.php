<?php
include 'components/connect.php';


if (!isset($_COOKIE['user_id'])) {
   header('location:home.php');
   exit();
}


$user_id = $_COOKIE['user_id'];


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
   $response = array('status' => 'error', 'message' => 'Invalid action');

   if (isset($_POST['action'])) {
      $comment_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);

      switch ($_POST['action']) {
         case 'delete_comment':
            $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ? AND user_id = ?");
            $delete_comment->execute([$comment_id, $user_id]);

            $response = array(
               'status' => $delete_comment->rowCount() > 0 ? 'success' : 'error',
               'message' => $delete_comment->rowCount() > 0 ? 'Comment deleted successfully' : 'Comment could not be deleted'
            );
            break;

         case 'update_comment':
            $update_text = filter_var($_POST['comment_text'], FILTER_SANITIZE_STRING);

            $verify_comment = $conn->prepare("SELECT 1 FROM `comments` WHERE id = ? AND user_id = ?");
            $verify_comment->execute([$comment_id, $user_id]);

            if ($verify_comment->rowCount() > 0) {
               $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
               $update_comment->execute([$update_text, $comment_id]);
               $response = array('status' => 'success', 'message' => 'Comment updated successfully');
            } else {
               $response = array('status' => 'error', 'message' => 'Comment not found');
            }
            break;
      }
   }

   if (isset($_GET['action']) && $_GET['action'] == 'get_comment') {
      $comment_id = filter_var($_GET['comment_id'], FILTER_SANITIZE_STRING);

      $get_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND user_id = ?");
      $get_comment->execute([$comment_id, $user_id]);

      if ($get_comment->rowCount() > 0) {
         $comment_data = $get_comment->fetch(PDO::FETCH_ASSOC);
         $response = array('status' => 'success', 'comment' => $comment_data);
      } else {
         $response = array('status' => 'error', 'message' => 'Comment not found');
      }
   }

   header('Content-Type: application/json');
   echo json_encode($response);
   exit();
}


if (isset($_POST['delete_comment'])) {
   $delete_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);

   $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ? AND user_id = ?");
   $delete_comment->execute([$delete_id, $user_id]);

   if ($delete_comment->rowCount() > 0) {
      $message[] = 'Comment deleted successfully!';
   } else {
      $message[] = 'Comment could not be deleted!';
   }
}

if (isset($_POST['update_now'])) {
   $update_id = filter_var($_POST['update_id'], FILTER_SANITIZE_STRING);
   $update_box = filter_var($_POST['update_box'], FILTER_SANITIZE_STRING);

   $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ? AND user_id = ?");
   $update_comment->execute([$update_box, $update_id, $user_id]);

   if ($update_comment->rowCount() > 0) {
      $message[] = 'Comment updated successfully!';
   } else {
      $message[] = 'Comment could not be updated!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Comments</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

 
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/comment.css">
</head>

<body>

   <?php include 'components/user_header.php'; ?>
>
   <div id="notification" class="notification"></div>


   <div id="edit-modal">
      <div class="modal-content">
         <h2>Edit Comment</h2>
         <form id="edit-comment-form">
            <input type="hidden" id="edit-comment-id" name="comment_id">
            <textarea id="edit-comment-text" name="comment_text" maxlength="1000" required rows="5" placeholder="Enter your comment here..."></textarea>
            <div class="modal-buttons">
               <button type="button" class="inline-option-btn" id="cancel-edit">Cancel</button>
               <button type="submit" class="inline-btn">Update Comment</button>
            </div>
         </form>
      </div>
   </div>

   <section class="comments">
      <h1 class="heading">Your Comments</h1>

      <div class="show-comments" id="comments-container">
         <?php
        
         $query = "SELECT c.*, ct.title as content_title, ct.id as content_id 
                  FROM `comments` c
                  JOIN `content` ct ON c.content_id = ct.id
                  WHERE c.user_id = ?
                  ORDER BY c.date DESC";

         $select_comments = $conn->prepare($query);
         $select_comments->execute([$user_id]);

         if ($select_comments->rowCount() > 0) {
            while ($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="box comment-box" id="comment-<?= $fetch_comment['id']; ?>">
                  <div class="content">
                     <span><?= $fetch_comment['date']; ?></span>
                     <p> - <?= $fetch_comment['content_title']; ?> - </p>
                     <a href="watch_video.php?get_id=<?= $fetch_comment['content_id']; ?>">view content</a>
                  </div>
                  <p class="text comment-text"><?= $fetch_comment['comment']; ?></p>

                  <div class="comment-actions">
                     <button type="button" class="inline-option-btn edit-btn" data-id="<?= $fetch_comment['id']; ?>">
                        <i class="fas fa-edit"></i> Edit
                     </button>
                     <button type="button" class="inline-delete-btn delete-btn" data-id="<?= $fetch_comment['id']; ?>">
                        <i class="fas fa-trash"></i> Delete
                     </button>
                  </div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">No comments added yet!</p>';
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

        
         $(document).on('click', '.delete-btn', function() {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            const commentId = $(this).data('id');
            const commentBox = $('#comment-' + commentId);

            commentBox.addClass('fade-out');

            $.ajax({
               url: 'comments.php',
               type: 'POST',
               data: {
                  action: 'delete_comment',
                  comment_id: commentId
               },
               dataType: 'json',
               success: function(response) {
                  if (response.status === 'success') {
                     commentBox.slideUp(300, function() {
                        $(this).remove();

                        if ($('.comment-box').length === 0) {
                           $('#comments-container').html('<p class="empty">No comments added yet!</p>');
                        }
                     });
                     showNotification('Comment deleted successfully');
                  } else {
                     commentBox.removeClass('fade-out');
                     showNotification(response.message, true);
                  }
               },
               error: function() {
                  commentBox.removeClass('fade-out');
                  showNotification('Error processing your request', true);
               }
            });
         });
n
         $(document).on('click', '.edit-btn', function() {
            const commentId = $(this).data('id');
            const commentText = $('#comment-' + commentId + ' .comment-text').text();

            $('#edit-comment-id').val(commentId);
            $('#edit-comment-text').val(commentText).focus();

            
            $('#edit-modal').css('display', 'flex').addClass('active');
         });

         function closeModal() {
            $('#edit-modal').removeClass('active');
            setTimeout(() => {
               $('#edit-modal').hide();
            }, 300);
         }

         $('#cancel-edit').click(closeModal);

         $(window).click(function(e) {
            if (e.target.id === 'edit-modal') {
               closeModal();
            }
         });

        
         $('#edit-comment-form').submit(function(e) {
            e.preventDefault();

            const commentId = $('#edit-comment-id').val();
            const commentText = $('#edit-comment-text').val();
            const commentBox = $('#comment-' + commentId);

            $.ajax({
               url: 'comments.php',
               type: 'POST',
               data: {
                  action: 'update_comment',
                  comment_id: commentId,
                  comment_text: commentText
               },
               dataType: 'json',
               beforeSend: function() {
               
                  $('#edit-comment-form button[type="submit"]').prop('disabled', true).html('<span class="loader"></span>Updating...');
               },
               success: function(response) {
                  if (response.status === 'success') {
                     
                     commentBox.find('.comment-text').text(commentText);

                     
                     closeModal();

                     showNotification('Comment updated successfully');
                  } else {
                     showNotification(response.message, true);
                  }
               },
               error: function() {
                  showNotification('Error updating comment', true);
               },
               complete: function() {
                
                  $('#edit-comment-form button[type="submit"]').prop('disabled', false).text('Update Comment');
               }
            });
         });
      });
   </script>

</body>

</html>