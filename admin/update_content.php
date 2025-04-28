<?php
include '../components/connect.php';

// Redirect to login if tutor_id cookie is not set
if (!isset($_COOKIE['tutor_id'])) {
    header('Location: login.php');
    exit;
}

$tutor_id = $_COOKIE['tutor_id'];

// Note: unique_id() function is already defined in connect.php

// JSON Schema for content update
$schema = [
    'type' => 'object',
    'required' => ['id', 'title', 'description', 'status'],
    'properties' => [
        'id' => ['type' => 'string'], // String-based ID
        'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100],
        'description' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
        'status' => ['type' => 'string', 'enum' => ['active', 'deactive']],
        'playlist_id' => ['type' => 'string'], // Optional playlist ID
        'thumb' => ['type' => 'object'], // For base64 encoded thumbnail
        'video' => ['type' => 'object'] // For base64 encoded video
    ]
];

// Handle AJAX POST requests for update
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

    // Sanitize inputs after validation
    $video_id = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8');
    $playlist_id = isset($data['playlist_id']) ? htmlspecialchars($data['playlist_id'], ENT_QUOTES, 'UTF-8') : '';
    
    // Get old file information
    try {
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ? LIMIT 1");
        $select_content->execute([$video_id, $tutor_id]);
        
        if ($select_content->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Content not found or not owned by you']);
            exit;
        }
        
        $content = $select_content->fetch(PDO::FETCH_ASSOC);
        $old_thumb = $content['thumb'];
        $old_video = $content['video'];
        
        // Update basic content info
        $update_content = $conn->prepare("UPDATE `content` SET title = ?, description = ?, status = ? WHERE id = ? AND tutor_id = ?");
        $update_content->execute([$title, $description, $status, $video_id, $tutor_id]);
        
        // Update playlist if provided
        if (!empty($playlist_id)) {
            $update_playlist = $conn->prepare("UPDATE `content` SET playlist_id = ? WHERE id = ? AND tutor_id = ?");
            $update_playlist->execute([$playlist_id, $video_id, $tutor_id]);
        }
        
        $fileUpdates = [];
        
        // Process thumbnail if provided
        if (isset($data['thumb']) && !empty($data['thumb']['data'])) {
            $img_data = $data['thumb']['data'];
            $img_data = str_replace('data:image/jpeg;base64,', '', $img_data);
            $img_data = str_replace('data:image/png;base64,', '', $img_data);
            $img_data = str_replace(' ', '+', $img_data);
            
            $img_data = base64_decode($img_data);
            if ($img_data) {
                $rename_thumb = unique_id() . '.jpg';
                $thumb_folder = '../uploaded_files/' . $rename_thumb;
                
                if (file_put_contents($thumb_folder, $img_data)) {
                    $update_thumb = $conn->prepare("UPDATE `content` SET thumb = ? WHERE id = ? AND tutor_id = ?");
                    $update_thumb->execute([$rename_thumb, $video_id, $tutor_id]);
                    
                    if (!empty($old_thumb)) {
                        $old_file = '../uploaded_files/' . $old_thumb;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $fileUpdates[] = 'Thumbnail updated';
                }
            }
        }
        
        // Process video if provided
        if (isset($data['video']) && !empty($data['video']['data'])) {
            $video_data = $data['video']['data'];
            $video_data = str_replace('data:video/mp4;base64,', '', $video_data);
            $video_data = str_replace(' ', '+', $video_data);
            
            $video_data = base64_decode($video_data);
            if ($video_data) {
                $rename_video = unique_id() . '.mp4';
                $video_folder = '../uploaded_files/' . $rename_video;
                
                if (file_put_contents($video_folder, $video_data)) {
                    $update_video = $conn->prepare("UPDATE `content` SET video = ? WHERE id = ? AND tutor_id = ?");
                    $update_video->execute([$rename_video, $video_id, $tutor_id]);
                    
                    if (!empty($old_video)) {
                        $old_file = '../uploaded_files/' . $old_video;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    
                    $fileUpdates[] = 'Video updated';
                }
            }
        }
        
        // Check for changes
        if ($update_content->rowCount() > 0 || !empty($fileUpdates)) {
            $message = 'Content updated successfully';
            if (!empty($fileUpdates)) {
                $message .= ': ' . implode(', ', $fileUpdates);
            }
            echo json_encode(['status' => 'success', 'message' => $message]);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'No changes made']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

// Handle AJAX GET request to fetch content data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_id']) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    $get_id = htmlspecialchars($_GET['get_id'], ENT_QUOTES, 'UTF-8');
    
    if (empty($get_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid content ID']);
        exit;
    }

    try {
        // Get content data
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ?");
        $select_content->execute([$get_id, $tutor_id]);

        if ($select_content->rowCount() > 0) {
            $content = $select_content->fetch(PDO::FETCH_ASSOC);
            
            // Get available playlists for dropdown
            $select_playlists = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ?");
            $select_playlists->execute([$tutor_id]);
            $playlists = $select_playlists->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the response
            $response = [
                'status' => 'success',
                'data' => $content,
                'playlists' => $playlists,
                'file_paths' => [
                    'thumb' => '../uploaded_files/' . $content['thumb'],
                    'video' => '../uploaded_files/' . $content['video']
                ]
            ];
            
            echo json_encode($response);
        } else {
            // If not found, try with just the ID (for debugging)
            $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ?");
            $select_content->execute([$get_id]);
            
            if ($select_content->rowCount() > 0) {
                $content = $select_content->fetch(PDO::FETCH_ASSOC);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Content found but not associated with your account',
                    'debug' => [
                        'content_tutor_id' => $content['tutor_id'],
                        'your_tutor_id' => $tutor_id
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Content not found',
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

// Handle AJAX DELETE request to delete content
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    // Read the raw DELETE data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id']) || empty($data['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request format']);
        exit;
    }
    
    $delete_id = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
    
    try {
        // Verify ownership before deletion
        $check_ownership = $conn->prepare("SELECT * FROM `content` WHERE id = ? AND tutor_id = ? LIMIT 1");
        $check_ownership->execute([$delete_id, $tutor_id]);
        
        if ($check_ownership->rowCount() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Content not found or not owned by you']);
            exit;
        }
        
        // Get file paths
        $content = $check_ownership->fetch(PDO::FETCH_ASSOC);
        $thumb_path = $content['thumb'];
        $video_path = $content['video'];
        
        // Delete thumbnail file
        if (!empty($thumb_path)) {
            $thumb_file = '../uploaded_files/' . $thumb_path;
            if (file_exists($thumb_file)) {
                unlink($thumb_file);
            }
        }
        
        // Delete video file
        if (!empty($video_path)) {
            $video_file = '../uploaded_files/' . $video_path;
            if (file_exists($video_file)) {
                unlink($video_file);
            }
        }
        
        // Delete associated data
        $delete_likes = $conn->prepare("DELETE FROM `likes` WHERE content_id = ?");
        $delete_likes->execute([$delete_id]);
        
        $delete_comments = $conn->prepare("DELETE FROM `comments` WHERE content_id = ?");
        $delete_comments->execute([$delete_id]);
        
        // Delete the content record
        $delete_content = $conn->prepare("DELETE FROM `content` WHERE id = ? AND tutor_id = ?");
        $delete_content->execute([$delete_id, $tutor_id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Content deleted successfully']);
        
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Content</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>

<?php include '../components/admin_header.php'; ?>
   
<section class="video-form">
    <h1 class="heading">Update Content</h1>
    
    <form id="contentForm" enctype="multipart/form-data">
        <input type="hidden" name="id" id="content_id">
        
        <p>Content Status <span>*</span></p>
        <select name="status" id="status" class="box" required>
            <option value="active">Active</option>
            <option value="deactive">Deactive</option>
        </select>
        
        <p>Content Title <span>*</span></p>
        <input type="text" name="title" id="title" maxlength="100" required placeholder="Enter content title" class="box">
        
        <p>Content Description <span>*</span></p>
        <textarea name="description" id="description" class="box" required placeholder="Write description" maxlength="1000" cols="30" rows="10"></textarea>
        
        <p>Playlist</p>
        <select name="playlist_id" id="playlist_id" class="box">
            <option value="">--Select playlist</option>
            <!-- Playlists will be loaded here via JavaScript -->
        </select>
        
        <div id="thumb-preview-container">
            <p>Current Thumbnail</p>
            <img id="thumb-preview" src="" alt="Thumbnail" style="max-width: 300px; margin-bottom: 15px;">
        </div>
        
        <p>Update Thumbnail</p>
        <input type="file" name="thumb" id="thumb" accept="image/*" class="box">
        
        <div id="video-preview-container">
            <p>Current Video</p>
            <video id="video-preview" controls style="max-width: 100%; margin-bottom: 15px;"></video>
        </div>
        
        <p>Update Video</p>
        <input type="file" name="video" id="video" accept="video/*" class="box">
        
        <input type="submit" value="Update Content" class="btn">
        <button type="button" id="deleteBtn" class="delete-btn">Delete Content</button>
        <div id="response-message"></div>
    </form>
</section>

<script>
$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const getId = urlParams.get('get_id');

    if (!getId) {
        window.location.href = 'contents.php';
        return;
    }

    // Debug info
    console.log('Fetching content with ID:', getId);

    // AJAX Load Existing Data
    $.ajax({
        url: 'update_content.php',
        method: 'GET',
        dataType: 'json',
        data: { get_id: getId },
        success: function(response) {
            if (response.status === 'success') {
                // Fill form fields
                $('#content_id').val(response.data.id);
                $('#status').val(response.data.status);
                $('#title').val(response.data.title);
                $('#description').val(response.data.description);
                
                // Set thumbnail and video previews
                if (response.data.thumb) {
                    $('#thumb-preview').attr('src', response.file_paths.thumb);
                } else {
                    $('#thumb-preview-container').hide();
                }
                
                if (response.data.video) {
                    $('#video-preview').attr('src', response.file_paths.video);
                } else {
                    $('#video-preview-container').hide();
                }
                
                // Load playlists
                const playlists = response.playlists;
                playlists.forEach(function(playlist) {
                    const selected = (playlist.id === response.data.playlist_id) ? 'selected' : '';
                    $('#playlist_id').append(`<option value="${playlist.id}" ${selected}>${playlist.title}</option>`);
                });
                
                // If no playlists are available
                if (playlists.length === 0) {
                    $('#playlist_id').append('<option value="" disabled>No playlist created yet!</option>');
                }
            } else {
                alert("Error: " + response.message);
                window.location.href = 'contents.php';
            }
        },
        error: function(xhr, status, error) {
            alert("Failed to load content data. Status: " + status);
            window.location.href = 'contents.php';
        }
    });

    // Helper function to convert file to base64
    function getBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }

    // AJAX Submit Form
    $('#contentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic content data
        const contentData = {
            id: $('#content_id').val(),
            status: $('#status').val(),
            title: $('#title').val(),
            description: $('#description').val(),
            playlist_id: $('#playlist_id').val()
        };
        
        // Client-side validation
        if (!contentData.id || !contentData.title || !contentData.description || !contentData.status) {
            $('#response-message').html('<div class="error">Required fields are missing</div>');
            return;
        }
        
        // Show loading state
        $('#response-message').html('<div class="info">Processing your request...</div>');
        
        // Process files if selected
        const promises = [];
        
        if ($('#thumb')[0].files.length > 0) {
            const thumbPromise = getBase64($('#thumb')[0].files[0])
                .then(base64 => {
                    contentData.thumb = {
                        data: base64,
                        name: $('#thumb')[0].files[0].name
                    };
                });
            promises.push(thumbPromise);
        }
        
        if ($('#video')[0].files.length > 0) {
            const videoPromise = getBase64($('#video')[0].files[0])
                .then(base64 => {
                    contentData.video = {
                        data: base64,
                        name: $('#video')[0].files[0].name
                    };
                });
            promises.push(videoPromise);
        }
        
        // When all files are processed, send data
        Promise.all(promises)
            .then(() => {
                console.log('Submitting data (file info only):', {
                    ...contentData,
                    thumb: contentData.thumb ? { name: contentData.thumb.name } : undefined,
                    video: contentData.video ? { name: contentData.video.name } : undefined
                });
                
                $.ajax({
                    url: 'update_content.php',
                    method: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(contentData),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Submit response:', response);
                        if (response.status === 'success') {
                            setTimeout(() => {
                                alert(response.message);
                                window.location.href = 'contents.php';
                            }, 1000);
                        } else {
                            $('#response-message').html('<div class="error">' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Submit error:', error);
                        console.log('Submit status:', status);
                        console.log('Submit response:', xhr.responseText);
                        $('#response-message').html('<div class="error">Failed to update content. Status: ' + status + '</div>');
                    }
                });
            })
            .catch(error => {
                console.error('File processing error:', error);
                $('#response-message').html('<div class="error">Failed to process files: ' + error.message + '</div>');
            });
    });
    
    // Handle delete button
    $('#deleteBtn').on('click', function() {
        if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
            const contentId = $('#content_id').val();
            
            $.ajax({
                url: 'update_content.php',
                method: 'DELETE',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ id: contentId }),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    console.log('Delete response:', response);
                    if (response.status === 'success') {
                        alert(response.message);
                        window.location.href = 'contents.php';
                    } else {
                        $('#response-message').html('<div class="error">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete error:', error);
                    $('#response-message').html('<div class="error">Failed to delete content. Status: ' + status + '</div>');
                }
            });
        }
    });
});
</script>


</body>
</html>