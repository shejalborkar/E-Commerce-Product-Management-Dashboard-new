<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-API-Key");

include_once '../../config/Database.php';
include_once '../../middleware/AuthMiddleware.php';

$database = new Database();
$db = $database->getConnection();

$auth = new AuthMiddleware($db);
if (!$auth->validateApiKey()) {
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->action) || !isset($data->percentage)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required parameters"));
    exit();
}

// Validate percentage
$percentage = floatval($data->percentage);
if ($percentage < 0 || $percentage > 100) {
    http_response_code(400);
    echo json_encode(array("message" => "Percentage must be between 0 and 100"));
    exit();
}

try {
    // Start transaction
    $db->beginTransaction();

    $operator = $data->action === 'increase' ? '+' : '-';
    $query = "UPDATE products SET price = ROUND(price * (1 " . $operator . " ?/100), 2)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$percentage]);

    // Commit transaction
    $db->commit();

    http_response_code(200);
    echo json_encode(array("message" => "Prices updated successfully"));
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollBack();
    http_response_code(503);
    echo json_encode(array("message" => "Unable to update prices"));
}
?> 