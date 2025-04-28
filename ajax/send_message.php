<?php
header('Content-Type: application/json');
include '../components/connect.php';


// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number (simple check for 7-10 digits)
function isValidPhone($number) {
    return preg_match('/^\d{7,10}$/', $number);
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request
    $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_STRING) : '';
    $number = isset($_POST['number']) ? filter_var($_POST['number'], FILTER_SANITIZE_STRING) : '';
    $msg = isset($_POST['msg']) ? filter_var($_POST['msg'], FILTER_SANITIZE_STRING) : '';
    
    // JSON Schema validation - check required fields and formats
    $errors = [];
    
    if (empty($name) || strlen($name) > 50) {
        $errors[] = "Name is required and must be less than 50 characters";
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = "A valid email address is required";
    }
    
    if (empty($number) || !isValidPhone($number)) {
        $errors[] = "A valid phone number is required (7-10 digits)";
    }
    
    if (empty($msg) || strlen($msg) > 1000) {
        $errors[] = "Message is required and must be less than 1000 characters";
    }
    
    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode([
            'status' => 'error',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Check if message already exists
    $select_contact = $conn->prepare("SELECT * FROM `contact` WHERE name = ? AND email = ? AND number = ? AND message = ?");
    $select_contact->execute([$name, $email, $number, $msg]);
    
    if ($select_contact->rowCount() > 0) {
        echo json_encode([
            'status' => 'warning',
            'message' => 'This message has already been sent!'
        ]);
    } else {
        // Insert into database
        try {
            $insert_message = $conn->prepare("INSERT INTO `contact`(name, email, number, message) VALUES(?,?,?,?)");
            $insert_message->execute([$name, $email, $number, $msg]);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Message sent successfully! We will get back to you soon.'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error occurred. Please try again later.'
            ]);
        }
    }
} else {
    // If not POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>