<?php

include 'components/connect.php';

if(isset($_COOKIE['user_id'])){
   $user_id = $_COOKIE['user_id'];
}else{
   $user_id = '';
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:home.php');
}

if(isset($_POST['like_content'])){

   if($user_id != ''){

      $content_id = $_POST['content_id'];
      $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);

      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $select_content->execute([$content_id]);
      $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

      $tutor_id = $fetch_content['tutor_id'];

      $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
      $select_likes->execute([$user_id, $content_id]);

      if($select_likes->rowCount() > 0){
         $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
         $remove_likes->execute([$user_id, $content_id]);
         $message[] = 'Removed from likes!';
      }else{
         $insert_likes = $conn->prepare("INSERT INTO `likes`(user_id, tutor_id, content_id) VALUES(?,?,?)");
         $insert_likes->execute([$user_id, $tutor_id, $content_id]);
         $message[] = 'Added to likes!';
      }

   }else{
      $message[] = 'Please login first!';
   }

}

if(isset($_POST['add_comment'])){

   if($user_id != ''){

      $id = unique_id();
      $comment_box = $_POST['comment_box'];
      $comment_box = filter_var($comment_box, FILTER_SANITIZE_STRING);
      $content_id = $_POST['content_id'];
      $content_id = filter_var($content_id, FILTER_SANITIZE_STRING);

      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $select_content->execute([$content_id]);
      $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

      $tutor_id = $fetch_content['tutor_id'];

      if($select_content->rowCount() > 0){

         $select_comment = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? AND user_id = ? AND tutor_id = ? AND comment = ?");
         $select_comment->execute([$content_id, $user_id, $tutor_id, $comment_box]);

         if($select_comment->rowCount() > 0){
            $message[] = 'Comment already added!';
         }else{
            $insert_comment = $conn->prepare("INSERT INTO `comments`(id, content_id, user_id, tutor_id, comment) VALUES(?,?,?,?,?)");
            $insert_comment->execute([$id, $content_id, $user_id, $tutor_id, $comment_box]);
            $message[] = 'New comment added!';
         }

      }else{
         $message[] = 'Something went wrong!';
      }

   }else{
      $message[] = 'Please login first!';
   }

}

