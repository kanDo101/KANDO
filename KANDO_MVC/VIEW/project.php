<?php
session_start();
// Add this after session_start()
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Include controllers
require_once '../controllers/task_controller.php';
require_once '../controllers/project_controller.php';
require_once '../controllers/user_controller.php';

if (!isset($_GET['projectId'])) {
    echo "Project ID is missing!";
    exit;
}

$projectId = intval($_GET['projectId']);
$user_id = $_SESSION["user_id"];

// Fetch project details
$projectDetails = getProjectDetails($projectId);
if (!$projectDetails['success']) {
    echo "Project not found!";
    exit;
}
$project = $projectDetails['project'];

// Get user profile
$userProfile = getUserProfile($user_id);
if ($userProfile['success']) {
    $photo = $userProfile['user']['photo'] ?: "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    $username = $userProfile['user']['username'];
} else {
    $photo = "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    $username = "User";
}

// Fetch tasks
$allTasks = getTasksByProjectId($projectId);

$tasks = [
    'Pending' => [],
    'inprogress' => [],
    'Completed' => []
];

foreach ($allTasks as $task) {
    // Set default user photo if not available
    if (!isset($task['photo']) || empty($task['photo'])) {
        $task['photo'] = "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    }

    // Map the fields to match what the frontend expects
    $taskData = [
        'id' => $task['id'],
        'name' => $task['name'], // Using 'name' instead of 'title'
        'description' => $task['description'],
        'state' => $task['state'],
        'assigned_user_id' => $task['assigned_to'], // Using 'assigned_to' instead of 'assignedTo'
        'assigned_username' => isset($task['username']) ? $task['username'] : '',
        'assigned_photo' => isset($task['photo']) ? $task['photo'] : 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'
    ];

    // Use the state directly from database
    $state = $task['state'];

    // Default to 'Pending' if state doesn't exist in our tasks array
    if (!isset($tasks[$state])) {
        $state = 'Pending';
    }

    $tasks[$state][] = $taskData;
}

// Get all users for the add task form
$users = getAllUsers();



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kando - Kanban Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link rel="stylesheet" href="../public/css/messages.css">
    <script src="../public/js/toggle.js"></script>
    <style>
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333;
            --text-muted: #6c757d;
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --button-bg: #3498db;
            --button-hover: #2980b9;
            --button-text: #fff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --transition: all 0.3s ease;
            --navbar-bg: #ffffff;
            --dropdown-bg: #ffffff;
            --card-border: #e9ecef;
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --border-color: #dee2e6;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f0f0f0;
            --text-muted: #a0a0a0;
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --button-bg: #3498db;
            --button-hover: #2980b9;
            --shadow: 0 4px 6px rgba(66, 20, 20, 0.05);
            --navbar-bg: #1a1a1a;
            --dropdown-bg: #2a2a2a;
            --card-border: #333333;
            --bg-primary: #1e1e1e;
            --bg-secondary: #2a2a2a;
            --border-color: #444444;
        }

        .kanban-board {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            overflow-x: hidden;
            width: 100%;
            height: calc(100vh - 120px);
        }

        .kanban-column {
    height: 100%;
    flex: 1;
    min-width: 0; /* Allow columns to shrink below min-width */
    background-color: var(--bg-secondary);
    border-radius: 8px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column; /* Stack children vertically */
    overflow: hidden; /* Prevent overflow */
}

