<?php
// Include database connection
require_once 'db_connection.php';

// Login function
function login($email, $password) {
    $conn = getConnection();

    // Use error_log for debugging instead of echo
    error_log("Login attempt: $email");

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password, username, fullName, photo FROM user WHERE email = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password, $username, $fullName, $photo);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        // Successful login: Start session
        session_start();
        $_SESSION["user_id"] = $id;
        $_SESSION["username"] = $username;
        $_SESSION["fullname"] = $fullName;
        $_SESSION["email"] = $email;
        $_SESSION["photo"] = $photo;

        $stmt->close();
        $conn->close();

        return true;
    } else {
        $stmt->close();
        $conn->close();

        return false;
    }
}

// Register function
function register($fullName, $username, $email, $password, $profession, $photo = null) {
    $conn = getConnection();

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Email already exists'];
    }

    $stmt->close();

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username already exists'];
    }

    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO user (fullName, username, email, password, profession, photo) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $fullName, $username, $email, $hashed_password, $profession, $photo);
    $result = $stmt->execute();

    if ($result) {
        $user_id = $conn->insert_id;
        $stmt->close();
        $conn->close();

        return ['success' => true, 'id' => $user_id, 'username' => $username];
    } else {
        $stmt->close();
        $conn->close();

        return ['success' => false, 'message' => 'Registration failed'];
    }
}

// Logout function
function logout() {
    session_start();
    session_unset();
    session_destroy();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine if it's a login or registration request based on the form fields
    if (isset($_POST["email"]) && isset($_POST["password"])) {
        // Check if it's a registration request (has fullName field)
        if (isset($_POST["fullName"]) && isset($_POST["username"]) && isset($_POST["profession"])) {
            // Registration request
            $fullName = htmlspecialchars(trim($_POST["fullName"]));
            $username = htmlspecialchars(trim($_POST["username"]));
            $profession = htmlspecialchars(trim($_POST["profession"]));
            $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
            $password = $_POST["password"];

            // Handle photo upload
            $photo = "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png";
            if (isset($_POST["photo"]) && !empty($_POST["photo"])) {
                // Check if it's a base64 image
                if (strpos($_POST["photo"], 'data:image') === 0) {
                    $photo = $_POST["photo"];

                    // Log for debugging
                    error_log("Base64 image received, length: " . strlen($photo));
                }
            }

            $result = register($fullName, $username, $email, $password, $profession, $photo);

            if ($result["success"]) {
                // Start session and store user information
                session_start();
                $_SESSION["user_id"] = $result["id"];
                $_SESSION["username"] = $result["username"];
                $_SESSION["fullname"] = $fullName;
                $_SESSION["email"] = $email;
                $_SESSION["photo"] = $photo;

                // Redirect to dashboard
                header("Location: /KANDO/KANDO_MVC/VIEW/auth/signup.php#useraded");
                exit();
            } else {
                // Registration failed, set error message and redirect back to signup page
                session_start();
                $_SESSION["login_error"] = $result["message"];
                header("Location: ../VIEW/auth/signup.php");
                exit();
            }
        } else {
            // Login request
            $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
            $password = $_POST["password"];

            if (login($email, $password)) {
                // Login successful, redirect to dashboard
                header("Location: /KANDO/KANDO_MVC/VIEW/dashboard.php");
                exit();
            } else {
                // Login failed, set error message and redirect back to signup page
                session_start();
                $_SESSION["login_error"] = "Invalid email or password";
                header("Location: ../VIEW/auth/signup.php");
                exit();
            }
        }
    } else {
        // Invalid request, redirect to signup page
        header("Location: ../VIEW/auth/signup.php");
        exit();
    }
}
?>
