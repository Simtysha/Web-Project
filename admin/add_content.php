<?php
include '../components/connect.php';

if (isset($_COOKIE['tutor_id'])) {
   $tutor_id = $_COOKIE['tutor_id'];
} else {
   echo json_encode(['status' => 'redirect', 'location' => 'login.php']);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $response = [];

   $id = unique_id();
   $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
   $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
   $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
   $playlist = filter_var($_POST['playlist'], FILTER_SANITIZE_STRING);

   $thumb = $_FILES['thumb']['name'];
   $thumb_ext = pathinfo($thumb, PATHINFO_EXTENSION);
   $rename_thumb = unique_id() . '.' . $thumb_ext;
   $thumb_size = $_FILES['thumb']['size'];
   $thumb_tmp_name = $_FILES['thumb']['tmp_name'];
   $thumb_folder = '../uploaded_files/' . $rename_thumb;

   $video = $_FILES['video']['name'];
   $video_ext = pathinfo($video, PATHINFO_EXTENSION);
   $rename_video = unique_id() . '.' . $video_ext;
   $video_tmp_name = $_FILES['video']['tmp_name'];
   $video_folder = '../uploaded_files/' . $rename_video;

   if ($thumb_size > 2000000) {
      $response = ['status' => 'error', 'message' => 'Image size is too large!'];
   } else {
      $add_content = $conn->prepare("INSERT INTO `content` (id, tutor_id, playlist_id, title, description, video, thumb, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $add_content->execute([$id, $tutor_id, $playlist, $title, $description, $rename_video, $rename_thumb, $status]);

      move_uploaded_file($thumb_tmp_name, $thumb_folder);
      move_uploaded_file($video_tmp_name, $video_folder);

      $response = ['status' => 'success', 'message' => 'New content uploaded!'];
   }

   echo json_encode($response);
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Add Content</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="video-form">
   <h1 class="heading">Upload content</h1>
   <form id="add-content-form" enctype="multipart/form-data">
      <p>Video status <span>*</span></p>
      <select name="status" class="box" required>
         <option value="" selected disabled>-- Select status</option>
         <option value="active">Active</option>
         <option value="deactive">Deactive</option>
      </select>

      <p>Video title <span>*</span></p>
      <input type="text" name="title" maxlength="100" required placeholder="Enter video title" class="box">

      <p>Video description <span>*</span></p>
      <textarea name="description" class="box" required placeholder="Write description" maxlength="1000" cols="30" rows="10"></textarea>

      <p>Video playlist <span>*</span></p>
      <select name="playlist" class="box" required>
         <option value="" disabled selected>--Select playlist</option>
         <?php
         $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ?");
         $select_playlists->execute([$tutor_id]);
         if ($select_playlists->rowCount() > 0) {
            while ($fetch_playlist = $select_playlists->fetch(PDO::FETCH_ASSOC)) {
               echo '<option value="' . $fetch_playlist['id'] . '">' . $fetch_playlist['title'] . '</option>';
            }
         } else {
            echo '<option value="" disabled>No playlist created yet!</option>';
         }
         ?>
      </select>

      <p>Select thumbnail <span>*</span></p>
      <input type="file" name="thumb" accept="image/*" required class="box">

      <p>Select video <span>*</span></p>
      <input type="file" name="video" accept="video/*" required class="box">

      <input type="submit" value="Upload video" class="btn">
   </form>

   <p id="response-msg" style="margin-top: 15px; font-weight: bold;"></p>
</section>

<script>
$('#add-content-form').on('submit', function(e) {
   e.preventDefault();
   const form = $(this)[0];
   const formData = new FormData(form);

   $.ajax({
      type: 'POST',
      url: '', // same file
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(response) {
         if (response.status === 'success') {
            $('#response-msg').css('color', 'green').text(response.message);
            $('#add-content-form')[0].reset();
         } else if (response.status === 'error') {
            $('#response-msg').css('color', 'red').text(response.message);
         } else if (response.status === 'redirect') {
            window.location.href = response.location;
         }
      },
      error: function() {
         $('#response-msg').css('color', 'red').text('Something went wrong.');
      }
   });
});
</script>

</body>
</html>