if(isset($_POST['delete_comment'])){

   $delete_id = $_POST['comment_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
   $verify_comment->execute([$delete_id]);

   if($verify_comment->rowCount() > 0){
      $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
      $delete_comment->execute([$delete_id]);
      $message[] = 'Comment deleted successfully!';
   }else{
      $message[] = 'Comment already deleted!';
   }

}

if(isset($_POST['update_now'])){

   $update_id = $_POST['update_id'];
   $update_id = filter_var($update_id, FILTER_SANITIZE_STRING);
   $update_box = $_POST['update_box'];
   $update_box = filter_var($update_box, FILTER_SANITIZE_STRING);

   $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND comment = ?");
   $verify_comment->execute([$update_id, $update_box]);

   if($verify_comment->rowCount() > 0){
      $message[] = 'Comment already added!';
   }else{
      $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
      $update_comment->execute([$update_box, $update_id]);
      $message[] = 'Comment edited successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Watch Video</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
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

   .like-btn.active i.fas.fa-heart {
      color: red;
   }

   .like-btn.active {
      background-color: #f0f0f0;
   }

   .like-btn i.fas.fa-heart {
      transition: color 0.3s ease;
   }
   </style>

   <!-- jQuery CDN -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- Notification area -->
<div id="notification" class="notification"></div>

<?php
   if(isset($_POST['edit_comment'])){
      $edit_id = $_POST['comment_id'];
      $edit_id = filter_var($edit_id, FILTER_SANITIZE_STRING);
      $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? LIMIT 1");
      $verify_comment->execute([$edit_id]);
      if($verify_comment->rowCount() > 0){
         $fetch_edit_comment = $verify_comment->fetch(PDO::FETCH_ASSOC);
?>
<section class="edit-comment">
   <h1 class="heading">Edit comment</h1>
   <form action="" method="post">
      <input type="hidden" name="update_id" value="<?= $fetch_edit_comment['id']; ?>">
      <textarea name="update_box" class="box" maxlength="1000" required placeholder="please enter your comment" cols="30" rows="10"><?= $fetch_edit_comment['comment']; ?></textarea>
      <div class="flex">
         <a href="watch_video.php?get_id=<?= $get_id; ?>" class="inline-option-btn">cancel edit</a>
         <input type="submit" value="update now" name="update_now" class="inline-btn">
      </div>
   </form>
</section>
<?php
   }else{
      $message[] = 'comment was not found!';
   }
}
?>

<!-- watch video section starts  -->

<section class="watch-video">

   <?php
      $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND status = ?");
      $select_content->execute([$get_id, 'active']);
      if($select_content->rowCount() > 0){
         while($fetch_content = $select_content->fetch(PDO::FETCH_ASSOC)){
            $content_id = $fetch_content['id'];

            $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE content_id = ?");
            $select_likes->execute([$content_id]);
            $total_likes = $select_likes->rowCount();  

            $verify_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
            $verify_likes->execute([$user_id, $content_id]);

            $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ? LIMIT 1");
            $select_tutor->execute([$fetch_content['tutor_id']]);
            $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
   ?>
   <div class="video-details">
      <video src="uploaded_files/<?= $fetch_content['video']; ?>" class="video" poster="uploaded_files/<?= $fetch_content['thumb']; ?>" controls autoplay></video>
      <h3 class="title"><?= $fetch_content['title']; ?></h3>
      <div class="info">
         <p><i class="fas fa-calendar"></i><span><?= $fetch_content['date']; ?></span></p>
         <p><i class="fas fa-heart"></i><span><?= $total_likes; ?> likes</span></p>
      </div>
      <div class="tutor">
         <img src="uploaded_files/<?= $fetch_tutor['image']; ?>" alt="">
         <div>
            <h3><?= $fetch_tutor['name']; ?></h3>
            <span><?= $fetch_tutor['profession']; ?></span>
         </div>
      </div>
      <form action="" method="post" class="flex">
    <input type="hidden" name="content_id" value="<?= $content_id; ?>">
    <a href="playlist.php?get_id=<?= $fetch_content['playlist_id']; ?>" class="inline-btn">view playlist</a>
    <?php
        if($verify_likes->rowCount() > 0){
    ?>
    <button type="submit" name="like_content" class="like-btn active" data-content-id="<?= $content_id; ?>">
        <i class="fas fa-heart"></i><span>Liked</span>
    </button>
    <?php
    }else{
    ?>
    <button type="submit" name="like_content" class="like-btn" data-content-id="<?= $content_id; ?>">
        <i class="far fa-heart"></i><span>Like</span>
    </button>
    <?php
        }
    ?>
</form>
      <div class="description"><p><?= $fetch_content['description']; ?></p></div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no videos added yet!</p>';
      }
   ?>

</section>


<!-- watch video section ends -->

<!-- comments section starts  -->

<section class="comments">

   <h1 class="heading">Add a comment</h1>

   <form action="" method="post" id="comment-form" class="add-comment">
      <input type="hidden" name="content_id" value="<?= $get_id; ?>">
      <textarea name="comment_box" required placeholder="write your comment..." maxlength="1000" cols="30" rows="10"></textarea>
      <input type="submit" value="add comment" name="add_comment" class="inline-btn">
   </form>

   <h1 class="heading">User Comments</h1>

   
   <div class="show-comments comments-container">
      <?php
         $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ?");
         $select_comments->execute([$get_id]);
         if($select_comments->rowCount() > 0){
            while($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)){   
               $select_commentor = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_commentor->execute([$fetch_comment['user_id']]);
               $fetch_commentor = $select_commentor->fetch(PDO::FETCH_ASSOC);
      ?>
      <div class="box" id="comment-<?= $fetch_comment['id']; ?>">
         <div class="user">
            <img src="uploaded_files/<?= $fetch_commentor['image']; ?>" alt="">
            <div>
               <h3><?= $fetch_commentor['name']; ?></h3>
               <span><?= $fetch_comment['date']; ?></span>
            </div>
         </div>
         <p class="text"><?= $fetch_comment['comment']; ?></p>
         <?php
            if($fetch_comment['user_id'] == $user_id){ 
         ?>
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="comment_id" value="<?= $fetch_comment['id']; ?>">
            <button type="submit" name="edit_comment" class="inline-option-btn edit-comment-btn" data-comment-id="<?= $fetch_comment['id']; ?>">Edit comment</button>
            <button type="submit" name="delete_comment" class="inline-delete-btn delete-comment" data-comment-id="<?= $fetch_comment['id']; ?>" onclick="return confirm('Delete this comment?');">Delete comment</button>
         </form>
         <?php
         }
         ?>
      </div>
      <?php
       }
      }else{
      ?>
      <p class="empty no-comments-message">No comments added yet!</p>
      <?php
         }
      ?>
      </div>
   
</section>

<script>
$(document).ready(function() {
    // Function to show notification
    function showNotification(message) {
        $('#notification').text(message).fadeIn();
        setTimeout(function() {
            $('#notification').fadeOut();
        }, 3000);
    }

    // Like functionality with AJAX
    $('.like-btn').on('click', function(e) {
        e.preventDefault();
        const contentId = $(this).data('content-id');
        const $button = $(this);
        const $icon = $button.find('i');
        const $text = $button.find('span');
        
        $.ajax({
            url: 'ajax/like_handler.php',
            type: 'POST',
            dataType: 'json',
            data: {
                content_id: contentId,
                action: 'like_content'
            },
            success: function(response) {
                if(response.status === 'success') {
                    const likesCount = response.likes_count;
                    $('.likes-count[data-content-id="' + contentId + '"]').text(likesCount);
                    $button.toggleClass('active');
                    
                    if($button.hasClass('active')) {
                        $icon.removeClass('far').addClass('fas');
                        $text.text('Liked');
                    } else {
                        $icon.removeClass('fas').addClass('far');
                        $text.text('Like');
                    }
                }
                showNotification(response.message);
            },
            error: function() {
                showNotification('Something went wrong!');
            }
        });
    });

    // Comment submission with AJAX
    $('#comment-form').on('submit', function(e) {
        e.preventDefault();
        const commentData = {
            content_id: $(this).find('[name="content_id"]').val(),
            comment: $(this).find('[name="comment_box"]').val()
        };

        $.ajax({
            url: 'ajax/comment_handler.php',
            type: 'POST',
            dataType: 'json',
            data: commentData,
            success: function(response) {
                if(response.status === 'success') {
                    // Remove "no comments" message if it exists
                    $('.no-comments-message').remove();
                    // Append new comment to the list
                    const newComment = response.comment_html;
                    $('.comments-container').prepend(newComment);
                    $('#comment-form')[0].reset();
                }
                showNotification(response.message);
            },
            error: function() {
                showNotification('Error submitting comment');
            }
        });
    });

    // Delete comment with AJAX
    $(document).on('click', '.delete-comment', function(e) {
        e.preventDefault();
        const commentId = $(this).data('comment-id');
        
        if(confirm('Delete this comment?')) {
            $.ajax({
                url: 'ajax/comment_handler.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    comment_id: commentId,
                    action: 'delete_comment'
                },
                success: function(response) {
                    if(response.status === 'success') {
                        $(`#comment-${commentId}`).remove();
                    }
                    showNotification(response.message);
                },
                error: function() {
                    showNotification('Error deleting comment');
                }
            });
        }
    });

    // Edit comment with AJAX
    $(document).on('click', '.edit-comment-btn', function(e) {
        e.preventDefault();
        const commentId = $(this).data('comment-id');
        const currentComment = $(this).closest('.comment-box').find('.comment-text').text();
        
        const editForm = `
            <form class="edit-comment-form">
                <textarea class="edit-comment-box">${currentComment}</textarea>
                <button type="submit" class="btn">Update</button>
                <button type="button" class="btn cancel-edit">Cancel</button>
            </form>
        `;
        
        $(this).closest('.comment-box').find('.comment-text').hide().after(editForm);
    });

    // Handle edit comment submission
    $(document).on('submit', '.edit-comment-form', function(e) {
        e.preventDefault();
        const commentBox = $(this).closest('.comment-box');
        const commentId = commentBox.find('.edit-comment-btn').data('comment-id');
        const updatedComment = $(this).find('.edit-comment-box').val();
        
        $.ajax({
            url: 'ajax/comment_handler.php',
            type: 'POST',
            dataType: 'json',
            data: {
                comment_id: commentId,
                comment: updatedComment,
                action: 'update_comment'
            },
            success: function(response) {
                if(response.status === 'success') {
                    commentBox.find('.comment-text').text(updatedComment).show();
                    commentBox.find('.edit-comment-form').remove();
                }
                showNotification(response.message);
            },
            error: function() {
                showNotification('Error updating comment');
            }
        });
    });

    // Cancel comment edit
    $(document).on('click', '.cancel-edit', function() {
        const commentBox = $(this).closest('.comment-box');
        commentBox.find('.comment-text').show();
        commentBox.find('.edit-comment-form').remove();
    });
});
</script>
   
</body>
</html>