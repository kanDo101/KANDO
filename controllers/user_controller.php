<?php
// Include database connection
require_once 'db_connection.php';

// Get user profile by ID
function getUserProfile($userId) {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id, username, email, photo FROM user WHERE id = ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user) {
        return ['success' => true, 'user' => $user];
    } else {
        return ['success' => false, 'message' => 'User not found'];
    }
}

// Get all users
function getAllUsers() {
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT id, username, photo FROM user");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }

    $stmt->close();
    $conn->close();

    return $users;
}

// Update user profile
function updateUserProfile($userId, $username, $email, $photo = null) {
    $conn = getConnection();

    if ($photo) {
        $stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $photo, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE user SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
    }

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $result = $stmt->execute();
    $stmt->close();
    $conn->close();

    if ($result) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
}

// Search users by username
function searchUsersByUsername($term) {
    $conn = getConnection();

    $searchTerm = "%$term%";
    $stmt = $conn->prepare("SELECT id, username, photo FROM user WHERE username LIKE ? OR email LIKE ? LIMIT 10");

    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($user = $result->fetch_assoc()) {
        $users[] = $user;
    }

    $stmt->close();
    $conn->close();

    return $users;
}
