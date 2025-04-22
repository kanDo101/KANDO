<?php
// Database connection
function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todo_kanban_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        // Log the error if the logMessage function exists
        if (function_exists('logMessage')) {
            logMessage("Database connection failed: " . $conn->connect_error);
        }
        return false;
    }

    return $conn;
}
?>
