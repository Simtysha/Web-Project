<?php

include 'components/connect.php';

if(!isset($_COOKIE['user_id'])){
   header('location:login.php');
   exit();
}

$user_id = $_COOKIE['user_id'];

$select_user = $conn->prepare("
   SELECT 
      u.*,
      COALESCE(l.like_count, 0) as total_likes,
      COALESCE(c.comment_count, 0) as total_comments,
      COALESCE(b.bookmark_count, 0) as total_bookmarked
   FROM users u
   LEFT JOIN (
      SELECT user_id, COUNT(*) as like_count 
      FROM likes 
      GROUP BY user_id
   ) l ON u.id = l.user_id
   LEFT JOIN (
      SELECT user_id, COUNT(*) as comment_count 
      FROM comments 
      GROUP BY user_id
   ) c ON u.id = c.user_id
   LEFT JOIN (
      SELECT user_id, COUNT(*) as bookmark_count 
      FROM bookmark 
      GROUP BY user_id
   ) b ON u.id = b.user_id
   WHERE u.id = ?
");

$select_user->execute([$user_id]);
$user_data = $select_user->fetch(PDO::FETCH_ASSOC);

if(!$user_data) {
   header('location:login.php');
   exit();
}


$recent_activity = $conn->prepare("
   (SELECT 'like' as type, content_id
    FROM likes 
    WHERE user_id = ? 
    LIMIT 3)
   UNION ALL
   (SELECT 'comment' as type, content_id
    FROM comments 
    WHERE user_id = ? 
    LIMIT 3)
   LIMIT 5
");
$recent_activity->execute([$user_id, $user_id]);
$activities = $recent_activity->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile - <?= htmlspecialchars($user_data['name']) ?></title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/profile.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="profile-container">
   <div class="profile-header">
      <img src="uploaded_files/<?= htmlspecialchars($user_data['image']) ?>" alt="Profile Image" class="profile-image">
      <div class="profile-info">
         <h1><?= htmlspecialchars($user_data['name']) ?></h1>
         <span class="role-badge">Student</span>
         <div style="margin-top: 1rem;">
            <a href="update.php" class="action-button">
               <i class="fas fa-edit"></i> Update Profile
            </a>
         </div>
      </div>
   </div>

   <div class="stats-grid">
      <div class="stat-card">
         <div class="stat-icon">
            <i class="fas fa-bookmark"></i>
         </div>
         <div class="stat-number"><?= $user_data['total_bookmarked'] ?></div>
         <div class="stat-label">Saved Playlists</div>
         <a href="bookmark.php" class="action-button">View Playlists</a>
      </div>

      <div class="stat-card">
         <div class="stat-icon">
            <i class="fas fa-heart"></i>
         </div>
         <div class="stat-number"><?= $user_data['total_likes'] ?></div>
         <div class="stat-label">Liked Tutorials</div>
         <a href="likes.php" class="action-button">View Liked</a>
      </div>

      <div class="stat-card">
         <div class="stat-icon">
            <i class="fas fa-comment"></i>
         </div>
         <div class="stat-number"><?= $user_data['total_comments'] ?></div>
         <div class="stat-label">Video Comments</div>
         <a href="comments.php" class="action-button">View Comments</a>
      </div>
   </div>

   <div class="recent-activity">
      <h2 class="activity-title">Recent Activity</h2>
      <?php if ($activities): ?>
         <?php foreach ($activities as $activity): ?>
            <div class="activity-item">
               <div class="activity-icon">
                  <i class="fas fa-<?= $activity['type'] === 'like' ? 'heart' : 'comment' ?>"></i>
               </div>
               <div class="activity-info">
                  <div>
                     <?= $activity['type'] === 'like' ? 'Liked a video' : 'Commented on a video' ?>
                  </div>
               </div>
            </div>
         <?php endforeach; ?>
      <?php else: ?>
         <p>No recent activity</p>
      <?php endif; ?>
   </div>
</div>

</body>
</html>
