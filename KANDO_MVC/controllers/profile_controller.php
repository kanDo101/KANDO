<?php
// Include database connection
require_once 'db_connection.php';

// Get user profile
function getUserProfile($userId) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
    
    if (!$stmt) {
        $conn->close();
        return null;
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    $conn->close();
    
    return $user;
}

// Update user profile
function updateUserProfile($userId, $fullName, $username, $email, $profession, $photo = null) {
    $conn = getConnection();
    
    // Check if username is already taken by another user
    if ($username) {
        $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Error preparing statement'];
        }
        
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Username already taken'];
        }
        
        $stmt->close();
    }
    
    // Check if email is already taken by another user
    if ($email) {
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
        if (!$stmt) {
            $conn->close();
            return ['success' => false, 'message' => 'Error preparing statement'];
        }
        
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Email already taken'];
        }
        
        $stmt->close();
    }
    
    // Update user profile
    $query = "UPDATE user SET ";
    $params = [];
    $types = "";
    
    if ($fullName) {
        $query .= "fullName = ?, ";
        $params[] = $fullName;
        $types .= "s";
    }
    
    if ($username) {
        $query .= "username = ?, ";
        $params[] = $username;
        $types .= "s";
    }
    
    if ($email) {
        $query .= "email = ?, ";
        $params[] = $email;
        $types .= "s";
    }
    
    if ($profession) {
        $query .= "profession = ?, ";
        $params[] = $profession;
        $types .= "s";
    }
    
    if ($photo) {
        $query .= "photo = ?, ";
        $params[] = $photo;
        $types .= "s";
    }
    
    // Remove trailing comma and space
    $query = rtrim($query, ", ");
    
    $query .= " WHERE id = ?";
    $params[] = $userId;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }
    
    // Dynamically bind parameters
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        // If username was updated, update session
        if ($username && isset($_SESSION['username'])) {
            $_SESSION['username'] = $username;
        }
        
        $conn->close();
        return ['success' => true];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
}

// Update user password
function updateUserPassword($userId, $currentPassword, $newPassword) {
    $conn = getConnection();
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();
    
    if (!password_verify($currentPassword, $hashedPassword)) {
        $conn->close();
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Update password
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }
    
    $stmt->bind_param("si", $newHashedPassword, $userId);
    $result = $stmt->execute();
    $stmt->close();
    
    if ($result) {
        $conn->close();
        return ['success' => true];
    } else {
        $conn->close();
        return ['success' => false, 'message' => 'Failed to update password'];
    }
}
?>
