<?php
include '../components/connect.php';

// Redirect to login if tutor_id cookie is not set
if (!isset($_COOKIE['tutor_id'])) {
    header('Location: login.php');
    exit;
}

$tutor_id = $_COOKIE['tutor_id'];

// JSON Schema for playlist update
$schema = [
    'type' => 'object',
    'required' => ['id', 'title', 'description', 'status'],
    'properties' => [
        'id' => ['type' => 'string'], // String-based ID
        'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
        'description' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
        'status' => ['type' => 'string', 'enum' => ['active', 'deactive']]
    ]
];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    // Read the raw POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate JSON syntax
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON format']);
        exit;
    }

    // Validate JSON against schema
    $valid = true;
    $errorMsg = '';

    // Required fields check
    foreach ($schema['required'] as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $valid = false;
            $errorMsg = "Missing required field: $field";
            break;
        }
    }

    // Property validation
    if ($valid) {
        // Validate id is not empty
        if (empty($data['id'])) {
            $valid = false;
            $errorMsg = 'ID cannot be empty';
        }
        // Validate title length
        else if (strlen($data['title']) > $schema['properties']['title']['maxLength']) {
            $valid = false;
            $errorMsg = 'Title exceeds maximum length';
        }
        // Validate description length
        else if (strlen($data['description']) > $schema['properties']['description']['maxLength']) {
            $valid = false;
            $errorMsg = 'Description exceeds maximum length';
        }
        // Validate status
        else if (!in_array($data['status'], $schema['properties']['status']['enum'])) {
            $valid = false;
            $errorMsg = 'Invalid status value';
        }
    }

    if (!$valid) {
        echo json_encode(['status' => 'error', 'message' => $errorMsg]);
        exit;
    }

    // Sanitize inputs after validation - use string sanitization for the ID
    $id = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8');

    try {
        // Update playlist
        $update_playlist = $conn->prepare("UPDATE `playlist` SET title = ?, description = ?, status = ? WHERE id = ? AND tutor_id = ?");
        $update_playlist->execute([$title, $description, $status, $id, $tutor_id]);
        
        if ($update_playlist->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Playlist updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No changes made or playlist not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Handle AJAX GET request to fetch playlist data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    // Use string sanitization for ID instead of numeric filtering
    $get_id = htmlspecialchars($_GET['get_id'], ENT_QUOTES, 'UTF-8');
    
    if (empty($get_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid playlist ID']);
        exit;
    }

    try {
        // First try with both id and tutor_id
        $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ? AND tutor_id = ?");
        $select_playlist->execute([$get_id, $tutor_id]);

        if ($select_playlist->rowCount() > 0) {
            $playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $playlist]);
        } else {
            // If not found, try with just the ID (for debugging)
            $select_playlist = $conn->prepare("SELECT * FROM `playlist` WHERE id = ?");
            $select_playlist->execute([$get_id]);
            
            if ($select_playlist->rowCount() > 0) {
                $playlist = $select_playlist->fetch(PDO::FETCH_ASSOC);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Playlist found but not associated with your account',
                    'debug' => [
                        'playlist_tutor_id' => $playlist['tutor_id'],
                        'your_tutor_id' => $tutor_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Playlist not found',
                    'debug' => [
                        'id_searched' => $get_id
                    ]
                ]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

// For non-AJAX requests, display the HTML page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Playlist</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <?php include '../components/admin_header.php'; ?>

    <section class="playlist-form">
        <h1 class="heading">Update Playlist</h1>
        <form id="playlistForm">
            <input type="hidden" name="id" id="playlist_id">
            <p>Playlist Status <span>*</span></p>
            <select name="status" id="status" class="box" required>
                <option value="active">Active</option>
                <option value="deactive">Deactive</option>
            </select>
            <p>Playlist Title <span>*</span></p>
            <input type="text" name="title" id="title" maxlength="100" required placeholder="Enter playlist title" class="box">
            <p>Playlist Description <span>*</span></p>
            <textarea name="description" id="description" class="box" required placeholder="Write description" maxlength="1000" cols="30" rows="10"></textarea>
            <input type="submit" value="Update Playlist" class="btn">
            <div id="response-message"></div>
        </form>
    </section>

    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const getId = urlParams.get('get_id');

            if (!getId) {
                window.location.href = 'playlists.php';
                return;
            }

            // Debug info
            console.log('Fetching playlist with ID:', getId);

            // AJAX Load Existing Data
            $.ajax({
                url: 'update_playlist.php',
                method: 'GET',
                dataType: 'json',
                data: { get_id: getId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Response received:', response);
                    if (response.status === 'success') {
                        $('#playlist_id').val(response.data.id);
                        $('#status').val(response.data.status);
                        $('#title').val(response.data.title);
                        $('#description').val(response.data.description);
                    } else {
                        console.error('Error:', response.message);
                        alert(response.message);
                        if (response.debug) {
                            console.log('Debug info:', response.debug);
                        }
                        window.location.href = 'playlists.php';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                    alert('Failed to load playlist data. Status: ' + status);
                    window.location.href = 'playlists.php';
                }
            });

            
            // AJAX Submit Form
            $('#playlistForm').on('submit', function(e) {
                e.preventDefault();
                
                const playlistData = {
                    id: $('#playlist_id').val(),
                    status: $('#status').val(),
                    title: $('#title').val(),
                    description: $('#description').val()
                };
                
                console.log('Submitting data:', playlistData);
                
                // Client-side validation before sending
                if (!playlistData.id || !playlistData.title || !playlistData.description || !playlistData.status) {
                    $('#response-message').html('<div class="error">All fields are required</div>');
                    return;
                }
                
                $.ajax({
                    url: 'update_playlist.php',
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(playlistData),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Submit response:', response);
                        if (response.status === 'success') {
                            alert(response.message);
                            window.location.href = 'playlists.php';
                        } else {
                            $('#response-message').html('<div class="error">' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Submit error:', error);
                        console.log('Submit status:', status);
                        console.log('Submit response:', xhr.responseText);
                        $('#response-message').html('<div class="error">Failed to update playlist. Status: ' + status + '</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>