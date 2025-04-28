<?php
include '../components/connect.php';

header('Content-Type: application/json');


$commentSchemas = [
    'add' => [
        'type' => 'object',
        'properties' => [
            'content_id' => ['type' => 'string', 'minLength' => 1],
            'comment' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000]
        ],
        'required' => ['content_id', 'comment']
    ],
    'delete' => [
        'type' => 'object',
        'properties' => [
            'comment_id' => ['type' => 'string', 'minLength' => 1],
            'action' => ['type' => 'string', 'enum' => ['delete_comment']]
        ],
        'required' => ['comment_id', 'action']
    ],
    'update' => [
        'type' => 'object',
        'properties' => [
            'comment_id' => ['type' => 'string', 'minLength' => 1],
            'comment' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
            'action' => ['type' => 'string', 'enum' => ['update_comment']]
        ],
        'required' => ['comment_id', 'comment', 'action']
    ]
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


    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete_comment') {
            if (!validateAgainstSchema($_POST, $commentSchemas['delete'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
                exit;
            }

            $comment_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);
            
            try {
                $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND user_id = ?");
                $verify_comment->execute([$comment_id, $user_id]);

                if($verify_comment->rowCount() > 0) {
                    $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
                    $delete_comment->execute([$comment_id]);
                    echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Comment not found or unauthorized']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
            }
        } 
        elseif ($_POST['action'] === 'update_comment') {
            if (!validateAgainstSchema($_POST, $commentSchemas['update'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
                exit;
            }

            $comment_id = filter_var($_POST['comment_id'], FILTER_SANITIZE_STRING);
            $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);

            try {
                $verify_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ? AND user_id = ?");
                $verify_comment->execute([$comment_id, $user_id]);

                if($verify_comment->rowCount() > 0) {
                    $update_comment = $conn->prepare("UPDATE `comments` SET comment = ? WHERE id = ?");
                    $update_comment->execute([$comment, $comment_id]);
                    echo json_encode(['status' => 'success', 'message' => 'Comment updated successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Comment not found or unauthorized']);
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
            }
        }
    } 
    else {
      
        if (!validateAgainstSchema($_POST, $commentSchemas['add'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request data']);
            exit;
        }

        $content_id = filter_var($_POST['content_id'], FILTER_SANITIZE_STRING);
        $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
        $id = uniqid();

        try {
            $select_content = $conn->prepare("SELECT * FROM `content` WHERE id = ? LIMIT 1");
            $select_content->execute([$content_id]);
            $fetch_content = $select_content->fetch(PDO::FETCH_ASSOC);

            if (!$fetch_content) {
                echo json_encode(['status' => 'error', 'message' => 'Content not found']);
                exit;
            }

            $tutor_id = $fetch_content['tutor_id'];

     
            $select_comment = $conn->prepare("SELECT * FROM `comments` WHERE content_id = ? AND user_id = ? AND comment = ?");
            $select_comment->execute([$content_id, $user_id, $comment]);

            if($select_comment->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Comment already exists!']);
                exit;
            }

            $insert_comment = $conn->prepare("INSERT INTO `comments`(id, content_id, user_id, tutor_id, comment) VALUES(?,?,?,?,?)");
            $insert_comment->execute([$id, $content_id, $user_id, $tutor_id, $comment]);

            $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
            $select_user->execute([$user_id]);
            $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

    
            $comment_html = '
            <div class="box" id="comment-'.$id.'">
                <div class="user">
                    <img src="uploaded_files/'.$fetch_user['image'].'" alt="">
                    <div>
                        <h3>'.$fetch_user['name'].'</h3>
                        <span>'.date('Y-m-d').'</span>
                    </div>
                </div>
                <div class="comment-box">
                    <p class="comment-text">'.$comment.'</p>
                    <form action="" method="post" class="flex-btn">
                        <input type="hidden" name="comment_id" value="'.$id.'">
                        <button type="submit" name="edit_comment" class="inline-option-btn edit-comment-btn" data-comment-id="'.$id.'">Edit comment</button>
                        <button type="submit" name="delete_comment" class="inline-delete-btn delete-comment" data-comment-id="'.$id.'">Delete comment</button>
                    </form>
                </div>
            </div>';

            echo json_encode([
                'status' => 'success',
                'message' => 'Comment added successfully!',
                'comment_html' => $comment_html
            ]);

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
