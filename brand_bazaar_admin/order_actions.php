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
        case 'get_orders':
            $page = $_POST['page'] ?? 1;
            $per_page = 10;
            $offset = ($page - 1) * $per_page;
            $status = $_POST['status'] ?? '';
            $date = $_POST['date'] ?? '';
            
            // Base query
            $query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone
                     FROM orders o
                     JOIN customers c ON o.customer_id = c.id";
            
            $count_query = "SELECT COUNT(*) FROM orders o";
            
            // Add filters
            $where = [];
            $params = [];
            
            if($status && $status != 'all') {
                $where[] = "o.status = :status";
                $params[':status'] = $status;
            }
            
            if($date) {
                $where[] = "DATE(o.order_date) = :order_date";
                $params[':order_date'] = $date;
            }
            
            if(!empty($where)) {
                $query .= " WHERE " . implode(" AND ", $where);
                $count_query .= " WHERE " . implode(" AND ", $where);
            }
            
            // Add sorting and pagination
            $query .= " ORDER BY o.order_date DESC LIMIT :offset, :per_page";
            
            // Get total count
            $count_stmt = $db->prepare($count_query);
            foreach($params as $key => $value) {
                $count_stmt->bindValue($key, $value);
            }
            $count_stmt->execute();
            $total = $count_stmt->fetchColumn();
            
            // Get orders
            $stmt = $db->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['orders'] = $orders;
            $response['total'] = $total;
            $response['pages'] = ceil($total / $per_page);
            break;
            
        case 'get_order':
            $order_id = $_POST['order_id'];
            
            // Get order details
            $query = "SELECT o.*, c.first_name, c.last_name, c.email, c.phone
                     FROM orders o
                     JOIN customers c ON o.customer_id = c.id
                     WHERE o.id = :order_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get order items
                $items_query = "SELECT oi.*, p.name as product_name
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.id
                              WHERE oi.order_id = :order_id";
                $items_stmt = $db->prepare($items_query);
                $items_stmt->bindParam(':order_id', $order_id);
                $items_stmt->execute();
                
                $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response['success'] = true;
                $response['order'] = $order;
            } else {
                $response['message'] = 'Order not found';
            }
            break;
            
        case 'update_order_status':
            $order_id = $_POST['order_id'];
            $status = $_POST['status'];
            
            $query = "UPDATE orders SET status = :status WHERE id = :order_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':status', $status);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Order status updated successfully';
            } else {
                $response['message'] = 'Failed to update order status';
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