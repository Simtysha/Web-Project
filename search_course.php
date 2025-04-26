<?php

include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
}

// Get the search query from GET parameter
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Courses</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- jQuery CDN -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <!-- courses section starts  -->

   <section class="courses">

      <h1 class="heading">search results</h1>

      <div class="box-container" id="search-results">
         <?php if (empty($search_query)): ?>
            <p class="empty">please search something!</p>
         <?php else: ?>
            <!-- Initial content will be replaced by AJAX -->
            <p class="empty">Loading results...</p>
         <?php endif; ?>
      </div>

   </section>

   <!-- courses section ends -->

   <script>
      $(document).ready(function() {
         // Function to perform the search
         function performSearch(query) {
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

         // Handle search input from the header
         $('.header .search-form input[type="text"]').on('input', function() {
            const query = $(this).val().trim();
            performSearch(query);
         });

         // Handle initial search if URL has search parameter
         const urlParams = new URLSearchParams(window.location.search);
         const searchQuery = urlParams.get('search');
         if (searchQuery) {
            $('.header .search-form input[type="text"]').val(searchQuery);
            performSearch(searchQuery);
         }
      });
   </script>

</body>

</html>