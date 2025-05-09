<?php
include '../components/connect.php';

if (!isset($_COOKIE['tutor_id'])) {
   header('location:login.php');
   exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Playlists</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="../css/admin_style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="playlists">
   <h1 class="heading">Added playlists</h1>
   <div class="box-container" id="playlist-container">
      <div class="box" style="text-align: center;">
         <h3 class="title" style="margin-bottom: .5rem;">Create new playlist</h3>
         <a href="add_playlist.php" class="btn">Add playlist</a>
      </div>
      <!-- Dynamic playlists will be appended here -->
   </div>
</section>

<script>
$(document).ready(function() {
   $.ajax({
      url: 'get_playlists.php',
      method: 'GET',
      dataType: 'json',
      success: function(playlists) {
         if (playlists.length === 0 || playlists.error) {
            $('#playlist-container').append('<p class="empty">No playlist added yet!</p>');
         } else {
            playlists.forEach(function(pl) {
               let statusColor = pl.status === 'active' ? 'limegreen' : 'red';
               let box = `
                  <div class="box">
                     <div class="flex">
                        <div><i class="fas fa-circle-dot" style="color:${statusColor}"></i><span style="color:${statusColor}">${pl.status}</span></div>
                        <div><i class="fas fa-calendar"></i><span>${pl.date}</span></div>
                     </div>
                     <div class="thumb">
                        <span>${pl.total_videos}</span>
                        <img src="../uploaded_files/${pl.thumb}" alt="">
                     </div>
                     <h3 class="title">${pl.title}</h3>
                     <p class="description">${pl.description.slice(0, 100)}</p>
                     <div class="flex-btn">
                        <a href="update_playlist.php?get_id=${pl.id}" class="option-btn">Update</a>
                        <button class="delete-btn" onclick="deletePlaylist('${pl.id}')">Delete</button>
                     </div>
                     <a href="view_playlist.php?get_id=${pl.id}" class="btn">View playlist</a>
                  </div>
               `;
               $('#playlist-container').append(box);
            });
         }
      },
      error: function() {
         $('#playlist-container').append('<p class="empty">Failed to load playlists.</p>');
      }
   });
   
   // Function to handle playlist deletion
   window.deletePlaylist = function(playlistId) {
      if (confirm('Are you sure you want to delete this playlist?')) {
         $.ajax({
            url: 'delete_playlist.php',
            method: 'POST',
            data: { playlist_id: playlistId },
            dataType: 'json',
            success: function(response) {
               if (response.success) {
                  alert('Playlist deleted successfully!');
                  location.reload(); // Refresh the page to show updated list
               } else {
                  alert(response.error || 'Failed to delete playlist.');
               }
            },
            error: function() {
               alert('An error occurred while trying to delete the playlist.');
            }
         });
      }
   };
});

</script>

<script src="../js/admin_script.js"></script>

</body>
</html>
