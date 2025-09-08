<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Add new notification
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO user_notifications (
                user_email,
                type,
                title,
                message,
                application_id,
                created_at
            ) VALUES (
                :user_email,
                :type,
                :title,
                :message,
                :application_id,
                NOW()
            )");

            $stmt->execute([
                ':user_email' => $data['userEmail'],
                ':type' => $data['type'],
                ':title' => $data['title'],
                ':message' => $data['message'],
                ':application_id' => $data['applicationId']
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Notification created successfully'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error creating notification: ' . $e->getMessage()
            ]);
        }
        break;

    case 'GET':
        $userEmail = $_GET['email'] ?? null;
        
        if (!$userEmail) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            break;
        }

        try {
            // Get user notifications
            $stmt = $pdo->prepare("SELECT * FROM user_notifications WHERE user_email = ? ORDER BY created_at DESC");
            $stmt->execute([$userEmail]);
            $notifications = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching notifications: ' . $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        // Mark notification as read
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
            break;
        }

        try {
            $stmt = $pdo->prepare("UPDATE user_notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error updating notification: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