.task-list {
    margin-top: 10px;
    background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), var(--card-bg));
    border-radius: 6px;
    overflow-y: auto; /* Add vertical scrolling when needed */
    flex-grow: 1; /* Allow list to fill available space */
    height: calc(100% - 50px); /* Adjust for column header */
}

        .kanban-column h3 {
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .column-todo h3 {
            color: var(--primary-color);
        }

        .column-in_progress h3 {
            color: var(--secondary-color);
        }

        .column-completed h3 {
            color: var(--accent-color);
        }

        .column-chat {
            min-width: 0;
            flex: 0.8;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .column-chat h3 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 10px;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: calc(100% - 60px);
        }

        .message {
            display: flex;
            gap: 10px;
            max-width: 80%;
            margin-bottom: 12px;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .message-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .message-content {
            background-color: var(--card-bg);
            padding: 10px 15px;
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .message.sent .message-content {
            background-color: var(--primary-color);
            color: white;
            border-bottom-right-radius: 4px;
            margin-right: 8px;
        }

        .message.received .message-content {
            background-color: var(--card-bg);
            border-bottom-left-radius: 4px;
            margin-left: 8px;
        }

        .message-text {
            margin: 0;
            word-break: break-word;
        }

        .message-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .message.sent .message-meta {
            color: rgba(255, 255, 255, 0.7);
        }

        .message-form {
            display: flex;
            padding: 10px;
            background-color: var(--bg-primary);
            border-top: 1px solid var(--border-color);
            border-bottom-left-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }

        .message-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background-color: var(--bg-color);
            color: var(--text-color);
            resize: none;
            min-height: 40px;
            max-height: 100px;
            overflow-y: auto;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .send-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .send-button:hover {
            background-color: var(--button-hover);
            transform: scale(1.05);
        }

        .empty-messages {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-muted);
            text-align: center;
            padding: 20px;
        }

        .empty-messages i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            opacity: 0.5;
        }

        .task-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background-color: var(--bg-primary);
            color: var(--text-color);
            font-size: 0.8rem;
            font-weight: bold;
        }



        .task-card {
            background-color: var(--bg-primary);
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            font-size: 0.95rem;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .task-card.dragging {
            opacity: 0.5;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .task-title {
            font-weight: bold;
            margin: 0;
            font-size: 0.95rem;
            word-break: break-word;
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .task-actions {
            display: flex;
            gap: 5px;
        }

        .task-actions button {
            background: none;
            border: none;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--text-muted);
            padding: 2px;
        }

        .task-actions button:hover {
            color: var(--text-color);
        }

        .task-description {
            margin: 6px 0;
            font-size: 0.85rem;
            color: var(--text-muted);
            max-height: 60px;
            overflow-y: auto;
        }

        .task-user {
            display: flex;
            align-items: center;
            margin-top: 6px;
        }

        .task-user img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }

        .task-user span {
            font-size: 0.85rem;
            color: var(--text-color);
        }

        /* Modal styles */
        .modal {
            z-index: 9999;
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: var(--bg-color);
        }

        .modal-content {
            background-color: var(--bg-primary);
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close-modal {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {

            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            background-color: var(--bg-color);
            color: var(--text-color);
            box-sizing: border-box;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .add-task-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-task-btn i {
            margin-right: 5px;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
            z-index: 1001;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        .button-row {
            display: flex !important;
            flex-direction: row !important;
            gap: 10px !important;
            justify-content: flex-end !important;
            width: auto !important;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s, transform 0.2s;
        }

        .action-btn:hover {
            background-color: var(--button-hover);
            transform: translateY(-2px);
        }

        .action-btn i {
            margin-right: 8px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background-color: var(--accent-color);
        }

        /* Fix for dropdown menu */
        #userDropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            background-color: var(--bg-primary, white);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 150px;
            z-index: 9999;
        }

        #userDropdown.active {
            display: block !important;
        }

        .user-profile {
            position: relative;
        }

        .avatar {
            cursor: pointer;
        }

        .dropdown-item {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color, #333);
        }

        .dropdown-item:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
    </style>


</head>

<body style="overflow: hidden; height: 100vh; width: 100vw; margin: 0; padding: 0;">
    <div class="moving-circles" id="movingCircles"></div>

    <nav class="navbar" id="navbar" style="position: sticky; top: 0; z-index: 1000; width: 100%;">
        <a href="./auth/landing.html">
            <div class="logo"><i class="fas fa-tasks"></i> Kando</div>
        </a>
        <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
            <a href="#features">Features</a>
            <a href="#workflow">How It Works</a>
            <button class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
    <i class="fas fa-<?php echo $theme === 'dark' ? 'sun' : 'moon'; ?>"></i>
</button>

            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($username); ?>"
                    class="avatar" id="avatarImg">
                <div class="dropdown" id="userDropdown">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="height: calc(100vh - 60px); padding-top: 10px; overflow: hidden;">
        <input type="hidden" id="projectId" value="<?php echo $projectId; ?>">
        <div class="project-header">
            <h2>Project: <?php echo htmlspecialchars($project['name']); ?></h2>
            <div class="button-row">
                <button class="action-btn" id="addTaskBtn">
                    <i class="fas fa-plus"></i> Add Task
                </button>
                <button class="action-btn" id="joinCallBtn">
                    <i class="fas fa-video"></i> Join Call
                </button>
            </div>
        </div>

        <div class="kanban-board">
            <div class="kanban-column column-todo" data-state="Pending">
                <h3>
                    To Do
                    <span class="task-count"><?php echo count($tasks['Pending']); ?></span>
                </h3>
                <div class="task-list" data-state="Pending">
                    <?php foreach ($tasks['Pending'] as $task): ?>
                        <div class="task-card" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <div class="task-header">
                                <h4 class="task-title"><?php echo htmlspecialchars($task['name']); ?></h4>
                                <div class="task-actions">
                                    <button class="edit-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($task['description'])): ?>
                                <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($task['assigned_username'])): ?>
                                <div class="task-user">
                                    <img src="<?php echo htmlspecialchars($task['assigned_photo']); ?>"
                                        alt="<?php echo htmlspecialchars($task['assigned_username']); ?>">
                                    <span><?php echo htmlspecialchars($task['assigned_username']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- inprogress Column -->
            <div class="kanban-column column-in_progress" data-state="inprogress">
                <h3>
                    In Progress
                    <span class="task-count"><?php echo count($tasks['inprogress']); ?></span>
                </h3>
                <div class="task-list" data-state="inprogress">
                    <?php foreach ($tasks['inprogress'] as $task): ?>
                        <div class="task-card" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <div class="task-header">
                                <h4 class="task-title"><?php echo htmlspecialchars($task['name']); ?></h4>
                                <div class="task-actions">
                                    <button class="edit-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($task['description'])): ?>
                                <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($task['assigned_username'])): ?>
                                <div class="task-user">
                                    <img src="<?php echo htmlspecialchars($task['assigned_photo']); ?>"
                                        alt="<?php echo htmlspecialchars($task['assigned_username']); ?>">
                                    <span><?php echo htmlspecialchars($task['assigned_username']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- COMPLETED Column -->
            <div class="kanban-column column-completed" data-state="Completed">
                <h3>
                    Completed
                    <span class="task-count"><?php echo count($tasks['Completed']); ?></span>
                </h3>
                <div class="task-list" data-state="Completed">
                    <?php foreach ($tasks['Completed'] as $task): ?>
                        <div class="task-card" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <div class="task-header">
                                <h4 class="task-title"><?php echo htmlspecialchars($task['name']); ?></h4>
                                <div class="task-actions">
                                    <button class="edit-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-task" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($task['description'])): ?>
                                <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($task['assigned_username'])): ?>
                                <div class="task-user">
                                    <img src="<?php echo htmlspecialchars($task['assigned_photo']); ?>"
                                        alt="<?php echo htmlspecialchars($task['assigned_username']); ?>">
                                    <span><?php echo htmlspecialchars($task['assigned_username']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Team Chat Column -->
            <div class="kanban-column column-chat">
                <h3>Team Chat</h3>
                <div class="messages-container" id="messagesContainer">
                    <div class="empty-messages" id="emptyMessages">
                        <i class="fas fa-comments"></i>
                        <h3>No messages yet</h3>
                        <p>Start the conversation with your team!</p>
                    </div>
                </div>
                <form id="messageForm" class="message-form">
                    <input type="hidden" name="projectId" value="<?php echo $projectId; ?>">
                    <textarea id="messageInput" class="message-input" placeholder="Type a message..." rows="1"></textarea>
                    <button type="submit" class="send-button" id="sendMessageBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>



    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h3 id="modalTitle">Add New Task</h3>
            <form id="taskForm">
                <input type="hidden" id="taskId" name="taskId" value="">
                <input type="hidden" id="projectId" name="projectId" value="<?php echo $projectId; ?>">

                <div class="form-group">
                    <label for="taskName">Task Name</label>
                    <input type="text" id="taskName" name="taskName" required>
                </div>

                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" name="taskDescription" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="taskState">Status</label>
                    <select id="taskState" name="taskState">
                        <option value="Pending">To Do</option>
                        <option value="inprogress">inprogress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assignedTo">Assign To</label>
                    <select id="assignedTo" name="assignedTo">
                        <option value="">-- Unassigned --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn" id="cancelTaskBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveTaskBtn">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeDeleteModal">&times;</span>
            <h3>Delete Task</h3>
            <p>Are you sure you want to delete this task? This action cannot be undone.</p>
            <input type="hidden" id="deleteTaskId">
            <div class="form-actions">
                <button type="button" class="btn" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Task</button>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        let draggedTask = null;

        // User dropdown functionality moved to DOMContentLoaded event

        // Create animated background circles
        function createCircles() {
            const movingCircles = document.getElementById('movingCircles');
            if (!movingCircles) return;

            const circleCount = 1;
            const colors = [
                'var(--primary-color)',
                'var(--secondary-color)',
                'var(--accent-color)'
            ];

            for (let i = 0; i < circleCount; i++) {
                const circle = document.createElement('div');
                circle.classList.add('circle');

                // Random position, size, and animation
                const size = Math.random() * 200 + 100;
                circle.style.width = `${size}px`;
                circle.style.height = `${size}px`;
                circle.style.left = `${Math.random() * 100}%`;
                circle.style.top = `${Math.random() * 100}%`;
                circle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                circle.style.animationDelay = `${Math.random() * 5}s`;
                circle.style.animationDuration = `${Math.random() * 10 + 15}s`;

                movingCircles.appendChild(circle);
            }
        }

        function updateCircleColors() {
            const circles = document.querySelectorAll('.circle');
            circles.forEach(circle => {
                const colors = [
                    'var(--primary-color)',
                    'var(--secondary-color)',
                    'var(--accent-color)'
                ];
                circle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            });
        }

        // Toast notification function
        function showToast(message, type = 'info', duration = 3000) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast';

            if (type === 'error') {
                toast.classList.add('error');
            }

            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
            }, duration);
        }
        // Drag and Drop functionality
        function setupDragAndDrop() {
            const taskCards = document.querySelectorAll('.task-card');
            const taskLists = document.querySelectorAll('.task-list');

            // Add event listeners to draggable items
            taskCards.forEach(taskCard => {
                taskCard.addEventListener('dragstart', function (e) {
                    draggedTask = this;
                    setTimeout(() => {
                        this.classList.add('dragging');
                    }, 0);
                });

                taskCard.addEventListener('dragend', function () {
                    this.classList.remove('dragging');
                    draggedTask = null;
                });
            });

            // Add event listeners to drop zones
            taskLists.forEach(taskList => {
                taskList.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    this.classList.add('drag-over');
                });

                taskList.addEventListener('dragleave', function () {
                    this.classList.remove('drag-over');
                });

                taskList.addEventListener('drop', function (e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');

                    if (draggedTask) {
                        const taskId = draggedTask.getAttribute('data-task-id');
                        const newState = this.getAttribute('data-state');
                        const oldState = draggedTask.parentElement.getAttribute('data-state');

                        // Only update if the state actually changed
                        if (newState !== oldState) {
                            this.appendChild(draggedTask);

                            // Log the states for debugging
                            console.log('TaskID:', taskId);
                            console.log('Old State:', oldState);
                            console.log('New State:', newState);

                            updateTaskState(taskId, newState);
                        }
                    }
                });
            });
        }

        function updateTaskState(taskId, newState) {
            // Update task state via AJAX
            const formData = new FormData();
            formData.append('taskId', taskId);
            formData.append('state', newState);
            formData.append('action', 'updateState');

            // Log the data being sent
            console.log('Sending data:', {
                taskId: taskId,
                state: newState,
                action: 'updateState'
            });

            fetch('../VIEW/task_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data);
                    if (data.success) {
                        showToast('Task updated successfully');
                        updateTaskCounts();
                    } else {
                        showToast('Error updating task: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error connecting to server', 'error');
                });
        }

        // Task Modal Functionality
        function setupTaskModals() {
            const addTaskBtn = document.getElementById('addTaskBtn');
            const taskModal = document.getElementById('taskModal');
            const closeModal = document.getElementById('closeModal');
            const cancelTaskBtn = document.getElementById('cancelTaskBtn');
            const taskForm = document.getElementById('taskForm');
            const modalTitle = document.getElementById('modalTitle');
            const deleteButtons = document.querySelectorAll('.delete-task');
            const editButtons = document.querySelectorAll('.edit-task');
            const deleteModal = document.getElementById('deleteModal');
            const closeDeleteModal = document.getElementById('closeDeleteModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            // Open Add Task Modal
            addTaskBtn.addEventListener('click', function () {
                taskForm.reset();
                document.getElementById('taskId').value = '';
                modalTitle.textContent = 'Add New Task';
                taskModal.style.display = 'block';
            });

            // Close Task Modal
            closeModal.addEventListener('click', function () {
                taskModal.style.display = 'none';
            });

            cancelTaskBtn.addEventListener('click', function () {
                taskModal.style.display = 'none';
            });

            // Handle Task Form Submit - fix the double submission issue
            let isSubmitting = false;

            taskForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Prevent double submission
                if (isSubmitting) return;
                isSubmitting = true;

                // Collect form values
                const taskId = document.getElementById('taskId').value;
                const projectId = document.getElementById('projectId').value;
                const taskName = document.getElementById('taskName').value;
                const taskDescription = document.getElementById('taskDescription').value;
                const taskState = document.getElementById('taskState').value;
                const assignedTo = document.getElementById('assignedTo').value;

                // FormData to send to server
                const formData = new FormData();
                formData.append('projectId', projectId);
                formData.append('name', taskName);
                formData.append('description', taskDescription);
                formData.append('state', taskState);
                formData.append('assignedTo', assignedTo);

                if (taskId) {
                    formData.append('taskId', taskId);
                    formData.append('action', 'updateTask');
                } else {
                    formData.append('action', 'addTask');
                }

                // Fetch to send data
                fetch('../VIEW/task_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        isSubmitting = false; // Reset submission flag
                        if (data.success) {
                            showToast(taskId ? 'Task updated successfully' : 'Task added successfully');

                            // Map backend state to frontend state
                            const stateMap = {
                                'Pending': 'todo',
                                'inprogress': 'in_progress',
                                'Completed': 'completed'
                            };

                            const frontendState = stateMap[data.task.state] || 'todo';

                            if (taskId) {
                                // Update existing task card
                                const taskCard = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                                if (taskCard) {
                                    // Get current column
                                    const currentColumn = taskCard.closest('.task-list');
                                    const currentState = currentColumn.getAttribute('data-state');

                                    // Update card content
                                    updateTaskCard(taskCard, {
                                        ...data.task,
                                        state: frontendState
                                    });

                                    // If state changed, move to new column
                                    if (currentState !== frontendState) {
                                        const newColumn = document.querySelector(`.task-list[data-state="${frontendState}"]`);
                                        if (newColumn) {
                                            newColumn.appendChild(taskCard);
                                        }
                                    }
                                }
                            } else {
                                // Create new task card with mapped state
                                createTaskCard({
                                    ...data.task,
                                    state: frontendState
                                });
                            }

                            updateTaskCounts();
                            taskModal.style.display = 'none'; // Close the modal on success
                        } else {
                            showToast(data.message || 'Error saving task');
                        }
                    })
                    .catch(error => {
                        isSubmitting = false; // Reset submission flag on error
                        console.error('Error:', error);
                        showToast('Error connecting to server');
                    });
            });

            // Edit Task Button Handlers
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    loadTaskDetails(taskId);
                });
            });

            // Delete Task Button Handlers
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const taskId = this.getAttribute('data-task-id');
                    document.getElementById('deleteTaskId').value = taskId;
                    deleteModal.style.display = 'block';
                });
            });

            // Close Delete Modal
            closeDeleteModal.addEventListener('click', function () {
                deleteModal.style.display = 'none';
            });

            cancelDeleteBtn.addEventListener('click', function () {
                deleteModal.style.display = 'none';
            });

            // Confirm Delete
            confirmDeleteBtn.addEventListener('click', function () {
                const taskId = document.getElementById('deleteTaskId').value;

                const formData = new FormData();
                formData.append('taskId', taskId);
                formData.append('action', 'deleteTask');

                fetch('../VIEW/task_ajax.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Task deleted successfully');
                            // Remove the task card from the DOM
                            const taskCard = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                            if (taskCard) {
                                taskCard.remove();
                                updateTaskCounts();
                            }
                        } else {
                            showToast(data.message || 'Error deleting task');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error connecting to server');
                    });

                deleteModal.style.display = 'none';
            });

            // Close modals when clicking outside
            window.addEventListener('click', function (e) {
                if (e.target === taskModal) {
                    taskModal.style.display = 'none';
                }
                if (e.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
        }

        // Load task details for editing
        function loadTaskDetails(taskId) {
            const formData = new FormData();
            formData.append('taskId', taskId);
            formData.append('action', 'getTaskDetails');

            fetch('task_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const task = data.task;

                        document.getElementById('taskId').value = task.id;
                        document.getElementById('taskName').value = task.name;
                        document.getElementById('taskDescription').value = task.description || '';
                        document.getElementById('taskState').value = task.state.toLowerCase();
                        document.getElementById('assignedTo').value = task.assigned_user_id || '';

                        document.getElementById('modalTitle').textContent = 'Edit Task';
                        document.getElementById('taskModal').style.display = 'block';
                    } else {
                        showToast(data.message || 'Error loading task details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error connecting to server');
                });
        }

        function updateTaskCounts() {
            // Update task counts in each column
            const columns = document.querySelectorAll('.kanban-column');

            columns.forEach(column => {
                const state = column.getAttribute('data-state');
                const taskList = column.querySelector('.task-list');

                // Add null check for taskList
                if (taskList) {
                    const taskCount = taskList.querySelectorAll('.task-card').length;

                    const countElement = column.querySelector('.task-count');
                    if (countElement) {
                        countElement.textContent = taskCount;
                    }
                } else {
                    console.warn('Task list not found for state:', state);
                }
            });
        }

        // Messages Panel Functions
        function setupMessagesPanel() {
            try {
                // Get all the elements we need
                const toggleMessagesBtn = document.getElementById('toggleMessagesBtn');
                const closeMessagesBtn = document.getElementById('closeMessagesBtn');
                const messagesPanel = document.getElementById('messagesPanel');

                // Set up the toggle button
                if (toggleMessagesBtn) {
                    toggleMessagesBtn.addEventListener('click', function() {
                        console.log('Toggle button clicked');

                        // Toggle the chat sidebar
                        document.body.classList.toggle('chat-open');

                        // If the chat is now open, load messages
                        if (document.body.classList.contains('chat-open')) {
                            if (typeof loadMessages === 'function') {
                                try {
                                    loadMessages();
                                } catch (e) {
                                    console.error('Error loading messages:', e);
                                }
                            }
                            showToast('Chat opened');
                        }
                    });
                }

                // Set up the close button
                if (closeMessagesBtn) {
                    closeMessagesBtn.addEventListener('click', function() {
                        document.body.classList.remove('chat-open');
                    });
                }

                // Set up the message form
                const messageForm = document.getElementById('messageForm');
                const messageInput = document.getElementById('messageInput');

                if (messageForm && messageInput) {
                    messageForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const content = messageInput.value.trim();
                        if (!content) return;

                        try {
                            sendMessage(content);
                        } catch (e) {
                            console.error('Error sending message:', e);
                            showToast('Error sending message', 'error');
                        }

                        messageInput.value = '';
                    });

                    // Auto-resize textarea
                    messageInput.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                }
            } catch (e) {
                console.error('Error setting up messages panel:', e);
            }
        }

        function loadMessages() {
            // Get project ID from the form's hidden input or from the page's hidden input
            const messageForm = document.getElementById('messageForm');
            const projectIdInput = messageForm.querySelector('input[name="projectId"]');
            const projectId = projectIdInput ? projectIdInput.value : document.getElementById('projectId').value;

            console.log('Loading messages for project ID:', projectId);

            const messagesContainer = document.getElementById('messagesContainer');
            const emptyMessages = document.getElementById('emptyMessages');

            fetch(`message_ajax.php?action=get_messages&projectId=${projectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages && data.messages.length > 0) {
                        emptyMessages.style.display = 'none';

                        // Clear existing messages
                        const existingMessages = messagesContainer.querySelectorAll('.message');
                        existingMessages.forEach(msg => msg.remove());

                        // Add messages
                        data.messages.forEach(message => {
                            addMessageToUI(message);
                        });

                        // Scroll to bottom
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    } else {
                        emptyMessages.style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    showToast('Error loading messages', 'error');
                });
        }

        function sendMessage(content) {
            // Get project ID from the form's hidden input or from the page's hidden input
            const messageForm = document.getElementById('messageForm');
            const projectIdInput = messageForm.querySelector('input[name="projectId"]');
            const projectId = projectIdInput ? projectIdInput.value : document.getElementById('projectId').value;

            console.log('Sending message with project ID:', projectId);

            const formData = new FormData();
            formData.append('action', 'add_message');
            formData.append('projectId', projectId);
            formData.append('content', content);

            // Clear the input field immediately for better UX
            document.getElementById('messageInput').value = '';

            fetch('message_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload messages to show the new message
                        loadMessages();
                        showToast('Message sent successfully');
                    } else {
                        showToast('Error sending message', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error sending message:', error);
                    showToast('Error sending message', 'error');
                });
        }

        function addMessageToUI(message) {
            const messagesContainer = document.getElementById('messagesContainer');
            const userId = <?php echo $_SESSION['user_id'] ?? 0; ?>;

            // Create message element
            const messageEl = document.createElement('div');
            messageEl.className = `message ${message.senderId == userId ? 'sent' : 'received'}`;

            // Format date
            const date = new Date(message.created_at);
            const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const formattedDate = date.toLocaleDateString();

            messageEl.innerHTML = `
                <img src="${message.photo}" alt="${message.username}" class="message-avatar">
                <div class="message-content">
                    <p class="message-text">${message.content}</p>
                    <div class="message-meta">
                        <span>${message.username}</span>
                        <span>${formattedTime} · ${formattedDate}</span>
                    </div>
                </div>
            `;

            messagesContainer.appendChild(messageEl);
        }

        // No need for markMessagesAsRead in the simple version

        function updateTaskCard(card, task) {
            card.querySelector('.task-title').textContent = task.name;

            // Handle description
            let descElement = card.querySelector('.task-description');
            if (task.description) {
                if (!descElement) {
                    descElement = document.createElement('div');
                    descElement.className = 'task-description';
                    const insertAfter = card.querySelector('.task-header');
                    insertAfter.parentNode.insertBefore(descElement, insertAfter.nextSibling);
                }
                descElement.textContent = task.description;
            } else if (descElement) {
                descElement.remove();
            }

            // Update assigned user
            let userElement = card.querySelector('.task-user');
            if (task.assigned_username) {
                if (!userElement) {
                    userElement = document.createElement('div');
                    userElement.className = 'task-user';
                    card.appendChild(userElement);
                }
                userElement.innerHTML = `
            <img src="${task.assigned_photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}"
                 alt="${task.assigned_username}">
            <span>${task.assigned_username}</span>
        `;
            } else if (userElement) {
                userElement.remove();
            }
        }

        function createTaskCard(task) {
            const state = task.state ? task.state.toLowerCase() : 'todo';
            const taskList = document.querySelector(`.task-list[data-state="${state}"]`);
            if (!taskList) return;

            const taskCard = document.createElement('div');
            taskCard.className = 'task-card';
            taskCard.draggable = true;
            taskCard.dataset.taskId = task.id;

            let userHtml = '';
            if (task.assigned_username) {
                userHtml = `
            <div class="task-user">
                <img src="${task.assigned_photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}"
                     alt="${task.assigned_username}">
                <span>${task.assigned_username}</span>
            </div>
        `;
            }

            let descHtml = '';
            if (task.description) {
                descHtml = `<div class="task-description">${task.description}</div>`;
            }

            taskCard.innerHTML = `
        <div class="task-header">
            <h4 class="task-title">${task.name}</h4>
            <div class="task-actions">
                <button class="edit-task" data-task-id="${task.id}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="delete-task" data-task-id="${task.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        ${descHtml}
        ${userHtml}
    `;

            taskList.prepend(taskCard);

            // Add event listeners to new buttons
            taskCard.querySelector('.edit-task').addEventListener('click', function () {
                loadTaskDetails(task.id);
            });

            taskCard.querySelector('.delete-task').addEventListener('click', function () {
                document.getElementById('deleteTaskId').value = task.id;
                document.getElementById('deleteModal').style.display = 'block';
            });

            // Add drag events
            taskCard.addEventListener('dragstart', function (e) {
                draggedTask = this;
                setTimeout(() => {
                    this.classList.add('dragging');
                }, 0);
            });

            taskCard.addEventListener('dragend', function () {
                this.classList.remove('dragging');
                draggedTask = null;
            });
        }

        // Initialize all required functionality
        document.addEventListener('DOMContentLoaded', () => {
            createCircles();
            setupDragAndDrop();
            setupTaskModals();
            updateTaskCounts();

            // Initialize theme toggle
            applyTheme();

            // Setup Join Call button
            const joinCallBtn = document.getElementById('joinCallBtn');
            if (joinCallBtn) {
                joinCallBtn.addEventListener('click', function() {
                    const projectId = document.getElementById('projectId').value;
                    // Redirect to the VOIP-WEB-APP with the project ID as a parameter
                    window.open(`../../VOIP-WEB-APP/index.html?projectId=${projectId}`, '_blank');
                });
            }

            // Setup user dropdown toggle
            const avatarImg = document.getElementById('avatarImg');
            const userDropdown = document.getElementById('userDropdown');

            if (avatarImg && userDropdown) {
                // Make sure dropdown is initially hidden
                userDropdown.classList.remove('active');

                // Add click event with stopPropagation
                avatarImg.addEventListener('click', function(event) {
                    event.stopPropagation();
                    console.log('Avatar clicked');
                    userDropdown.classList.toggle('active');
                });

                // Prevent clicks inside dropdown from closing it
                userDropdown.addEventListener('click', function(event) {
                    event.stopPropagation();
                });

                // Close dropdown when clicking anywhere else
                document.addEventListener('click', function(event) {
                    console.log('Document clicked');
                    userDropdown.classList.remove('active');
                });
            }

            // Setup message form
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');

            if (messageForm && messageInput) {
                messageForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const content = messageInput.value.trim();
                    if (!content) return;

                    sendMessage(content);
                    messageInput.value = '';
                });

                // Auto-resize textarea
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }

            // Load messages on page load
            loadMessages();
        });
    </script>
</body>

</html>
