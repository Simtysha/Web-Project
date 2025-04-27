<?php
include 'components/connect.php';

// Check login status once at the beginning
if (!isset($_COOKIE['user_id'])) {
   header('location:home.php');
   exit();
}


$user_id = $_COOKIE['user_id'];

// Handle AJAX requests
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

   // Fetch single comment for editing via AJAX
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

   // Return JSON response for AJAX requests
   header('Content-Type: application/json');
   echo json_encode($response);
   exit();
}

// Handle traditional form submissions (fallback for non-JS browsers)
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

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- jQuery CDN -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .edit-form {
         display: none;
         margin-top: 1rem;
      }

      .edit-form textarea {
         width: 100%;
         padding: 1rem;
         font-size: 1rem;
         resize: vertical;
         border: 1px solid #ddd;
         border-radius: 5px;
         margin-bottom: 1rem;
      }

      .comment-actions {
         display: flex;
         gap: 0.5rem;
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
         font-size: 1rem;
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

      #edit-modal {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.6);
         z-index: 999;
         justify-content: center;
         align-items: center;
      }

      .modal-content {
         background-color: white;
         padding: 2.5rem;
         border-radius: 12px;
         width: 90%;
         max-width: 600px;
         box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
         transform: scale(0.9);
         opacity: 0;
         transition: all 0.3s ease;
      }

      #edit-modal.active .modal-content {
         transform: scale(1);
         opacity: 1;
      }

      .modal-content h2 {
         margin-bottom: 1.5rem;
         color: #333;
         font-size: 1.5rem;
      }

      #edit-comment-text {
         width: 100%;
         padding: 1.5rem;
         font-size: 2rem;
         border: 2px solid #e0e0e0;
         border-radius: 8px;
         margin-bottom: 1.5rem;
         resize: vertical;
         min-height: 120px;
      }

      #edit-comment-text:focus {
         outline: none;
         border-color: #6b46c1;
      }

      .modal-buttons {
         display: flex;
         justify-content: flex-end;
         gap: 1rem;
      }

      .modal-buttons button {
         padding: 0.75rem 1.5rem;
         border-radius: 6px;
         font-size: 1rem;
         cursor: pointer;
         transition: all 0.3s ease;
      }

      .modal-buttons button:hover {
         transform: translateY(-2px);
      }

      .loader {
         display: inline-block;
         width: 20px;
         height: 20px;
         border: 3px solid rgba(255, 255, 255, .3);
         border-radius: 50%;
         border-top-color: #fff;
         animation: spin 1s ease-in-out infinite;
         margin-right: 10px;
      }

      @keyframes spin {
         to {
            transform: rotate(360deg);
         }
      }

      .comment-box {
         transition: all 0.3s ease;
      }

      .comment-box.fade-out {
         opacity: 0.5;
      }
   </style>
</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <!-- Notification area -->
   <div id="notification" class="notification"></div>

   <!-- Edit comment modal -->
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
         // Optimized query using JOIN to fetch all data at once
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

   <!-- Custom JS file link -->
   <script src="js/script.js"></script>

   <script>
      $(document).ready(function() {
         // Show notification function with improved animation
         function showNotification(message, isError = false) {
            const notification = $('#notification');
            notification.text(message).toggleClass('error', isError).fadeIn();
            setTimeout(() => notification.fadeOut(), 3000);
         }

         // Delete comment
         $(document).on('click', '.delete-btn', function() {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            const commentId = $(this).data('id');
            const commentBox = $('#comment-' + commentId);

            // Add visual feedback
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

                        // Check if no more comments exist
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

         // Open edit modal with improved animation
         $(document).on('click', '.edit-btn', function() {
            const commentId = $(this).data('id');
            const commentText = $('#comment-' + commentId + ' .comment-text').text();

            // Populate the edit form
            $('#edit-comment-id').val(commentId);
            $('#edit-comment-text').val(commentText).focus();

            // Show the edit modal with animation
            $('#edit-modal').css('display', 'flex').addClass('active');
         });

         // Close edit modal with animation
         function closeModal() {
            $('#edit-modal').removeClass('active');
            setTimeout(() => {
               $('#edit-modal').hide();
            }, 300);
         }

         $('#cancel-edit').click(closeModal);

         // Close modal when clicking outside with animation
         $(window).click(function(e) {
            if (e.target.id === 'edit-modal') {
               closeModal();
            }
         });

         // Submit edit form
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
                  // Disable submit button and show loading state
                  $('#edit-comment-form button[type="submit"]').prop('disabled', true).html('<span class="loader"></span>Updating...');
               },
               success: function(response) {
                  if (response.status === 'success') {
                     // Update the comment text in the DOM
                     commentBox.find('.comment-text').text(commentText);

                     // Hide the modal
                     closeModal();

                     // Show success message
                     showNotification('Comment updated successfully');
                  } else {
                     showNotification(response.message, true);
                  }
               },
               error: function() {
                  showNotification('Error updating comment', true);
               },
               complete: function() {
                  // Re-enable the submit button
                  $('#edit-comment-form button[type="submit"]').prop('disabled', false).text('Update Comment');
               }
            });
         });
      });
   </script>

</body>

</html>