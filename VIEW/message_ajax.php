<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set a default user ID for testing if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

$user_id = $_SESSION['user_id'];

// Include message controller
require_once '../controllers/message_controller.php';

// Simple action handling
if (isset($_POST['action']) && $_POST['action'] == 'add_message') {
    handleAddMessage();
} elseif (isset($_GET['action']) && $_GET['action'] == 'get_messages') {
    handleGetMessages();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

function handleAddMessage()
{
    global $user_id;

    $projectId = isset($_POST['projectId']) ? intval($_POST['projectId']) : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if (empty($content) || $projectId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid message data"]);
        return;
    }

    // Add the message to the database
    $result = addMessage($projectId, $user_id, $content);

    if ($result['success']) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add message"]);
    }
}

function handleGetMessages()
{
    $projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : 0;

    if ($projectId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid project ID"]);
        return;
    }

    // Get messages for the project
    $messages = getMessagesByProjectId($projectId);
    echo json_encode(["success" => true, "messages" => $messages]);
}
