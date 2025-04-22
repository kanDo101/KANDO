<?php
// Include database connection
require_once 'db_connection.php';

/**
 * Get all messages for a project
 *
 * @param int $projectId The project ID
 * @return array Array of messages with sender information
 */
function getMessagesByProjectId($projectId) {
    $conn = getConnection();
    if (!$conn) {
        return [];
    }

    $stmt = $conn->prepare("SELECT m.*, u.username, u.photo
                           FROM message m
                           JOIN user u ON m.senderId = u.id
                           WHERE m.projectId = ?
                           ORDER BY m.created_at ASC");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($message = $result->fetch_assoc()) {
        // Set default photo if not available
        if (!isset($message['photo']) || empty($message['photo'])) {
            $message['photo'] = "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
        }
        $messages[] = $message;
    }

    $stmt->close();
    $conn->close();

    return $messages;
}

/**
 * Add a new message
 *
 * @param int $projectId The project ID
 * @param int $senderId The sender user ID
 * @param string $content The message content
 * @return array Success status and message ID
 */
function addMessage($projectId, $senderId, $content) {
    $conn = getConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    $stmt = $conn->prepare("INSERT INTO message (projectId, senderId, content)
                           VALUES (?, ?, ?)");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement: ' . $conn->error];
    }

    $stmt->bind_param("iis", $projectId, $senderId, $content);
    $result = $stmt->execute();

    if (!$result) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to add message: ' . $stmt->error];
    }

    $messageId = $conn->insert_id;

    $stmt->close();
    $conn->close();

    return ['success' => true, 'messageId' => $messageId];
}

/**
 * Mark messages as read
 *
 * @param int $projectId The project ID
 * @param int $userId The user ID who is reading the messages
 * @return array Success status
 */
function markMessagesAsRead($projectId, $userId) {
    $conn = getConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    $stmt = $conn->prepare("UPDATE message SET is_read = 1
                           WHERE projectId = ? AND senderId != ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("ii", $projectId, $userId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();

    return ['success' => $result];
}

/**
 * Get unread message count for a user
 *
 * @param int $userId The user ID
 * @return int Number of unread messages
 */
function getUnreadMessageCount($userId) {
    $conn = getConnection();
    if (!$conn) {
        return 0;
    }

    // Get projects the user is a member of
    $stmt = $conn->prepare("SELECT projectId FROM Appartenir WHERE userId = ?");

    if (!$stmt) {
        $conn->close();
        return 0;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $projectIds = [];
    while ($row = $result->fetch_assoc()) {
        $projectIds[] = $row['projectId'];
    }
    $stmt->close();

    if (empty($projectIds)) {
        $conn->close();
        return 0;
    }

    // Get count of unread messages in those projects
    $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';
    $query = "SELECT COUNT(*) as count FROM message
              WHERE projectId IN ($placeholders)
              AND senderId != ?
              AND is_read = 0";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $conn->close();
        return 0;
    }

    $types = str_repeat('i', count($projectIds)) . 'i';
    $params = array_merge($projectIds, [$userId]);

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $count = $row ? $row['count'] : 0;

    $stmt->close();
    $conn->close();

    return $count;
}


