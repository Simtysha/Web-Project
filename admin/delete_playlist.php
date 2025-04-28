<?php
include '../components/connect.php';

// Check if the tutor is logged in by verifying the presence of a 'tutor_id' cookie
if (!isset($_COOKIE['tutor_id'])) {
   // Return JSON error if the tutor is not logged in
   header('Content-Type: application/json');
   echo json_encode(['error' => 'Unauthorized']);
   exit;
}

// Handle POST request for deleting a playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $tutor_id = $_COOKIE['tutor_id'];
   
   // Check if playlist_id exists in POST or GET parameters
   $delete_id = null;
   if (isset($_POST['playlist_id'])) {
      $delete_id = filter_var($_POST['playlist_id'], FILTER_SANITIZE_STRING);
   } elseif (isset($_GET['playlist_id'])) {
      $delete_id = filter_var($_GET['playlist_id'], FILTER_SANITIZE_STRING);
   }
   
   if (!$delete_id) {
      header('Content-Type: application/json');
      echo json_encode(['error' => 'No playlist ID provided']);
      exit;
   }

   // Verify if the playlist belongs to the logged-in tutor
   $verify = $conn->prepare("SELECT * FROM playlist WHERE id = ? AND tutor_id = ?");
   $verify->execute([$delete_id, $tutor_id]);

   if ($verify->rowCount() > 0) {
      // Fetch playlist details
      $fetch = $verify->fetch(PDO::FETCH_ASSOC);
      
      // Check if the playlist has a thumbnail and if the file exists
      if (!empty($fetch['thumb']) && file_exists('../uploaded_files/' . $fetch['thumb'])) {
         // Delete the thumbnail image from the server
         unlink('../uploaded_files/' . $fetch['thumb']);
      }

      // Delete related content from the database
      $conn->prepare("DELETE FROM content WHERE playlist_id = ?")->execute([$delete_id]);
      
      // Delete related bookmarks from the database
      $conn->prepare("DELETE FROM bookmark WHERE playlist_id = ?")->execute([$delete_id]);

      // Delete the playlist from the database
      $delete_playlist = $conn->prepare("DELETE FROM playlist WHERE id = ?");
      $delete_playlist->execute([$delete_id]);
      
      // Check if the deletion was successful
      if ($delete_playlist->rowCount() > 0) {
         header('Content-Type: application/json');
         echo json_encode(['success' => true, 'message' => 'Playlist deleted successfully']);
         exit;
      }
   } else {
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Playlist not found or you do not have permission to delete it']);
      exit;
   }
}

// If the request method is not POST or if something went wrong during deletion
header('Content-Type: application/json');
echo json_encode(['error' => 'Invalid request or deletion failed']);
exit;