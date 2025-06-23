<?php
session_start();
require_once 'config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function login($username, $password) {
        $query = "SELECT * FROM admin_users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_fullname'] = $user['full_name'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_avatar'] = $user['avatar'];
                return true;
            }
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
    
    public function logout() {
        session_destroy();
        session_unset();
        return true;
    }
    
    public function getCurrentUser() {
        if($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'full_name' => $_SESSION['admin_fullname'],
                'email' => $_SESSION['admin_email'],
                'avatar' => $_SESSION['admin_avatar']
            ];
        }
        return null;
    }
}
?>