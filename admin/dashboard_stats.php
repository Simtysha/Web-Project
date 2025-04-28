<?php
// Include the connection file
require_once '../components/connect.php';

// Include the SimpleRest class
require_once 'SimpleREST.php';

class DashboardStatsRestHandler extends SimpleREST {
    
    private $conn;
    
    // Constructor to set the database connection
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    // Method to handle GET requests for dashboard stats
    public function getDashboardStats() {
        // Verify authentication
        if(isset($_COOKIE['tutor_id'])){
            $tutor_id = $_COOKIE['tutor_id'];
        } else {
            // Set 401 Unauthorized status code
            $this->setHttpHeaders('application/json', 401);
            $errorResponse = array('error' => 'Authentication required');
            echo json_encode($errorResponse);
            exit;
        }
        
        $stats = array();
        
        try {
            // Query for contents count
            $contentsQuery = "SELECT COUNT(*) FROM `content` WHERE tutor_id = ?";
            $contentsStmt = $this->conn->prepare($contentsQuery);
            $contentsStmt->execute([$tutor_id]);
            $stats['contents'] = $contentsStmt->fetchColumn();
            
            // Query for playlists count
            $playlistsQuery = "SELECT COUNT(*) FROM `playlist` WHERE tutor_id = ?";
            $playlistsStmt = $this->conn->prepare($playlistsQuery);
            $playlistsStmt->execute([$tutor_id]);
            $stats['playlists'] = $playlistsStmt->fetchColumn();
            
            // Query for likes count
            $likesQuery = "SELECT COUNT(*) FROM `likes` WHERE tutor_id = ?";
            $likesStmt = $this->conn->prepare($likesQuery);
            $likesStmt->execute([$tutor_id]);
            $stats['likes'] = $likesStmt->fetchColumn();
            
            // Query for comments count
            $commentsQuery = "SELECT COUNT(*) FROM `comments` WHERE tutor_id = ?";
            $commentsStmt = $this->conn->prepare($commentsQuery);
            $commentsStmt->execute([$tutor_id]);
            $stats['comments'] = $commentsStmt->fetchColumn();
            
            // Set 200 OK status code
            $this->setHttpHeaders('application/json', 200);
            
            // Return successful response
            $response = array(
                'status' => 'success',
                'data' => $stats
            );
            echo json_encode($response);
            
        } catch (PDOException $e) {
            // Set 500 Internal Server Error status code
            $this->setHttpHeaders('application/json', 500);
            
            // Return error response
            $errorResponse = array(
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            );
            echo json_encode($errorResponse);
        }
    }
}

// Process the request
$method = $_SERVER['REQUEST_METHOD'];
$dashboardStatsHandler = new DashboardStatsRestHandler($conn);

switch($method) {
    case 'GET':
        $dashboardStatsHandler->getDashboardStats();
        break;
    default:
        // Method not allowed
        $dashboardStatsHandler->setHttpHeaders('application/json', 405);
        $errorResponse = array(
            'status' => 'error',
            'message' => 'Method not allowed'
        );
        echo json_encode($errorResponse);
        
} 