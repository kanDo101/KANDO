<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page if not logged in
    header("Location: ../auth/SignUp/login.php");
    exit();
}

// Include the profile controller
require_once 'profile_controller.php';

$user_id = $_SESSION["user_id"];
$redirect_url = "../VIEW/profile.php";

// Initialize alert messages
$profile_message = "";
$password_message = "";
$message_type = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullName = $_POST['fullName'];
        $username = $_POST['username'];
        $profession = $_POST['profession'];
        $email = $_POST['email'];

        // Handle file upload for photo
        $photo = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $upload_dir = '../VIEW/uploads/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $photo = $upload_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
            
            // Make the path relative for storage in the database
            $photo = 'uploads/' . basename($_FILES['photo']['name']);
        }

        // Update user profile
        $result = updateUserProfile($user_id, $fullName, $username, $email, $profession, $photo);
        
        if ($result['success']) {
            $_SESSION['profile_message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['profile_message'] = $result['message'] ?? "Error updating profile.";
            $_SESSION['message_type'] = "error";
        }
        
        // Redirect back to profile page
        header("Location: $redirect_url");
        exit();
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            $_SESSION['password_message'] = "New passwords do not match.";
            $_SESSION['message_type'] = "error";
            header("Location: $redirect_url");
            exit();
        }
        
        // Update password
        $result = updateUserPassword($user_id, $current_password, $new_password);
        
        if ($result['success']) {
            $_SESSION['password_message'] = "Password updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['password_message'] = $result['message'] ?? "Error updating password.";
            $_SESSION['message_type'] = "error";
        }
        
        // Redirect back to profile page
        header("Location: $redirect_url");
        exit();
    }
}

// If no form was submitted, redirect to profile page
header("Location: $redirect_url");
exit();
?>
