<?php
include '../components/connect.php';

header('Content-Type: application/json');

try {
    // In a real application, these would come from the database
    $statistics = [
        'courses' => [
            'icon' => 'fas fa-graduation-cap',
            'count' => '+1k',
            'text' => 'online courses'
        ],
        'students' => [
            'icon' => 'fas fa-user-graduate',
            'count' => '+25k',
            'text' => 'brilliant students'
        ],
        'teachers' => [
            'icon' => 'fas fa-chalkboard-user',
            'count' => '+5k',
            'text' => 'expert teachers'
        ],
        'placement' => [
            'icon' => 'fas fa-briefcase',
            'count' => '100%',
            'text' => 'job placement'
        ]
    ];

    echo json_encode(['status' => 'success', 'data' => $statistics]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching statistics']);
}
?>
