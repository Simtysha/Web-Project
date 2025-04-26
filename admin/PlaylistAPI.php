<?php
require_once '../components/connect.php';

class SimpleREST {
    protected function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

class PlaylistAPI extends SimpleREST {

    public function getAllPlaylists() {
        if (!isset($_COOKIE['tutor_id'])) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        global $conn;
        $tutor_id = $_COOKIE['tutor_id'];

        $query = $conn->prepare("SELECT * FROM playlist WHERE tutor_id = ? ORDER BY date DESC");
        $query->execute([$tutor_id]);
        $playlists = [];

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $playlist_id = $row['id'];
            $video_count_query = $conn->prepare("SELECT COUNT(*) FROM content WHERE playlist_id = ?");
            $video_count_query->execute([$playlist_id]);
            $row['total_videos'] = $video_count_query->fetchColumn();
            $playlists[] = $row;
        }

        return $this->jsonResponse($playlists);
    }
}
