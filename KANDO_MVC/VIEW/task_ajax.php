<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Include task controller
require_once '../controllers/task_controller.php';

$user_id = $_SESSION["user_id"];
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'addTask':
        handleAddTask();
        break;
    case 'updateTask':
        handleUpdateTask();
        break;
    case 'deleteTask':
        handleDeleteTask();
        break;
    case 'updateState':
        handleUpdateTaskState();
        break;
    case 'getTaskDetails':
        handleGetTaskDetails();
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}

function handleAddTask()
{
    $projectId = isset($_POST['projectId']) ? intval($_POST['projectId']) : 0;
    $title = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : 'Pending';
    $assignedTo = isset($_POST['assignedTo']) && !empty($_POST['assignedTo']) ? intval($_POST['assignedTo']) : null;
    // Note: dueDate and priority are not used in the current task table structure

    if (empty($title) || $projectId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid task data"]);
        return;
    }

    // Validate state values directly
    $validStates = ['Pending', 'inprogress', 'Completed'];
    if (!in_array($state, $validStates)) {
        echo json_encode(["success" => false, "message" => "Invalid state"]);
        return;
    }

    $result = addTask($title, $description, $state, $projectId, $assignedTo);

    if ($result['success']) {
        $taskId = $result['taskId'];
        // Get the task details to return to the client
        $task = getTaskWithUserInfo($taskId);
        echo json_encode(["success" => true, "task" => $task]);
    } else {
        echo json_encode(["success" => false, "message" => $result['message']]);
    }
}

function handleUpdateTask()
{
    $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
    $title = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $assignedTo = isset($_POST['assignedTo']) && !empty($_POST['assignedTo']) ? intval($_POST['assignedTo']) : null;
    // Note: dueDate and priority are not used in the current task table structure

    if (empty($title) || $taskId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid task data"]);
        return;
    }

    // Validate state values directly
    $validStates = ['Pending', 'inprogress', 'Completed'];
    if (!in_array($state, $validStates)) {
        echo json_encode(["success" => false, "message" => "Invalid state"]);
        return;
    }

    $result = updateTask($taskId, $title, $description, $state, $assignedTo);

    if ($result['success']) {
        // Get the updated task details
        $task = getTaskWithUserInfo($taskId);
        echo json_encode(["success" => true, "task" => $task]);
    } else {
        echo json_encode(["success" => false, "message" => $result['message']]);
    }
}

function handleDeleteTask()
{
    $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;

    if ($taskId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid task ID"]);
        return;
    }

    $result = deleteTask($taskId);

    if ($result['success']) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $result['message']]);
    }
}

function handleUpdateTaskState() {
    $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';

    if ($taskId <= 0 || empty($state)) {
        echo json_encode(["success" => false, "message" => "Invalid task data"]);
        return;
    }

    // Debug information
    error_log("Updating task $taskId to state: '$state'");

    // Validate state values directly
    $validStates = ['Pending', 'inprogress', 'Completed'];
    if (!in_array($state, $validStates)) {
        echo json_encode(["success" => false, "message" => "Invalid state value: $state"]);
        return;
    }

    $result = updateTaskState($taskId, $state);

    if ($result['success']) {
        echo json_encode([
            "success" => true,
            "message" => "Task updated to $state"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $result['message']
        ]);
    }
}


function handleGetTaskDetails()
{
    $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;

    if ($taskId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid task ID"]);
        return;
    }

    $task = getTaskWithUserInfo($taskId);

    if ($task) {
        echo json_encode(["success" => true, "task" => $task]);
    } else {
        echo json_encode(["success" => false, "message" => "Task not found"]);
    }
}

function getTaskWithUserInfo($taskId)
{
    $conn = getConnection();

    $query = "SELECT t.id, t.name, t.description, t.state,
                     u.id as assigned_user_id, u.username as assigned_username, u.photo as assigned_photo
              FROM task t
              LEFT JOIN user u ON t.assigned_to = u.id
              WHERE t.id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $conn->close();
        return null;
    }

    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Set default photo if not available
    if ($task && (!isset($task['assigned_photo']) || empty($task['assigned_photo']))) {
        $task['assigned_photo'] = "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    }

    return $task;
}