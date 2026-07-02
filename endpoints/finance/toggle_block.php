<?php
// Set CORS headers for Flutter communication
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db.php';

// Instantiate Database
$database = new Database();
$db = $database->getConnection();

// Get raw posted JSON data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (empty($data->student_id) || !isset($data->current_status)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing student ID or status parameters."]);
    exit();
}

$student_id = htmlspecialchars(strip_tags($data->student_id));
// Invert the current status (if true, make false; if false, make true)
$new_status = $data->current_status ? 0 : 1; 

try {
    // Check if the student actually exists in the finance table
    $check_query = "SELECT student_id FROM student_finances WHERE student_id = :student_id LIMIT 1";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':student_id', $student_id);
    $check_stmt->execute();

    if ($check_stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Financial record for this student not found."]);
        exit();
    }

    // Update the block status
    $query = "UPDATE student_finances 
              SET is_blocked = :new_status 
              WHERE student_id = :student_id";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':new_status', $new_status, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Student block status updated successfully.",
            "data" => [
                "student_id" => $student_id,
                "is_blocked" => (bool)$new_status
            ]
        ]);
    } else {
        http_response_code(503);
        echo json_encode(["status" => "error", "message" => "Unable to update status."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database error encountered."]);
}
?>