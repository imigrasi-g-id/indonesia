<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Handle login
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            break;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
            $stmt->execute([$data['email']]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($data['password'], $admin['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Email atau kata sandi salah']);
                break;
            }

            // Create session token
            $token = bin2hex(random_bytes(32));
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE admins SET auth_token = ? WHERE id = ?");
            $stmt->execute([$token, $admin['id']]);

            // Return success with admin data
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'name' => $admin['name'],
                    'email' => $admin['email']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error during login: ' . $e->getMessage()
            ]);
        }
        break;

    case 'GET':
        // Verify token
        $token = $_GET['token'] ?? null;
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication token required']);
            break;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE auth_token = ?");
            $stmt->execute([$token]);
            $admin = $stmt->fetch();

            if (!$admin) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
                break;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'name' => $admin['name'],
                    'email' => $admin['email']
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error verifying token: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
