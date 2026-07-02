<?php
// Set CORS headers so the Flutter App can communicate with it
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/db.php';

// Instantiate Database and connect
$database = new Database();
$db = $database->getConnection();

// Get raw posted JSON data from Flutter's Dio client
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete credentials."]);
    exit();
}

$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;

try {
    // Prepare SQL query to fetch user and potential financial block status
    $query = "SELECT u.id, u.name, u.email, u.password_hash, u.role_identifier, 
                     COALESCE(sf.is_blocked, 0) as is_blocked 
              FROM users u 
              LEFT JOIN student_finances sf ON u.id = sf.student_id 
              WHERE u.email = :email AND u.is_active = 1 
              LIMIT 1";
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify Password
        if (password_verify($password, $row['password_hash'])) {
            
            // If the user is a student and they are blocked by accounting, deny access
            if ($row['role_identifier'] === 'student' && $row['is_blocked'] == 1) {
                http_response_code(403);
                echo json_encode([
                    "status" => "error", 
                    "message" => "Account suspended. Please contact Accounting regarding outstanding installments."
                ]);
                exit();
            }

            // Generate a simple mock token (In production, use JWT - JSON Web Tokens)
            $token = bin2hex(random_bytes(16));

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login successful.",
                "data" => [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "role_identifier" => $row['role_identifier'],
                    "is_blocked" => (bool)$row['is_blocked'],
                    "access_token" => $token
                ]
            ]);
        } else {
            // Password incorrect
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Incorrect email or password."]);
        }
    } else {
        // User not found or inactive
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Incorrect email or password."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error."]);
}
?>