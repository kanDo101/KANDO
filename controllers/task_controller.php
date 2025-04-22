<?php
// Include database connection
require_once 'db_connection.php';

// Get tasks by project ID
function getTasksByProjectId($projectId) {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT t.*, u.username, u.photo
                           FROM task t
                           LEFT JOIN user u ON t.assigned_to = u.id
                           WHERE t.projectId = ?
                           ORDER BY t.id DESC");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($task = $result->fetch_assoc()) {
        $tasks[] = $task;
    }

    $stmt->close();
    $conn->close();

    return $tasks;
}

// Add a new task
function addTask($title, $description, $state, $projectId, $assignedTo = null) {
    $conn = getConnection();

    $stmt = $conn->prepare("INSERT INTO task (name, description, state, projectId, assigned_to)
                           VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("sssii", $title, $description, $state, $projectId, $assignedTo);
    $result = $stmt->execute();
    $taskId = $conn->insert_id;
    $stmt->close();

    if ($result) {
        $conn->close();
        return ['success' => true, 'taskId' => $taskId];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to add task'];
    }
}

// Update task
function updateTask($taskId, $title, $description, $state, $assignedTo = null) {
    $conn = getConnection();

    $stmt = $conn->prepare("UPDATE task SET name = ?, description = ?, state = ?, assigned_to = ? WHERE id = ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("sssii", $title, $description, $state, $assignedTo, $taskId);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $conn->close();
        return ['success' => true];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to update task'];
    }
}

// Update task state
function updateTaskState($taskId, $state) {
    $conn = getConnection();

    $stmt = $conn->prepare("UPDATE task SET state = ? WHERE id = ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("si", $state, $taskId);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $conn->close();
        return ['success' => true];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to update task state'];
    }
}

// Delete task
function deleteTask($taskId) {
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM task WHERE id = ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("i", $taskId);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $conn->close();
        return ['success' => true];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to delete task'];
    }
}
