<!-- <?php
// include '../components/connect.php';

// // Check if the tutor is logged in by verifying the presence of a 'tutor_id' cookie
// if (!isset($_COOKIE['tutor_id'])) {
//    // Redirect to login page if the tutor is not logged in
//    header('location:login.php');
//    exit;
// }

// // Handle POST request for deleting a playlist

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['playlist_id'])) {
//    // Sanitize the playlist ID received in the POST request
//    $tutor_id = $_COOKIE['tutor_id'];
//    $delete_id = filter_var($_POST['playlist_id'], FILTER_SANITIZE_STRING);

//    // Verify if the playlist belongs to the logged-in tutor
//    $verify = $conn->prepare("SELECT * FROM playlist WHERE id = ? AND tutor_id = ?");
//    $verify->execute([$delete_id, $tutor_id]);

//    if ($verify->rowCount() > 0) {
//       // Fetch playlist details
//       $fetch = $verify->fetch(PDO::FETCH_ASSOC);
      
//       // Check if the playlist has a thumbnail and if the file exists
//       if (!empty($fetch['thumb']) && file_exists('../uploaded_files/' . $fetch['thumb'])) {
//          // Delete the thumbnail image from the server
//          unlink('../uploaded_files/' . $fetch['thumb']);
//       }

//       // Delete related bookmarks from the database
//       $conn->prepare("DELETE FROM bookmark WHERE playlist_id = ?")->execute([$delete_id]);

//       // Delete the playlist from the database
//       $conn->prepare("DELETE FROM playlist WHERE id = ?")->execute([$delete_id]);
//    }
// }

// // Redirect to the playlists page after deleting the playlist
// header('Location: playlists.php');
// exit;
?> -->
