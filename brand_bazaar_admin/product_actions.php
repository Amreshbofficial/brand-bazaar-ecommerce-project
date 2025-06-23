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
        case 'add_product':
            // Handle product addition
            $product_code = 'PROD-' . strtoupper(uniqid());
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category_id = $_POST['category_id'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $query = "INSERT INTO products (product_code, name, description, category_id, price, stock_quantity, featured) 
                      VALUES (:product_code, :name, :description, :category_id, :price, :stock, :featured)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':product_code', $product_code);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':featured', $featured);
            
            if($stmt->execute()) {
                $product_id = $db->lastInsertId();
                
                // Handle image uploads
                if(!empty($_FILES['images'])) {
                    $upload_dir = 'uploads/products/';
                    if(!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $is_first = true;
                    foreach($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = basename($_FILES['images']['name'][$key]);
                        $target_path = $upload_dir . uniqid() . '_' . $file_name;
                        
                        if(move_uploaded_file($tmp_name, $target_path)) {
                            $image_query = "INSERT INTO product_images (product_id, image_path, is_main) 
                                          VALUES (:product_id, :image_path, :is_main)";
                            $image_stmt = $db->prepare($image_query);
                            $image_stmt->bindParam(':product_id', $product_id);
                            $image_stmt->bindParam(':image_path', $target_path);
                            $image_stmt->bindValue(':is_main', $is_first ? 1 : 0);
                            $image_stmt->execute();
                            
                            $is_first = false;
                        }
                    }
                }
                
                $response['success'] = true;
                $response['message'] = 'Product added successfully';
                $response['product_id'] = $product_id;
            } else {
                $response['message'] = 'Failed to add product';
            }
            break;
            
        case 'edit_product':
            // Handle product editing
            $product_id = $_POST['product_id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $category_id = $_POST['category_id'];
            $featured = isset($_POST['featured']) ? 1 : 0;
            
            $query = "UPDATE products SET 
                      name = :name, 
                      description = :description, 
                      category_id = :category_id, 
                      price = :price, 
                      stock_quantity = :stock, 
                      featured = :featured,
                      updated_at = NOW()
                      WHERE id = :product_id";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':featured', $featured);
            
            if($stmt->execute()) {
                // Handle image uploads for edits
                if(!empty($_FILES['images'])) {
                    $upload_dir = 'uploads/products/';
                    if(!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Check if we have existing main image
                    $check_main = "SELECT COUNT(*) FROM product_images WHERE product_id = :product_id AND is_main = 1";
                    $check_stmt = $db->prepare($check_main);
                    $check_stmt->bindParam(':product_id', $product_id);
                    $check_stmt->execute();
                    $has_main = $check_stmt->fetchColumn() > 0;
                    
                    foreach($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = basename($_FILES['images']['name'][$key]);
                        $target_path = $upload_dir . uniqid() . '_' . $file_name;
                        
                        if(move_uploaded_file($tmp_name, $target_path)) {
                            $is_main = !$has_main ? 1 : 0;
                            $has_main = true;
                            
                            $image_query = "INSERT INTO product_images (product_id, image_path, is_main) 
                                          VALUES (:product_id, :image_path, :is_main)";
                            $image_stmt = $db->prepare($image_query);
                            $image_stmt->bindParam(':product_id', $product_id);
                            $image_stmt->bindParam(':image_path', $target_path);
                            $image_stmt->bindValue(':is_main', $is_main);
                            $image_stmt->execute();
                        }
                    }
                }
                
                $response['success'] = true;
                $response['message'] = 'Product updated successfully';
            } else {
                $response['message'] = 'Failed to update product';
            }
            break;
            
        case 'delete_product':
            $product_id = $_POST['product_id'];
            
            // First delete images to maintain referential integrity
            $delete_images = "DELETE FROM product_images WHERE product_id = :product_id";
            $stmt = $db->prepare($delete_images);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            // Then delete the product
            $query = "DELETE FROM products WHERE id = :product_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Product deleted successfully';
            } else {
                $response['message'] = 'Failed to delete product';
            }
            break;
            
        case 'get_product':
            $product_id = $_POST['product_id'];
            
            $query = "SELECT p.*, 
                     GROUP_CONCAT(pi.image_path) as images,
                     GROUP_CONCAT(pi.is_main) as is_main
                     FROM products p
                     LEFT JOIN product_images pi ON p.id = pi.product_id
                     WHERE p.id = :product_id
                     GROUP BY p.id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Process images
                $images = [];
                if($product['images']) {
                    $image_paths = explode(',', $product['images']);
                    $is_mains = explode(',', $product['is_main']);
                    
                    foreach($image_paths as $index => $path) {
                        $images[] = [
                            'path' => $path,
                            'is_main' => $is_mains[$index]
                        ];
                    }
                }
                
                $product['images'] = $images;
                unset($product['is_main']);
                
                $response['success'] = true;
                $response['product'] = $product;
            } else {
                $response['message'] = 'Product not found';
            }
            break;
            
        case 'get_products':
            $page = $_POST['page'] ?? 1;
            $per_page = 10;
            $offset = ($page - 1) * $per_page;
            
            // Get total count
            $count_query = "SELECT COUNT(*) FROM products";
            $count_stmt = $db->prepare($count_query);
            $count_stmt->execute();
            $total = $count_stmt->fetchColumn();
            
            // Get products
            $query = "SELECT p.*, 
                     (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image,
                     c.name as category_name
                     FROM products p
                     LEFT JOIN product_categories c ON p.category_id = c.id
                     ORDER BY p.id DESC
                     LIMIT :offset, :per_page";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->execute();
            
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['success'] = true;
            $response['products'] = $products;
            $response['total'] = $total;
            $response['pages'] = ceil($total / $per_page);
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch(PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch(Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>