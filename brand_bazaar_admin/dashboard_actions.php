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
        case 'get_stats':
            // Total orders
            $orders_query = "SELECT COUNT(*) as total_orders, 
                           SUM(total_amount) as total_revenue
                           FROM orders";
            $orders_stmt = $db->prepare($orders_query);
            $orders_stmt->execute();
            $orders_stats = $orders_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Total customers
            $customers_query = "SELECT COUNT(*) as total_customers FROM customers";
            $customers_stmt = $db->prepare($customers_query);
            $customers_stmt->execute();
            $customers_stats = $customers_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Total products
            $products_query = "SELECT COUNT(*) as total_products,
                             SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
                             FROM products";
            $products_stmt = $db->prepare($products_query);
            $products_stmt->execute();
            $products_stats = $products_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Recent orders
            $recent_orders_query = "SELECT o.order_number, o.order_date, o.total_amount, o.status,
                                  CONCAT(c.first_name, ' ', c.last_name) as customer_name
                                  FROM orders o
                                  JOIN customers c ON o.customer_id = c.id
                                  ORDER BY o.order_date DESC
                                  LIMIT 5";
            $recent_orders_stmt = $db->prepare($recent_orders_query);
            $recent_orders_stmt->execute();
            $recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['stats'] = [
                'total_orders' => $orders_stats['total_orders'],
                'total_revenue' => $orders_stats['total_revenue'] ?? 0,
                'total_customers' => $customers_stats['total_customers'],
                'total_products' => $products_stats['total_products'],
                'out_of_stock' => $products_stats['out_of_stock']
            ];
            $response['recent_orders'] = $recent_orders;
            break;
            
        case 'get_sales_data':
            $range = $_POST['range'] ?? '30'; // Default to last 30 days
            
            $query = "SELECT 
                     DATE(order_date) as date,
                     COUNT(*) as order_count,
                     SUM(total_amount) as revenue
                     FROM orders
                     WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL :range DAY)
                     GROUP BY DATE(order_date)
                     ORDER BY date ASC";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':range', $range, PDO::PARAM_INT);
            $stmt->execute();
            
            $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['sales_data'] = $sales_data;
            break;
            
        case 'get_category_data':
            $query = "SELECT 
                     c.name as category,
                     COUNT(oi.id) as items_sold,
                     SUM(oi.total_price) as revenue
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     JOIN product_categories c ON p.category_id = c.id
                     GROUP BY c.name
                     ORDER BY items_sold DESC
                     LIMIT 5";
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $category_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['category_data'] = $category_data;
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>