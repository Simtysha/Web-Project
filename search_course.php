<?php

include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
   $user_id = $_COOKIE['user_id'];
} else {
   $user_id = '';
}

$search_query = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Courses</title>


   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">


   <link rel="stylesheet" href="css/style.css">


   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

</head>

<body>

   <?php include 'components/user_header.php'; ?>


   <section class="courses">

      <h1 class="heading">search results</h1>

      <div class="box-container" id="search-results">
         <?php if (empty($search_query)): ?>
            <p class="empty">please search something!</p>
         <?php else: ?>

            <p class="empty">Loading results...</p>
         <?php endif; ?>
      </div>

   </section>

   <div id="login-modal" class="modal">
      <div class="modal-content">
         <div class="modal-header">
            <h3><i class="fas fa-exclamation-circle"></i> Login Required</h3>
            <span class="close-modal">&times;</span>
         </div>
         <div class="modal-body">
            <p>Log in to view playlists</p>
            <div class="modal-buttons">
               <a href="login.php" class="btn">Login</a>
               <a href="register.php" class="btn">Register</a>
            </div>
         </div>
      </div>
   </div>

   <script>
      $(document).ready(function() {

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

                     addViewPlaylistHandlers();
                  },
                  error: function() {
                     $('#search-results').html('<p class="empty">Error occurred while searching!</p>');
                  }
               });
            } else {
               $('#search-results').html('<p class="empty">please search something!</p>');
            }
         }

         function addViewPlaylistHandlers() {
            $('.view-playlist-btn').on('click', function(e) {
               e.preventDefault();
               $('#login-modal').css('display', 'block');
            });
         }

         $('.header .search-form input[type="text"]').on('input', function() {
            const query = $(this).val().trim();
            performSearch(query);
         });

         const urlParams = new URLSearchParams(window.location.search);
         const searchQuery = urlParams.get('search');
         if (searchQuery) {
            $('.header .search-form input[type="text"]').val(searchQuery);
            performSearch(searchQuery);
         }

         $('.close-modal').on('click', function() {
            $('#login-modal').css('display', 'none');
         });

         $(window).on('click', function(event) {
            if (event.target == document.getElementById('login-modal')) {
               $('#login-modal').css('display', 'none');
            }
         });
      });
   </script>

   <style>
      .modal {
         display: none;
         position: fixed;
         z-index: 1000;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0,0,0,0.5);
         overflow: auto;
      }

      .modal-content {
         background-color: #fff;
         margin: 15% auto;
         width: 90%;
         max-width: 500px;
         border-radius: 10px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.3);
         animation: modalFadeIn 0.3s;
      }

      @keyframes modalFadeIn {
         from {opacity: 0; transform: translateY(-20px);}
         to {opacity: 1; transform: translateY(0);}
      }

      .modal-header {
         padding: 15px 20px;
         background-color: rgb(41, 3, 64);
         color: white;
         border-radius: 10px 10px 0 0;
         display: flex;
         justify-content: space-between;
         align-items: center;
      }

      .modal-header h3 {
         margin: 0;
         font-size: 18px;
         display: flex;
         align-items: center;
      }

      .modal-header h3 i {
         margin-right: 10px;
      }

      .close-modal {
         color: white;
         font-size: 28px;
         font-weight: bold;
         cursor: pointer;
      }

      .modal-body {
         padding: 20px;
         text-align: center;
      }

      .modal-body p {
         margin-bottom: 20px;
         font-size: 16px;
      }

      .modal-buttons {
         display: flex;
         justify-content: center;
         gap: 15px;
         margin-top: 15px;
      }

      .modal-buttons .btn {
         padding: 10px 20px;
         background-color: rgb(41, 3, 64);
         color: white;
         text-decoration: none;
         border-radius: 5px;
         font-weight: 500;
         transition: background-color 0.3s;
      }

      .modal-buttons .btn:hover {
         background-color: #dfbbf2;
      }

      .courses .box .lessons {
         color: rgb(255, 255, 255);
         font-size: 12px;
         margin-left: 70px;
         margin-top: 30px;
      }

      .course-footer .lessons {
         background-color: rgb(41, 3, 64);
         padding: 5px 10px;
         border-radius: 20px;
         font-size: 12px;
         font-weight: 500;
      }

.login-required-modal,
.modal-content,
.modal-dialog,
.modal-box,
.modal-wrapper,
[class*="modal"] {
    border-radius: 0 !important;
}


.modal-content .btn,
.login-btn,
.register-btn,
button[class*="login"],
button[class*="register"] {
    border-radius: 0 !important;
}

   </style>

</body>

</html>