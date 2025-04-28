<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>


<header class="header">

   <section class="flex">

   <a href="dashboard.php">
   <img src="../images/logo.png" alt="logo" class="logo-img">
   <span class="logo-text">Virtu-Learn </span>
   </a>

   <div class="navbar">
      <a href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
      <a href="playlists.php"><i class="fa-solid fa-bars-staggered"></i><span>Playlists</span></a>
      <a href="contents.php"><i class="fas fa-graduation-cap"></i><span>Contents</span></a>
      <a href="comments.php"><i class="fas fa-comment"></i><span>Comments</span></a>
   </div>

   <form action="search_page.php" method="post" class="search-form">
      <input type="text" name="search" placeholder="search here..." required maxlength="100">
      <button type="submit" class="fas fa-search" name="search_btn"></button>
   </form>

   <div class="icons">
      <div id="user-btn" class="fas fa-user"></div>
   </div>

   <div class="profile">
   <?php
      $select_profile = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
      $select_profile->execute([$tutor_id]);
      if($select_profile->rowCount() > 0){
         $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
   ?>
      <div class="profile-header">
         <img src="../uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
         <h3><?= $fetch_profile['name']; ?></h3>
         <span><?= $fetch_profile['profession']; ?></span>
      </div>
      <div class="profile-actions">
         <a href="profile.php" class="btn">View Profile</a>
         <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">Logout</a>
      </div>
   <?php
      }else{
   ?>
   <div class="flex-btn">
      <a href="login.php" class="option-btn">Login</a>
      <a href="register.php" class="option-btn">Register</a>
   </div>
   <?php
      }
   ?>
</div>
   </section>

</header>


