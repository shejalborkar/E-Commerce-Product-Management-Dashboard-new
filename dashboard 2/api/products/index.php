<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-API-Key");

include_once '../../config/Database.php';
include_once '../../models/Product.php';
include_once '../../middleware/AuthMiddleware.php';

$database = new Database();
$db = $database->getConnection();

$auth = new AuthMiddleware($db);
if (!$auth->validateApiKey()) {
    exit();
}

$product = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $product->id = $_GET['id'];
            $stmt = $product->readOne();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Product not found."));
            }
        } else {
            $stmt = $product->read();
            $products_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            
            http_response_code(200);
            echo json_encode($products_arr);
        }
        break;

    case 'POST':
    case 'PUT':
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->stock = $_POST['stock'];
        $product->category_id = $_POST['category_id'];
        
        // Handle file upload
        if (isset($_FILES['image'])) {
            $product->image = $_FILES['image'];
        }

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($product->create()) {
                    http_response_code(201);
                    echo json_encode(array("message" => "Product created successfully."));
                }
            } else {
                $product->id = $_POST['id'];
                if ($product->update()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Product updated successfully."));
                }
            }
        } catch (Exception $e) {
            http_response_code(503);
            echo json_encode(array("message" => $e->getMessage()));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            $product->id = $data->id;
            
            if ($product->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Product deleted successfully."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete product."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete product. Please provide ID."));
        }
        break;
}
?> 