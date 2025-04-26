<?php
require_once 'PlaylistAPI.php';

$playlistAPI = new PlaylistAPI();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $playlistAPI->getAllPlaylists();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
