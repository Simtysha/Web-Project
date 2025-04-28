<?php
include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

if(isset($_GET['get_id'])){
   $get_id = $_GET['get_id'];
}else{
   $get_id = '';
   header('location:playlist.php');
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Playlist Details</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <!-- jQuery -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="playlist-details">
   <h1 class="heading">Playlist details</h1>

   <div id="playlist-details"></div>
</section>

<section class="contents">
   <h1 class="heading">Playlist videos</h1>
   <div class="box-container" id="videos-container"></div>
</section>

<script>
$(document).ready(function() {
    var get_id = '<?php echo $get_id; ?>';
    var tutor_id = '<?php echo $tutor_id; ?>';

    // Fetch playlist details and videos using AJAX
    $.ajax({
        url: 'fetch_playlist.php',
        type: 'GET',
        data: { get_id: get_id, tutor_id: tutor_id },
        success: function(response) {
            var data = JSON.parse(response);
            // Render playlist details
            $('#playlist-details').html(data.playlistHTML);
            // Render videos
            $('#videos-container').html(data.videosHTML);
        },
        error: function() {
            alert('Failed to load playlist details.');
        }
    });
});
</script>

<script src="../js/admin_script.js"></script>

</body>
</html>
