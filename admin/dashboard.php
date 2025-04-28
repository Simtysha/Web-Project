<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">

   <h1 class="heading">Admin Dashboard</h1>

   <div class="box-container">

      <div class="box">
         <h3>Welcome!</h3>
         <p><?= isset($fetch_profile['name']) ? $fetch_profile['name'] : 'Admin'; ?></p>
         <a href="profile.php" class="btn">View profile</a>
      </div>

      <div class="box">
         <h3 id="stat-contents">Loading...</h3>
         <p>Total contents</p>
         <a href="add_content.php" class="btn">Add new content</a>
      </div>

      <div class="box">
         <h3 id="stat-playlists">Loading...</h3>
         <p>Total playlists</p>
         <a href="add_playlist.php" class="btn">Add new playlist</a>
      </div>

      <div class="box">
         <h3 id="stat-likes">Loading...</h3>
         <p>Total likes</p>
         <a href="contents.php" class="btn">View contents</a>
      </div>

      <div class="box">
         <h3 id="stat-comments">Loading...</h3>
         <p>Total comments</p>
         <a href="comments.php" class="btn">View comments</a>
      </div>

   </div>

</section>

<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- AJAX to Fetch Dashboard Data -->
<script>
$(document).ready(function(){
   $.ajax({
      url: 'dashboard_stats.php',
      method: 'GET',
      dataType: 'json',
      success: function(response){
         if(response.status === 'success'){
            $('#stat-contents').text(response.data.contents);
            $('#stat-playlists').text(response.data.playlists);
            $('#stat-likes').text(response.data.likes);
            $('#stat-comments').text(response.data.comments);
         } else {
            alert("Error: " + response.message);
         }
      },
      error: function(xhr, status, error){
         alert("Failed to load dashboard stats. Status: " + status);
      }
   });
});
</script>

<!-- Custom JS -->
<script src="../js/admin_script.js"></script>


</body>
</html>