<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$usernameOrEmail = $data['username']; 
$password = $data['password'];

//  Use prepared statements to avoid SQL injection
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if user is verified 
    if (isset($user['is_verified']) && $user['is_verified'] != 1) {
        echo json_encode(["status" => "error", "message" => "Email not verified"]);
        exit;
    }

  
    if (password_verify($password, $user['password'])) {
        echo json_encode(["status" => "success", "message" => "Login successful"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>
