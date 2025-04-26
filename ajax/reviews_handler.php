<?php
include '../components/connect.php';

header('Content-Type: application/json');

try {
    // In a real application, these would come from the database
    $reviews = [
        [
            'name' => 'Aria Thompson',
            'rating' => 4.5,
            'text' => 'Virtu-Learn offers a seamless online tutoring experience with flexible scheduling, expert tutors, and a user-friendly platform. Perfect for students and professionals alike!',
            'image' => 'ReviewProfile1.jpg'
        ],
        [
            'name' => 'Noah Sullivan',
            'rating' => 4.5,
            'text' => 'The structured courses and interactive features on Virtu-Learn make learning engaging and effective. Highly recommend for anyone looking to upskill conveniently!',
            'image' => 'ReviewProfile2.jpg'
        ],
        [
            'name' => 'Caleb Anderson',
            'rating' => 4.5,
            'text' => 'A highly engaging and well-designed platform! Virtu-Learn\'s personalized learning approach enables students grasp concepts effectively while enjoying the learning process.',
            'image' => 'ReviewProfile3.jpg'
        ]
    ];

    echo json_encode(['status' => 'success', 'data' => $reviews]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching reviews']);
}
?>
