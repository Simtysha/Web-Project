<?php

include '../components/connect.php';

if(isset($_COOKIE['tutor_id'])){
   $tutor_id = $_COOKIE['tutor_id'];
}else{
   $tutor_id = '';
   header('location:login.php');
}

// Web Service API endpoint for AJAX search
if(isset($_GET['ajax_search']) && isset($_GET['keyword'])) {
    $search = $_GET['keyword'];
    $search = filter_var($search, FILTER_SANITIZE_STRING);
    $response = array('status' => 'success', 'data' => array('videos' => array(), 'playlists' => array()));
    
    // Get videos matching search
    $select_videos = $conn->prepare("SELECT * FROM `content` WHERE title LIKE ? AND tutor_id = ? ORDER BY date DESC");
    $select_videos->execute(['%'.$search.'%', $tutor_id]);
    if($select_videos->rowCount() > 0){
        while($fetch_videos = $select_videos->fetch(PDO::FETCH_ASSOC)){
            $video_id = $fetch_videos['id'];
            $response['data']['videos'][] = array(
                'id' => $video_id,
                'title' => $fetch_videos['title'],
                'thumb' => $fetch_videos['thumb'],
                'status' => $fetch_videos['status'],
                'date' => $fetch_videos['date']
            );
        }
    }
    
    // Get playlists matching search
    $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE title LIKE ? AND tutor_id = ? ORDER BY date DESC");
    $select_playlist->execute(['%'.$search.'%', $tutor_id]);
    if($select_playlist->rowCount() > 0){
        while($fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC)){
            $playlist_id = $fetch_playlist['id'];
            $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
            $count_videos->execute([$playlist_id]);
            $total_videos = $count_videos->rowCount();
            
            $response['data']['playlists'][] = array(
                'id' => $playlist_id,
                'title' => $fetch_playlist['title'],
                'thumb' => $fetch_playlist['thumb'],
                'description' => $fetch_playlist['description'],
                'status' => $fetch_playlist['status'],
                'date' => $fetch_playlist['date'],
                'total_videos' => $total_videos
            );
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

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

if(isset($_POST['delete_playlist'])){
   $delete_id = $_POST['playlist_id'];
   $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

   $verify_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND tutor_id = ? LIMIT 1");
   $verify_playlist->execute([$delete_id, $tutor_id]);

   if($verify_playlist->rowCount() > 0){
      $delete_playlist_thumb = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? LIMIT 1");
      $delete_playlist_thumb->execute([$delete_id]);
      $fetch_thumb = $delete_playlist_thumb->fetch(PDO::FETCH_ASSOC);
      unlink('../uploaded_files/'.$fetch_thumb['thumb']);
      $delete_bookmark = $conn->prepare("DELETE FROM `bookmark` WHERE playlist_id = ?");
      $delete_bookmark->execute([$delete_id]);
      $delete_playlist = $conn->prepare("DELETE FROM `playlist` WHERE id = ?");
      $delete_playlist->execute([$delete_id]);
      $message[] = 'playlist deleted!';
   }else{
      $message[] = 'playlist already deleted!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>

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

   <h1 class="heading">Contents</h1>

   <div class="box-container" id="video-container">

   <?php
      if(isset($_POST['search']) or isset($_POST['search_btn'])){
      $search = $_POST['search'];
      $select_videos = $conn->prepare("SELECT * FROM `content` WHERE title LIKE '%{$search}%' AND tutor_id = ? ORDER BY date DESC");
      $select_videos->execute([$tutor_id]);
      if($select_videos->rowCount() > 0){
         while($fecth_videos = $select_videos->fetch(PDO::FETCH_ASSOC)){ 
            $video_id = $fecth_videos['id'];
   ?>
      <div class="box">
         <div class="flex">
            <div><i class="fas fa-dot-circle" style="<?php if($fecth_videos['status'] == 'active'){echo 'color:limegreen'; }else{echo 'color:red';} ?>"></i><span style="<?php if($fecth_videos['status'] == 'active'){echo 'color:limegreen'; }else{echo 'color:red';} ?>"><?= $fecth_videos['status']; ?></span></div>
            <div><i class="fas fa-calendar"></i><span><?= $fecth_videos['date']; ?></span></div>
         </div>
         <img src="../uploaded_files/<?= $fecth_videos['thumb']; ?>" class="thumb" alt="">
         <h3 class="title"><?= $fecth_videos['title']; ?></h3>
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="video_id" value="<?= $video_id; ?>">
            <a href="update_content.php?get_id=<?= $video_id; ?>" class="option-btn">Update</a>
            <input type="submit" value="delete" class="delete-btn" onclick="return confirm('delete this video?');" name="delete_video">
         </form>
         <a href="view_content.php?get_id=<?= $video_id; ?>" class="btn">View content</a>
      </div>
   <?php
         }
      }else{
         echo '<p class="empty" style="position:relative;right:690px;">No contents founds!</p>';
      }
   }else{
      echo '<p class="empty">Please search something!</p>';
   }
   ?>

   </div>

</section>

<section class="playlists">

   <h1 class="heading">Playlists</h1>

   <div class="box-container" id="playlist-container">
   
      <?php
      if(isset($_POST['search']) or isset($_POST['search_btn'])){
         $search = $_POST['search'];
         $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE title LIKE '%{$search}%' AND tutor_id = ? ORDER BY date DESC");
         $select_playlist->execute([$tutor_id]);
         if($select_playlist->rowCount() > 0){
         while($fetch_playlist = $select_playlist->fetch(PDO::FETCH_ASSOC)){
            $playlist_id = $fetch_playlist['id'];
            $count_videos = $conn->prepare("SELECT * FROM `content` WHERE playlist_id = ?");
            $count_videos->execute([$playlist_id]);
            $total_videos = $count_videos->rowCount();
      ?>
      <div class="box">
         <div class="flex">
            <div><i class="fas fa-circle-dot" style="<?php if($fetch_playlist['status'] == 'active'){echo 'color:limegreen'; }else{echo 'color:red';} ?>"></i><span style="<?php if($fetch_playlist['status'] == 'active'){echo 'color:limegreen'; }else{echo 'color:red';} ?>"><?= $fetch_playlist['status']; ?></span></div>
            <div><i class="fas fa-calendar"></i><span><?= $fetch_playlist['date']; ?></span></div>
         </div>
         <div class="thumb">
            <span><?= $total_videos; ?></span>
            <img src="../uploaded_files/<?= $fetch_playlist['thumb']; ?>" alt="">
         </div>
         <h3 class="title"><?= $fetch_playlist['title']; ?></h3>
         <p class="description"><?= $fetch_playlist['description']; ?></p>
         <form action="" method="post" class="flex-btn">
            <input type="hidden" name="playlist_id" value="<?= $playlist_id; ?>">
            <a href="update_playlist.php?get_id=<?= $playlist_id; ?>" class="option-btn">Update</a>
            <input type="submit" value="delete_playlist" class="delete-btn" onclick="return confirm('delete this playlist?');" name="delete">
         </form>
         <a href="view_playlist.php?get_id=<?= $playlist_id; ?>" class="btn">View playlist</a>
      </div>
      <?php
         } 
      }else{
         echo '<p class="empty" style="position:relative;top:150px;right:885px;margin-bottom:50px;">No playlists found!</p>';      
      }
      }else{
         echo '<p class="empty" style="position:relative;>please search something!</p>';
      }
      ?>

   </div>

</section>

<script src="../js/admin_script.js"></script>

<script>
   document.querySelectorAll('.playlists .box-container .box .description').forEach(content => {
      if(content.innerHTML.length > 100) content.innerHTML = content.innerHTML.slice(0, 100);
   });

   // AJAX Search Implementation with jQuery
   $(document).ready(function(){
      const searchForm = $('form.search-form');
      const searchInput = $('input[name="search"]');
      
      if (searchForm.length && searchInput.length) {
         // Create real-time search with debounce
         let searchTimeout;
         
         searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            const keyword = $(this).val().trim();
            
            // Only search if there's at least 1 character
            if (keyword.length > 0) {
               searchTimeout = setTimeout(function() {
                  performAjaxSearch(keyword);
               }, 500); // Debounce 500ms
            } else {
               // Clear results when search is empty
               $('#video-container').html('<p class="empty">Please search something!</p>');
               $('#playlist-container').html('<p class="empty" style="position:relative;">please search something!</p>');
            }
         });
         
         // Prevent form submission to keep AJAX behavior
         searchForm.on('submit', function(e) {
            e.preventDefault();
            const keyword = searchInput.val().trim();
            if (keyword.length > 0) {
               performAjaxSearch(keyword);
            }
         });
      }
      
      function performAjaxSearch(keyword) {
         $.ajax({
            url: '?ajax_search=true&keyword=' + encodeURIComponent(keyword),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
               if(response.status === 'success') {
                  // Process video results
                  const videoContainer = $('#video-container');
                  if (response.data.videos.length > 0) {
                     let videoHTML = '';
                     $.each(response.data.videos, function(index, video) {
                        videoHTML += `
                           <div class="box">
                              <div class="flex">
                                 <div><i class="fas fa-dot-circle" style="${video.status === 'active' ? 'color:limegreen' : 'color:red'}"></i>
                                 <span style="${video.status === 'active' ? 'color:limegreen' : 'color:red'}">${video.status}</span></div>
                                 <div><i class="fas fa-calendar"></i><span>${video.date}</span></div>
                              </div>
                              <img src="../uploaded_files/${video.thumb}" class="thumb" alt="">
                              <h3 class="title">${video.title}</h3>
                              <form action="" method="post" class="flex-btn">
                                 <input type="hidden" name="video_id" value="${video.id}">
                                 <a href="update_content.php?get_id=${video.id}" class="option-btn">Update</a>
                                 <input type="submit" value="delete" class="delete-btn" onclick="return confirm('delete this video?');" name="delete_video">
                              </form>
                              <a href="view_content.php?get_id=${video.id}" class="btn">View content</a>
                           </div>
                        `;
                     });
                     videoContainer.html(videoHTML);
                  } else {
                     videoContainer.html('<p class="empty" style="position:relative;right:690px;">No contents founds!</p>');
                  }
                  
                  // Process playlist results
                  const playlistContainer = $('#playlist-container');
                  if (response.data.playlists.length > 0) {
                     let playlistHTML = '';
                     $.each(response.data.playlists, function(index, playlist) {
                        // Truncate description to 100 characters
                        let description = playlist.description;
                        if (description.length > 100) {
                           description = description.substring(0, 100);
                        }
                        
                        playlistHTML += `
                           <div class="box">
                              <div class="flex">
                                 <div><i class="fas fa-circle-dot" style="${playlist.status === 'active' ? 'color:limegreen' : 'color:red'}"></i>
                                 <span style="${playlist.status === 'active' ? 'color:limegreen' : 'color:red'}">${playlist.status}</span></div>
                                 <div><i class="fas fa-calendar"></i><span>${playlist.date}</span></div>
                              </div>
                              <div class="thumb">
                                 <span>${playlist.total_videos}</span>
                                 <img src="../uploaded_files/${playlist.thumb}" alt="">
                              </div>
                              <h3 class="title">${playlist.title}</h3>
                              <p class="description">${description}</p>
                              <form action="" method="post" class="flex-btn">
                                 <input type="hidden" name="playlist_id" value="${playlist.id}">
                                 <a href="update_playlist.php?get_id=${playlist.id}" class="option-btn">Update</a>
                                 <input type="submit" value="delete_playlist" class="delete-btn" onclick="return confirm('delete this playlist?');" name="delete">
                              </form>
                              <a href="view_playlist.php?get_id=${playlist.id}" class="btn">View playlist</a>
                           </div>
                        `;
                     });
                     playlistContainer.html(playlistHTML);
                  } else {
                     playlistContainer.html('<p class="empty" style="position:relative;top:150px;right:885px;margin-bottom:50px;">No playlists found!</p>');
                  }
               } else {
                  alert("Error: " + response.message);
               }
            },
            error: function(xhr, status, error){
               alert("Search failed. Status: " + status);
            }
         });
      }
   });
</script>

</body>
</html>