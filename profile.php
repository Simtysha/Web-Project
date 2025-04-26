<?php

include 'components/connect.php';

if(!isset($_COOKIE['user_id'])){
   header('location:login.php');
   exit();
}

$user_id = $_COOKIE['user_id'];

// Fetch user data and counts in a more efficient way
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

// Get recent activity (simplified version without timestamp)
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

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
   .profile-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
   }

   .profile-header {
      background: linear-gradient(135deg, var(--main-color), #6c14d0);
      border-radius: 20px;
      padding: 3rem;
      color: white;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 3rem;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
   }

   .profile-image {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 5px solid white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      object-fit: cover;
   }

   .profile-info h1 {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
   }

   .role-badge {
      background: rgba(255, 255, 255, 0.2);
      padding: 0.5rem 1.5rem;
      border-radius: 20px;
      font-size: 1.4rem;
      display: inline-block;
      backdrop-filter: blur(5px);
   }

   .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
   }

   .stat-card {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
   }

   .stat-card:hover {
      transform: translateY(-5px);
   }

   .stat-icon {
      font-size: 3rem;
      color: var(--main-color);
      margin-bottom: 1rem;
   }

   .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: var(--black);
      margin-bottom: 0.5rem;
   }

   .stat-label {
      color: var(--light-color);
      font-size: 1.4rem;
   }

   .action-button {
      background: var(--main-color);
      color: white;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-size: 1.4rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: all 0.3s ease;
      display: inline-block;
      margin-top: 1rem;
      border: none;
      cursor: pointer;
   }

   .action-button:hover {
      background: var(--black);
      transform: translateY(-2px);
   }

   .recent-activity {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      margin-top: 2rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
   }

   .activity-title {
      font-size: 2rem;
      color: var(--black);
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--light-bg);
      padding-bottom: 1rem;
   }

   .activity-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem 0;
      border-bottom: 1px solid var(--light-bg);
   }

   .activity-icon {
      width: 40px;
      height: 40px;
      background: var(--light-bg);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--main-color);
   }

   .activity-info {
      flex: 1;
      font-size: 1.6rem;
   }

   @media (max-width: 768px) {
      .profile-header {
         flex-direction: column;
         text-align: center;
         padding: 2rem;
      }

      .profile-image {
         width: 120px;
         height: 120px;
      }

      .stats-grid {
         grid-template-columns: 1fr;
      }
   }
   </style>
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