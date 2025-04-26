<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

// This section handles the delete action from POST requests (keeping original functionality)
if(isset($_POST['delete_video'])){
   $delete_id = $_POST['video_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);
   $verify_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
   $verify_video->execute([$delete_id]);
   if($verify_video->rowCount() > 0){
      $delete_video_thumb = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $delete_video_thumb->execute([$delete_id]);
      $fetch_thumb = $delete_video_thumb->fetch(PDO::FETCH_ASSOC);
      unlink('../uploaded_files/'.$fetch_thumb['thumb']);
      $delete_video = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
      $delete_video->execute([$delete_id]);
      $fetch_video = $delete_video->fetch(PDO::FETCH_ASSOC);
      unlink('../uploaded_files/'.$fetch_video['video']);
      $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
      $delete_likes->execute([$delete_id]);
      $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
      $delete_comments->execute([$delete_id]);
      $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ?");
      $delete_content->execute([$delete_id]);
      $message[] = 'video deleted!';
   }else{
      $message[] = 'video already deleted!';
   }
}

// Check if this is an AJAX request for content list
if(isset($_GET['action']) && $_GET['action'] == 'get_contents') {
    // This section will handle the AJAX request and return JSON
    header('Content-Type: application/json');
    
    try {
        $select_videos = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ? ORDER BY date DESC");
        $select_videos->execute([$tutor_id]);
        
        $contents = [];
        if($select_videos->rowCount() > 0){
            while($fetch_videos = $select_videos->fetch(PDO::FETCH_ASSOC)){ 
                $contents[] = [
                    'id' => $fetch_videos['id'],
                    'status' => $fetch_videos['status'],
                    'date' => $fetch_videos['date'],
                    'thumb' => $fetch_videos['thumb'],
                    'title' => $fetch_videos['title']
                ];
            }
            echo json_encode(['status' => 'success', 'data' => $contents]);
        } else {
            echo json_encode(['status' => 'empty', 'message' => 'No contents added yet!']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    
    exit(); // Stop execution after sending JSON response
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contents</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   
   <!-- jQuery CDN -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="contents">

   <h1 class="heading">Your contents</h1>
   
   <?php
   if(isset($message)){
      foreach($message as $msg){
         echo '<div class="message">'.$msg.'</div>';
      }
   }
   ?>

   <div class="box-container" id="content-container">
      <!-- AJAX will load content here -->
      <div class="box loading-state" style="text-align: center;">
         <h3>Loading contents...</h3>
      </div>
   </div>

</section>

<script>
$(document).ready(function(){
   // Load contents via AJAX
   loadContents();
   
   // Function to load contents
   function loadContents() {
      $.ajax({
         url: 'contents.php?action=get_contents',
         method: 'GET',
         dataType: 'json',
         success: function(response){
            // Clear the loading state
            $('#content-container').empty();
            
            // Add the "Create new content" box
            $('#content-container').append(`
               <div class="box" style="text-align: center;">
                  <h3 class="title" style="margin-bottom: .5rem;">Create new content</h3>
                  <a href="add_content.php" class="btn">Add content</a>
               </div>
            `);
            
            if(response.status === 'success'){
               // Loop through the contents and add them to the container
               $.each(response.data, function(index, content){
                  let statusColor = content.status === 'active' ? 'limegreen' : 'red';
                  
                  $('#content-container').append(`
                     <div class="box">
                        <div class="flex">
                           <div><i class="fas fa-dot-circle" style="color:${statusColor}"></i><span style="color:${statusColor}">${content.status}</span></div>
                           <div><i class="fas fa-calendar"></i><span>${content.date}</span></div>
                        </div>
                        <img src="../uploaded_files/${content.thumb}" class="thumb" alt="">
                        <h3 class="title">${content.title}</h3>
                        <form action="" method="post" class="flex-btn">
                           <input type="hidden" name="video_id" value="${content.id}">
                           <a href="update_content.php?get_id=${content.id}" class="option-btn">Update</a>
                           <input type="submit" value="delete" class="delete-btn" onclick="return confirm('delete this video?');" name="delete_video">
                        </form>
                        <a href="view_content.php?get_id=${content.id}" class="btn">View content</a>
                     </div>
                  `);
               });
            } else if(response.status === 'empty') {
               $('#content-container').append('<p class="empty">No contents added yet!</p>');
            } else {
               alert("Error: " + response.message);
            }
         },
         error: function(xhr, status, error){
            $('#content-container').empty();
            $('#content-container').append(`
               <div class="box" style="text-align: center;">
                  <h3 class="title" style="margin-bottom: .5rem;">Create new content</h3>
                  <a href="add_content.php" class="btn">Add content</a>
               </div>
               <p class="empty">Failed to load contents. Error: ${status}</p>
            `);
         }
      });
   }
   
   // Handle delete via AJAX (but keeping form post method for compatibility)
   $(document).on('submit', 'form', function(e){
      // The original POST form is still used to keep the delete functionality the same
      // After submission, we'll reload the contents
      setTimeout(function(){
         loadContents();
      }, 1000); // Add a small delay to ensure server-side processing completes
   });
});
</script>

<script src="../js/admin_script.js"></script>

</body>
</html>