<?php
// ... standard PDO setup ...
$student_id = htmlspecialchars(strip_tags($_GET['student_id']));

$query = "SELECT 
            u.name, 
            u.department, 
            e.university_name AS exchange_university,
            e.semester AS exchange_semester,
            x.activity_title AS extracurricular
          FROM users u
          LEFT JOIN exchange_programs e ON u.id = e.student_id
          LEFT JOIN extracurriculars x ON u.id = x.student_id
          WHERE u.id = :student_id LIMIT 1";

$stmt = $db->prepare($query);
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
// Return JSON mapped to the Profile UI...
?>