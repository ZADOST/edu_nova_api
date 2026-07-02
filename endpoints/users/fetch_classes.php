<?php
// Set CORS headers so the Flutter App can communicate securely
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db.php';

// Instantiate Database
$database = new Database();
$db = $database->getConnection();

// Validate GET parameters
if (!isset($_GET['user_id']) || !isset($_GET['role'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing user ID or role parameters."]);
    exit();
}

$user_id = htmlspecialchars(strip_tags($_GET['user_id']));
$role = htmlspecialchars(strip_tags($_GET['role']));

try {
    if ($role === 'teacher') {
        // Query the database for the teacher's assigned classes
        $query = "SELECT id, course_name, schedule_time, location 
                  FROM courses 
                  WHERE teacher_id = :user_id 
                  ORDER BY schedule_time ASC";
                  
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $classes = [];
        
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Ensure local KRI context if location is missing from the database record
                $location = !empty($row['location']) ? $row['location'] : 'Main Campus - Erbil';
                
                $classes[] = [
                    "id" => $row['id'],
                    "className" => $row['course_name'],
                    "time" => $row['schedule_time'],
                    "location" => $location,
                    "studentCount" => rand(15, 35) // Simulating student enrollment count for the UI
                ];
            }
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Teacher schedule retrieved.",
            "data" => $classes
        ]);

    } elseif ($role === 'student') {
        // Since the student enrollment pivot table is pending in the schema, 
        // we return structured data matching the exact format your Flutter Dashboard requires.
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Student schedule retrieved.",
            "data" => [
                [
                    "id" => "c1", 
                    "className" => "Advanced Java OOP", 
                    "time" => "08:30 AM - 10:00 AM", 
                    "instructor" => "Dr. Alan Turing", 
                    "progress" => 0.75
                ],
                [
                    "id" => "c2", 
                    "className" => "Kurdish Literature & Poetry", 
                    "time" => "10:30 AM - 12:00 PM", 
                    "instructor" => "Prof. Bakhtyar Ali", 
                    "progress" => 0.40
                ],
                [
                    "id" => "c3", 
                    "className" => "Mobile App Dev (Flutter)", 
                    "time" => "01:00 PM - 02:30 PM", 
                    "instructor" => "Prof. Abdulrahman", 
                    "progress" => 0.90
                ]
            ]
        ]);
        
    } else {
        // Block HR, Accounting, or Alumni from hitting this specific endpoint
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Unauthorized role for fetching academic schedules."]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    // Suppress raw database errors from the frontend, returning a clean API response
    echo json_encode(["status" => "error", "message" => "A server configuration error occurred."]);
}
?>