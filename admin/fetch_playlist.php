<?php
include '../components/connect.php';

if(isset($_GET['get_id']) && isset($_GET['tutor_id'])){
   $get_id = $_GET['get_id'];
   $tutor_id = $_GET['tutor_id'];

   // Fetch playlist details
   $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND tutor_id = ?");
   $select_playlist->execute([$get_id, $tutor_id]);
   $playlistHTML = '';
   if($select_playlist->rowCount() > 0){
      $fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);
      $playlist_id = $fetch_playlist['id'];
      $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
      $count_videos->execute([$playlist_id]);
      $total_videos = $count_videos->rowCount();

      // Generate playlist HTML
      $playlistHTML .= '<div class="row">';
      $playlistHTML .= '<div class="thumb"><span>' . $total_videos . '</span>';
      $playlistHTML .= '<img src="../uploaded_files/' . $fetch_playlist['thumb'] . '" alt=""></div>';
      $playlistHTML .= '<div class="details">';
      $playlistHTML .= '<h3 class="title">' . $fetch_playlist['title'] . '</h3>';
      $playlistHTML .= '<div class="date"><i class="fas fa-calendar"></i><span>' . $fetch_playlist['date'] . '</span></div>';
      $playlistHTML .= '<div class="description">' . $fetch_playlist['description'] . '</div>';
      $playlistHTML .= '<a href="update_playlist.php?get_id=' . $playlist_id . '" class="option-btn">Update playlist</a>';
      $playlistHTML .= '<form action="" method="post" class="flex-btn"><input type="hidden" name="playlist_id" value="' . $playlist_id . '">';
      $playlistHTML .= '<input type="submit" value="delete playlist" class="delete-btn" onclick="return confirm(\'delete this playlist?\');" name="delete"></form></div></div>';
   } else {
      $playlistHTML = '<p class="empty">No playlist found!</p>';
   }

   // Fetch videos for this playlist
   $videosHTML = '';
   $select_videos = $conn->prepare("SELECT * FROM `content` WHERE tutor_id = ? AND playlist_id = ?");
   $select_videos->execute([$tutor_id, $get_id]);
   if($select_videos->rowCount() > 0){
      while($fetch_video = $select_videos->fetch(PDO::FETCH_ASSOC)){
         $videosHTML .= '<div class="box">';
         $videosHTML .= '<div class="flex"><div><i class="fas fa-dot-circle" style="' . ($fetch_video['status'] == 'active' ? 'color:limegreen' : 'color:red') . '"></i><span style="' . ($fetch_video['status'] == 'active' ? 'color:limegreen' : 'color:red') . '">' . $fetch_video['status'] . '</span></div>';
         $videosHTML .= '<div><i class="fas fa-calendar"></i><span>' . $fetch_video['date'] . '</span></div></div>';
         $videosHTML .= '<img src="../uploaded_files/' . $fetch_video['thumb'] . '" class="thumb" alt="">';
         $videosHTML .= '<h3 class="title">' . $fetch_video['title'] . '</h3>';
         $videosHTML .= '<a href="update_content.php?get_id=' . $fetch_video['id'] . '" class="option-btn">Update</a>';
         $videosHTML .= '<form action="" method="post" class="flex-btn"><input type="hidden" name="video_id" value="' . $fetch_video['id'] . '">';
         $videosHTML .= '<input type="submit" value="delete" class="delete-btn" onclick="return confirm(\'delete this video?\');" name="delete_video"></form>';
         $videosHTML .= '<a href="view_content.php?get_id=' . $fetch_video['id'] . '" class="btn">Watch video</a></div>';
      }
   } else {
      $videosHTML = '<p class="empty" style="position:relative;right:700px;margin-bottom:50px;">No videos added yet! <a href="add_content.php" class="btn" style="margin-top: 1.5rem;">add videos</a></p>';
   }

   // Return playlist and videos as JSON
   echo json_encode(['playlistHTML' => $playlistHTML, 'videosHTML' => $videosHTML]);
}

?>
