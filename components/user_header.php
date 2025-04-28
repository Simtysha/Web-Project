<?php
// Only display messages in the header if the display_in_header flag is true
// Since we're displaying messages in the main content, we'll skip displaying them here
if (isset($message) && isset($display_in_header) && $display_in_header === true) {
   foreach ($message as $message) {
      echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>


<header class="header">

   <section class="flex">

      <a href="home.php">
         <img src="images/logo.png" alt="logo" class="logo-img">
         <span class="logo-text">Virtu-Learn</span>
      </a>

      <div class="navbar">
         <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
         <a href="about.php"><i class="fas fa-question"></i><span>About Us</span></a>
         <a href="courses.php"><i class="fas fa-graduation-cap"></i><span>Courses</span></a>
         <a href="teachers.php"><i class="fas fa-chalkboard-user"></i><span>Teachers</span></a>
         <a href="contact.php"><i class="fas fa-headset"></i><span>Contact us</span></a>
      </div>

      <form action="search_course.php" method="get" class="search-form" id="search-form">
         <input type="text" name="search" id="search-input" placeholder="search courses..." required maxlength="100">
         <button type="submit" class="fas fa-search"></button>
      </form>

      <div class="icons">
         <div id="user-btn" class="fas fa-user"></div>
      </div>

<div class="profile">
   <?php
      $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
      $select_profile->execute([$user_id]);
      if($select_profile->rowCount() > 0){
      $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
   ?>
   <div class="profile-header">
      <img src="uploaded_files/<?= $fetch_profile['image']; ?>" alt="">
      <h3><?= $fetch_profile['name']; ?></h3>
      <span>Student</span>
   </div>
   <div class="profile-actions">
      <a href="profile.php" class="btn">View Profile</a>
      <a href="components/user_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">Logout</a>
   </div>
   <?php
      }else{
   ?>
   <div class="profile-header">
      <h3>Welcome</h3>
      <span>Guest User</span>
   </div>
   <div class="profile-actions">
      <a href="login.php" class="option-btn">Login</a>
      <a href="register.php" class="option-btn">Register</a>
   </div>
   <?php
      }
   ?>
</div>

   </section>

</header>

<!-- header section ends -->

<script>
   $(document).ready(function() {
      const searchForm = $('#search-form');
      const searchInput = $('#search-input');

      // Handle form submission
      searchForm.on('submit', function(e) {
         const searchQuery = searchInput.val().trim();
         if (searchQuery.length > 0) {
            // Allow form to submit normally to search_course.php
            return true;
         }
         e.preventDefault();
         return false;
      });

      // If we're on search_course.php, handle real-time search
      if (window.location.pathname.includes('search_course.php')) {
         searchInput.on('input', function() {
            const query = $(this).val().trim();
            performSearch(query);
         });
      }

      // Update URL when searching without reloading the page
      function performSearch(query) {
         if (window.location.pathname.includes('search_course.php')) {
            // Update URL without reloading
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('search', query);
            window.history.pushState({
               path: newUrl.href
            }, '', newUrl.href);

            // Perform AJAX search
            if (query.length > 0) {
               $.ajax({
                  url: 'search_ajax.php',
                  method: 'POST',
                  data: {
                     search_query: query
                  },
                  success: function(response) {
                     $('#search-results').html(response);
                  },
                  error: function() {
                     $('#search-results').html('<p class="empty">Error occurred while searching!</p>');
                  }
               });
            } else {
               $('#search-results').html('<p class="empty">please search something!</p>');
            }
         }
      }

      // Handle initial search value if on search page
      if (window.location.pathname.includes('search_course.php')) {
         const urlParams = new URLSearchParams(window.location.search);
         const searchQuery = urlParams.get('search');
         if (searchQuery) {
            searchInput.val(searchQuery); 
            performSearch(searchQuery);
         }
      }
   });
</script>