<?php
include 'components/connect.php';

if (isset($_POST['search_query'])) {
    $search_course = $_POST['search_query'];

    if (!empty($search_course)) {
        $select_courses = $conn->prepare("SELECT p.*, t.name as tutor_name, t.image as tutor_image 
                                        FROM `playlist` p 
                                        JOIN `tutors` t ON p.tutor_id = t.id 
                                        WHERE p.title LIKE ? AND p.status = ?");
        $like_search = "%$search_course%";
        $select_courses->execute([$like_search, 'active']);

        if ($select_courses->rowCount() > 0) {
            while ($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)) {
                $course_id = $fetch_course['id'];
?>
                <div class="box">
                    <div class="tutor">
                        <img src="uploaded_files/<?= $fetch_course['tutor_image']; ?>" alt="">
                        <div>
                            <h3><?= $fetch_course['tutor_name']; ?></h3>
                            <span><?= $fetch_course['date']; ?></span>
                        </div>
                    </div>
                    <img src="uploaded_files/<?= $fetch_course['thumb']; ?>" class="thumb" alt="">
                    <h3 class="title"><?= $fetch_course['title']; ?></h3>
                    <a href="playlist.php?get_id=<?= $course_id; ?>" class="inline-btn">view playlist</a>
                </div>
<?php
            }
        } else {
            echo '<p class="empty">no courses found!</p>';
        }
    } else {
        echo '<p class="empty">please search something!</p>';
    }
} else {
    echo '<p class="empty">Invalid request!</p>';
}
?>