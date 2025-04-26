<?php
include '../components/connect.php';

session_start();

if (isset($_COOKIE['tutor_id'])) {
    $tutor_id = $_COOKIE['tutor_id'];
} else {
    header('location:login.php');
    exit;
}

$fetch_profile = [];
$select_profile = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
$select_profile->execute([$tutor_id]);
if ($select_profile->rowCount() > 0) {
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
}

$select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ?");
$select_playlists->execute([$tutor_id]);
$total_playlists = $select_playlists->rowCount();

$select_contents = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ?");
$select_contents->execute([$tutor_id]);
$total_contents = $select_contents->rowCount();

$select_likes = $conn->prepare("SELECT * FROM `likes` WHERE tutor_id = ?");
$select_likes->execute([$tutor_id]);
$total_likes = $select_likes->rowCount();

$select_comments = $conn->prepare("SELECT * FROM `comments` WHERE tutor_id = ?");
$select_comments->execute([$tutor_id]);
$total_comments = $select_comments->rowCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="tutor-profile" style="min-height: calc(100vh - 19rem);"> 
   <h1 class="heading">Profile details</h1>

   <div class="details">
      <div class="tutor">
         <img src="../uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
         <h3 id="profile-name"><?= $fetch_profile['name']; ?></h3>
         <span id="profile-profession"><?= $fetch_profile['profession']; ?></span>

         <!-- Update button -->
         <button id="update-button" class="inline-btn" style="margin-top: 1rem;">Update Profile</button>
      </div>

      <div class="flex">
         <div class="box">
            <span><?= $total_playlists; ?></span>
            <p>Total playlists</p>
            <a href="playlists.php" class="btn">View playlists</a>
         </div>
         <div class="box">
            <span><?= $total_contents; ?></span>
            <p>Total videos</p>
            <a href="contents.php" class="btn">View contents</a>
         </div>
         <div class="box">
            <span><?= $total_likes; ?></span>
            <p>Total likes</p>
            <a href="contents.php" class="btn">View contents</a>
         </div>
         <div class="box">
            <span><?= $total_comments; ?></span>
            <p>Total comments</p>
            <a href="comments.php" class="btn">View comments</a>
         </div>
      </div>
   </div>
</section>

<script>
$(document).ready(function() {
   let currentName = '';
   let currentProfession = '';

   // Fetch profile data on page load
   $.get('profile.php?action=fetch', function(data) {
      $('#profile-name').text(data.name);
      $('#profile-profession').text(data.profession);
      currentName = data.name;
      currentProfession = data.profession;
   });

   // When the update button is clicked, redirect to update.php with current profile data
   $('#update-button').click(function() {
      // Redirect to the update page with name and profession as query parameters
      window.location.href = 'update.php?name=' + encodeURIComponent(currentName) + '&profession=' + encodeURIComponent(currentProfession);
   });
});
</script>

<script src="../js/admin_script.js"></script>

</body>
</html>
