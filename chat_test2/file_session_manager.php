<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'file_session_handler.php';  // Include the file session handler

header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

switch ($data['action']) {
    case 'start':
        if (!isset($data['userId'])) {
            $response = ['success' => false, 'message' => 'User ID is required'];
        } else {
            $userId = $data['userId'];
            $response = startFileSession($userId);
        }
        break;
    case 'update':
        if (!isset($data['session_id'], $data['step'])) {
            $response = ['success' => false, 'message' => 'Session ID and step are required'];
        } else {
            $sessionId = $data['session_id'];
            $step = $data['step'];
            $response = updateFileSession($sessionId, $step);
        }
        break;
    case 'end':
        if (!isset($data['session_id'])) {
            $response = ['success' => false, 'message' => 'Session ID is required for ending a session'];
        } else {
            $sessionId = $data['session_id'];
            $response = endFileSession($sessionId);
        }
        break;
    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
}

echo json_encode($response);
?>
