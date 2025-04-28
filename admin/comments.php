<?php
include '../components/connect.php';

header('Content-Type: application/json');

if (isset($_GET['fetch'])) {
   // Return all comments as JSON (like a web service)
   if (isset($_COOKIE['tutor_id'])) {
      $tutor_id = $_COOKIE['tutor_id'];

      $select_comments = $conn->prepare("SELECT c.*, ct.title FROM comments c JOIN content ct ON c.content_id = ct.id WHERE c.tutor_id = ?");
      $select_comments->execute([$tutor_id]);

      $comments = $select_comments->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(['status' => 'success', 'comments' => $comments]);
      exit;
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
      exit;
   }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if (isset($_POST['delete_comment'])) {
      $delete_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);
      $verify_comment = $conn->prepare("SELECT * FROM comments WHERE id = ?");
      $verify_comment->execute([$delete_id]);

      if ($verify_comment->rowCount() > 0) {
         $delete_comment = $conn->prepare("DELETE FROM comments WHERE id = ?");
         $delete_comment->execute([$delete_id]);
         echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully!']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Comment already deleted!']);
      }
      exit;
   }
}

// If not AJAX, show HTML page
header('Content-Type: text/html');
if (!isset($_COOKIE['tutor_id'])) {
   header('location:login.php');
   exit;
}

$tutor_id = $_COOKIE['tutor_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Comments</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="comments">
   <h1 class="heading">User comments</h1>
   <div class="show-comments" id="comments-container">
      <p class="empty">Loading comments...</p>
   </div>
</section>

<script>
$(document).ready(function() {
   // Fetch comments via AJAX
   function loadComments() {
      $.get('comments.php?fetch=1', function(response) {
         let html = '';
         if (response.status === 'success' && response.comments.length > 0) {
            response.comments.forEach(function(comment) {
               html += `
               <div class="box" style="${comment.tutor_id === '<?= $tutor_id ?>' ? 'order:-1;' : ''}">
                  <div class="content">
                     <span>${comment.date}</span>
                     <p> - ${comment.title} - </p>
                     <a href="view_content.php?get_id=${comment.content_id}">view content</a>
                  </div>
                  <p class="text">${comment.comment}</p>
                  <form class="delete-form" data-id="${comment.id}">
                     <input type="hidden" name="comment_id" value="${comment.id}">
                     <button type="submit" name="delete_comment" class="inline-delete-btn">delete comment</button>
                  </form>
               </div>
               `;
            });
         } else {
            html = '<p class="empty">No comments added yet!</p>';
         }
         $('#comments-container').html(html);
      }, 'json');
   }

   // Initial load
   loadComments();

   // Handle delete using AJAX
   $(document).on('submit', '.delete-form', function(e) {
      e.preventDefault();
      if (!confirm('Delete this comment?')) return;

      const form = $(this);
      const commentID = form.find('input[name="comment_id"]').val();

      $.post('comments.php', { delete_comment: true, comment_id: commentID }, function(response) {
         alert(response.message);
         if (response.status === 'success') {
            loadComments();
         }
      }, 'json');
   });
});
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>

