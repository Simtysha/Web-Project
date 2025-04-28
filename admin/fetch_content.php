<?php
include '../components/connect.php';

if (isset($_GET['get_id']) && isset($_GET['tutor_id'])) {
   $get_id = $_GET['get_id'];
   $tutor_id = $_GET['tutor_id'];

   $contentHTML = '';
   $commentsHTML = '';

   // Fetch video content
   $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ?");
   $select_content->execute([$get_id, $tutor_id]);

   if ($select_content->rowCount() > 0) {
      $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);
      $video_id = $fetch_content['id'];

      // Count likes and comments
      $count_likes = $conn->prepare("SELECT * FROM `likes` WHERE tutor_id = ? AND content_id = ?");
      $count_likes->execute([$tutor_id, $video_id]);
      $total_likes = $count_likes->rowCount();

      $count_comments = $conn->prepare("SELECT * FROM `comments` WHERE tutor_id = ? AND content_id = ?");
      $count_comments->execute([$tutor_id, $video_id]);
      $total_comments = $count_comments->rowCount();

      // Build content HTML
      $contentHTML .= '<div class="container">';
      $contentHTML .= '<video src="../uploaded_files/' . $fetch_content['video'] . '" controls autoplay poster="../uploaded_files/' . $fetch_content['thumb'] . '" class="video"></video>';
      $contentHTML .= '<div class="date"><i class="fas fa-calendar"></i><span>' . $fetch_content['date'] . '</span></div>';
      $contentHTML .= '<h3 class="title">' . $fetch_content['title'] . '</h3>';
      $contentHTML .= '<div class="flex">';
      $contentHTML .= '<div><i class="fas fa-heart"></i><span>' . $total_likes . '</span></div>';
      $contentHTML .= '<div><i class="fas fa-comment"></i><span>' . $total_comments . '</span></div>';
      $contentHTML .= '</div>';
      $contentHTML .= '<div class="description">' . $fetch_content['description'] . '</div>';
      $contentHTML .= '<div class="flex-btn">';
      $contentHTML .= '<a href="update_content.php?get_id=' . $video_id . '" class="option-btn">Update</a>';
      $contentHTML .= '<form method="post">';
      $contentHTML .= '<input type="hidden" name="video_id" value="' . $video_id . '">';
      $contentHTML .= '<button type="submit" name="delete_video" class="delete-btn" onclick="return confirm(\'Delete this video?\');">Delete</button>';
      $contentHTML .= '</form></div></div>';

      // Fetch comments
      $select_comments = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ?");
      $select_comments->execute([$get_id]);

      if ($select_comments->rowCount() > 0) {
         while ($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)) {
            $select_commentor = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_commentor->execute([$fetch_comment['user_id']]);
            $fetch_commentor = $select_commentor->fetch(PDO::FETCH_ASSOC);

            $commentsHTML .= '<div class="box">';
            $commentsHTML .= '<div class="user">';
            $commentsHTML .= '<img src="../uploaded_files/' . $fetch_commentor['image'] . '" alt="">';
            $commentsHTML .= '<div>';
            $commentsHTML .= '<h3>' . $fetch_commentor['name'] . '</h3>';
            $commentsHTML .= '<span>' . $fetch_comment['date'] . '</span>';
            $commentsHTML .= '</div></div>';
            $commentsHTML .= '<p class="text">' . $fetch_comment['comment'] . '</p>';
            $commentsHTML .= '<form method="post" class="flex-btn">';
            $commentsHTML .= '<input type="hidden" name="comment_id" value="' . $fetch_comment['id'] . '">';
            $commentsHTML .= '<button type="submit" name="delete_comment" class="inline-delete-btn" onclick="return confirm(\'Delete this comment?\');">Delete Comment</button>';
            $commentsHTML .= '</form></div>';
         }
      } else {
         $commentsHTML .= '<p class="empty">No comments yet!</p>';
      }

   } else {
      $contentHTML = '<p class="empty">No content found!</p>';
   }

   // Return both content and comments as JSON
   echo json_encode([
      'contentHTML' => $contentHTML,
      'commentsHTML' => $commentsHTML
   ]);
}

?>
