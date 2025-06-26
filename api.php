<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

// Get user ID from username
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(401);
    die(json_encode(['error' => 'User not found']));
}

$user_id = $user['user_id'];
$stmt->close();

// Handle API requests
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            $type = $_GET['type'] === 'notes' ? 'note' : 'task';
            $stmt = $conn->prepare("SELECT * FROM items WHERE user_id = ? AND type = ? ORDER BY created_at DESC");
            $stmt->bind_param("is", $user_id, $type);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['items' => $items]);
            break;
            
        case 'create':
            $type = $_POST['type'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $priority = $_POST['priority'];
            
            $stmt = $conn->prepare("INSERT INTO items (user_id, type, title, content, priority) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $type, $title, $content, $priority);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'item_id' => $conn->insert_id]);
            } else {
                throw new Exception("Failed to create item");
            }
            break;
            
        case 'update':
            $item_id = $_POST['item_id'];
            $title = $_POST['title'];
            $content = $_POST['content'];
            $priority = $_POST['priority'];
            
            $stmt = $conn->prepare("UPDATE items SET title = ?, content = ?, priority = ? WHERE item_id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $title, $content, $priority, $item_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to update item");
            }
            break;
            
        case 'delete':
            $item_id = $_POST['item_id'];
            
            $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $item_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to delete item");
            }
            break;
            
        case 'toggle':
            $item_id = $_POST['item_id'];
            
            $stmt = $conn->prepare("UPDATE items SET completed = NOT completed WHERE item_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $item_id, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception("Failed to toggle item completion");
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();