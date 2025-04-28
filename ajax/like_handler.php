<?php
include '../components/connect.php';

header('Content-Type: application/json');



$likeRequestSchema = [
    'type' => 'object',
    'properties' => [
        'content_id' => ['type' => 'string', 'minLength' => 1],
        'action' => ['type' => 'string', 'enum' => ['like_content']]
    ],
    'required' => ['content_id', 'action']
];


function validateAgainstSchema($data, $schema) {
   
    foreach ($schema['required'] as $field) {
        if (!isset($data[$field])) {
            return false;
        }
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_COOKIE['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Please login first!']);
        exit;
    }

    $user_id = $_COOKIE['user_id'];
    
    
    if (!validateAgainstSchema($_POST, $likeRequestSchema)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
        exit;
    }

    $content_id = filter_var($_POST['content_id'], FILTER_SANITIZE_STRING);

    try {
        
        $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
        $select_content->execute([$content_id]);
        $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

        if (!$fetch_content) {
            echo json_encode(['status' => 'error', 'message' => 'Content not found']);
            exit;
        }

        $tutor_id = $fetch_content['tutor_id'];

      
        $select_likes = $conn->prepare("SELECT * FROM `likes` WHERE user_id = ? AND content_id = ?");
        $select_likes->execute([$user_id, $content_id]);

        if($select_likes->rowCount() > 0) {

            $remove_likes = $conn->prepare("DELETE FROM `likes` WHERE user_id = ? AND content_id = ?");
            $remove_likes->execute([$user_id, $content_id]);
            
      
            $new_likes = $conn->prepare("SELECT COUNT(*) as count FROM `likes` WHERE content_id = ?");
            $new_likes->execute([$content_id]);
            $likes_count = $new_likes->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Removed from likes!',
                'likes_count' => $likes_count
            ]);
        } else {
           
            $insert_likes = $conn->prepare("INSERT INTO `likes`(user_id, tutor_id, content_id) VALUES(?,?,?)");
            $insert_likes->execute([$user_id, $tutor_id, $content_id]);
            
       
            $new_likes = $conn->prepare("SELECT COUNT(*) as count FROM `likes` WHERE content_id = ?");
            $new_likes->execute([$content_id]);
            $likes_count = $new_likes->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Added to likes!',
                'likes_count' => $likes_count
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
