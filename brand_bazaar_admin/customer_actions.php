<?php
require_once 'config/database.php';
require_once 'auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if(!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'get_customers':
            $page = $_POST['page'] ?? 1;
            $per_page = 10;
            $offset = ($page - 1) * $per_page;
            $search = $_POST['search'] ?? '';
            
            // Base query
            $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
                     (SELECT SUM(total_amount) FROM orders WHERE customer_id = c.id) as total_spent
                     FROM customers c";
            
            $count_query = "SELECT COUNT(*) FROM customers";
            
            // Add search filter
            $params = [];
            if($search) {
                $query .= " WHERE CONCAT(c.first_name, ' ', c.last_name) LIKE :search OR c.email LIKE :search";
                $count_query .= " WHERE CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search";
                $params[':search'] = "%$search%";
            }
            
            // Add sorting and pagination
            $query .= " ORDER BY c.id DESC LIMIT :offset, :per_page";
            
            // Get total count
            $count_stmt = $db->prepare($count_query);
            foreach($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetchColumn();
            
            // Get customers
            $stmt = $db->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->execute();
            
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['customers'] = $customers;
            $response['total'] = $total;
            $response['pages'] = ceil($total / $per_page);
            break;
            
        case 'get_customer':
            $customer_id = $_POST['customer_id'];
            
            // Get customer details
            $query = "SELECT c.*, 
                     (SELECT COUNT(*) FROM orders WHERE customer_id = c.id) as order_count,
                     (SELECT SUM(total_amount) FROM orders WHERE customer_id = c.id) as total_spent,
                     (SELECT AVG(rating) FROM reviews WHERE customer_id = c.id) as avg_rating
                     FROM customers c
                     WHERE c.id = :customer_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get recent orders
                $orders_query = "SELECT id as order_id, order_number, order_date, total_amount, status
                               FROM orders
                               WHERE customer_id = :customer_id
                               ORDER BY order_date DESC
                               LIMIT 5";
                $orders_stmt = $db->prepare($orders_query);
                $orders_stmt->bindParam(':customer_id', $customer_id);
                $orders_stmt->execute();
                
                $customer['recent_orders'] = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response['success'] = true;
                $response['customer'] = $customer;
            } else {
                $response['message'] = 'Customer not found';
            }
            break;
            
        case 'update_customer':
            $customer_id = $_POST['customer_id'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $address = $_POST['address'];
            
            $query = "UPDATE customers SET 
                     first_name = :first_name,
                     last_name = :last_name,
                     email = :email,
                     phone = :phone,
                     address = :address
                     WHERE id = :customer_id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Customer updated successfully';
            } else {
                $response['message'] = 'Failed to update customer';
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>