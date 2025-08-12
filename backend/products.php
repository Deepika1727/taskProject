<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$conn = new mysqli("localhost", "root", "", "mydb");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB Connection Failed"]));
}

switch ($method) {
    case 'GET': 
        $result = $conn->query("SELECT * FROM products");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        break;

    case 'POST': // Create Product
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data['name'] || !$data['price']) {
            echo json_encode(["success" => false, "message" => "Name & Price are required"]);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $data['name'], $data['price'], $data['description']);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product Added"]);
        } else {
            echo json_encode(["success" => false, "message" => "Insert Failed"]);
        }
        break;

    case 'PUT': // Update Product
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=? WHERE id=?");
        $stmt->bind_param("sdsi", $data['name'], $data['price'], $data['description'], $data['id']);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Product Updated"]);
        } else {
            echo json_encode(["success" => false, "message" => "Update Failed"]);
        }
        break;

    case 'DELETE': // Delete Product
        $id = intval($_GET['id']);
        if ($conn->query("DELETE FROM products WHERE id=$id")) {
            echo json_encode(["success" => true, "message" => "Product Deleted"]);
        } else {
            echo json_encode(["success" => false, "message" => "Delete Failed"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid Request"]);
        break;
}

$conn->close();
?>
