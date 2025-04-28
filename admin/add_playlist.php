<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('Content-Type: application/json');
   echo json_encode(['error' => 'Unauthorized']);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {

   $id = unique_id();
   $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
   $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
   $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

   $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
   $ext = pathinfo($image, PATHINFO_EXTENSION);
   $rename = unique_id().'.'.$ext;
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = '../uploaded_files/'.$rename;

   $add_playlist = $conn->prepare("INSERT INTO `playlist`(id, tutor_id, title, description, thumb, status) VALUES(?,?,?,?,?,?)");
   $add_playlist->execute([$id, $tutor_id, $title, $description, $rename, $status]);

   move_uploaded_file($image_tmp_name, $image_folder);

   header('Content-Type: application/json');
   echo json_encode(['success' => true]);
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Add Playlist</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="playlist-form">
   <h1 class="heading">Create playlist</h1>

   <form id="playlistForm" enctype="multipart/form-data">
      <p>Playlist status <span>*</span></p>
      <select name="status" class="box" required>
         <option value="" selected disabled>-- Select status</option>
         <option value="active">Active</option>
         <option value="deactive">Deactive</option>
      </select>
      <p>Playlist title <span>*</span></p>
      <input type="text" name="title" maxlength="100" required placeholder="enter playlist title" class="box">
      <p>Playlist description <span>*</span></p>
      <textarea name="description" class="box" required placeholder="write description" maxlength="1000" cols="30" rows="10"></textarea>
      <p>Playlist thumbnail <span>*</span></p>
      <input type="file" name="image" accept="image/*" required class="box">
      <input type="submit" value="create playlist" class="btn">
   </form>
</section>

<script>
$('#playlistForm').on('submit', function(e) {
   e.preventDefault();

   const title = $('input[name="title"]').val().trim();
   const description = $('textarea[name="description"]').val().trim();
   const status = $('select[name="status"]').val();
   const image = $('input[name="image"]')[0].files[0];

   if (!status || !title || !description || !image) {
      alert('All fields are required.');
      return;
   }

   const formData = new FormData(this);

   $.ajax({
      url: 'add_playlist.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(response) {
         if (response.success) {
            alert('Playlist created!');
            window.location.href = "playlists.php";
         } else {
            alert('Something went wrong.');
         }
      },
      error: function(xhr) {
         alert('Error: ' + xhr.statusText);
      }
   });
});
</script>

<script src="../js/admin_script.js"></script>

</body>
</html>

