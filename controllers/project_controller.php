<?php
// Include database connection
require_once 'db_connection.php';

// Handle action parameter if present
if (isset($_GET['action'])) {
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION["user_id"])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit();
    }

    $userId = $_SESSION["user_id"];
    $action = $_GET['action'];

    switch ($action) {
        case 'add_project':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $projectName = isset($_POST['projectName']) ? trim($_POST['projectName']) : '';
                $projectDescription = isset($_POST['projectDescription']) ? trim($_POST['projectDescription']) : '';
                $dueDate = isset($_POST['dueDate']) ? trim($_POST['dueDate']) : '';
                $memberIds = isset($_POST['memberIds']) ? json_decode($_POST['memberIds'], true) : [];

                $result = addProject($projectName, $projectDescription, $dueDate, $userId, $memberIds);

                if ($result['success']) {
                    header('Location: /KANDO/KANDO_MVC/VIEW/dashboard.php');
                } else {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                }
                exit();
            }
            break;

        case 'update_project':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Get JSON data from request body
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);

                if (!$data) {
                    // Try to get form data if JSON parsing failed
                    $projectId = isset($_POST['projectId']) ? intval($_POST['projectId']) : 0;
                    $projectName = isset($_POST['projectName']) ? trim($_POST['projectName']) : '';
                    $projectDescription = isset($_POST['projectDescription']) ? trim($_POST['projectDescription']) : '';
                    $dueDate = isset($_POST['dueDate']) ? trim($_POST['dueDate']) : '';
                    $memberIds = isset($_POST['memberIds']) ? json_decode($_POST['memberIds'], true) : [];
                } else {
                    // Use JSON data
                    $projectId = isset($data['id']) ? intval($data['id']) : 0;
                    $projectName = isset($data['name']) ? trim($data['name']) : '';
                    $projectDescription = isset($data['description']) ? trim($data['description']) : '';
                    $dueDate = isset($data['dueDate']) ? trim($data['dueDate']) : '';
                    $memberIds = isset($data['memberIds']) ? $data['memberIds'] : [];
                }

                $result = updateProject($projectId, $projectName, $projectDescription, $dueDate, $memberIds);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }
            break;

        case 'delete_project':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $projectId = isset($_POST['projectId']) ? intval($_POST['projectId']) : 0;

                $result = deleteProject($projectId, $userId);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }
            break;

        case 'get_project_details':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $projectId = isset($_GET['projectId']) ? intval($_GET['projectId']) : 0;

                $result = getProjectDetails($projectId);

                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }
            break;

        case 'search_users':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $term = isset($_GET['term']) ? trim($_GET['term']) : '';

                // Include user controller if not already included
                if (!function_exists('searchUsersByUsername')) {
                    require_once 'user_controller.php';
                }

                $users = searchUsersByUsername($term);

                header('Content-Type: application/json');
                echo json_encode($users);
                exit();
            }
            break;
    }
}

// Get projects by user ID
function getProjectsByUserId($userId) {
    $conn = getConnection();
    $created_projects = [];
    $collab_projects = [];

    // Get user's created projects
    $created_projects_query = "SELECT p.*,
        (SELECT COUNT(*) FROM Appartenir WHERE projectId = p.id) as member_count
        FROM Project p
        WHERE p.userId = ?";

    $stmt_created = $conn->prepare($created_projects_query);
    if ($stmt_created) {
        $stmt_created->bind_param("i", $userId);
        $stmt_created->execute();
        $result = $stmt_created->get_result();

        while ($row = $result->fetch_assoc()) {
            $created_projects[] = $row;
        }

        $stmt_created->close();
    }

    // Get projects the user is collaborating on (but didn't create)
    $collab_projects_query = "SELECT p.*,
        (SELECT COUNT(*) FROM Appartenir WHERE projectId = p.id) as member_count
        FROM Project p
        JOIN Appartenir pm ON p.id = pm.projectId
        WHERE pm.userId = ? AND p.userId != ?";

    $stmt_collab = $conn->prepare($collab_projects_query);
    if ($stmt_collab) {
        $stmt_collab->bind_param("ii", $userId, $userId);
        $stmt_collab->execute();
        $result = $stmt_collab->get_result();

        while ($row = $result->fetch_assoc()) {
            $collab_projects[] = $row;
        }

        $stmt_collab->close();
    }

    $conn->close();

    return [
        'created_projects' => $created_projects,
        'collab_projects' => $collab_projects
    ];
}

// Get project members
function getProjectMembers($projectId, $limit = 4) {
    $conn = getConnection();

    $members_query = "SELECT u.photo FROM user u
                      JOIN Appartenir pm ON u.id = pm.userId
                      WHERE pm.projectId = ? LIMIT ?";

    $stmt = $conn->prepare($members_query);
    if (!$stmt) {
        $conn->close();
        return [];
    }

    $stmt->bind_param("ii", $projectId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row['photo'] ? $row['photo'] : "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    }

    $stmt->close();
    $conn->close();

    return $members;
}

// Calculate project progress
function getProjectProgress($projectId) {
    $conn = getConnection();

    $progress_query = "SELECT
                      COUNT(CASE WHEN state = 'completed' THEN 1 END) as completed,
                      COUNT(*) as total
                      FROM task WHERE projectId = ?";

    $stmt = $conn->prepare($progress_query);
    if (!$stmt) {
        $conn->close();
        return 0;
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $progress = 0;
    if ($row && $row['total'] > 0) {
        $progress = ($row['completed'] / $row['total']) * 100;
    }

    $stmt->close();
    $conn->close();

    return round($progress);
}

// Add a new project
function addProject($name, $description, $dueDate, $userId, $memberIds = []) {
    $conn = getConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert project
        $stmt = $conn->prepare("INSERT INTO Project (name, description, userId, dueDate) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing project insert statement: " . $conn->error);
        }

        $stmt->bind_param("ssis", $name, $description, $userId, $dueDate);
        $stmt->execute();
        $projectId = $conn->insert_id;
        $stmt->close();

        // Add project owner to Appartenir table
        $stmt = $conn->prepare("INSERT INTO Appartenir (userId, projectId) VALUES (?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing owner insert statement: " . $conn->error);
        }

        $stmt->bind_param("ii", $userId, $projectId);
        $stmt->execute();
        $stmt->close();

        // Add team members if any
        if (!empty($memberIds)) {
            $stmt = $conn->prepare("INSERT INTO Appartenir (userId, projectId) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing member insert statement: " . $conn->error);
            }

            foreach ($memberIds as $memberId) {
                $stmt->bind_param("ii", $memberId, $projectId);
                $stmt->execute();
            }

            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        $conn->close();

        return ['success' => true, 'projectId' => $projectId];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->close();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Get project details
function getProjectDetails($projectId) {
    $conn = getConnection();

    // Get project info
    $stmt = $conn->prepare("SELECT * FROM Project WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();

    if (!$project) {
        $conn->close();
        return ['success' => false, 'message' => 'Project not found'];
    }

    // Get project members
    $stmt = $conn->prepare("SELECT u.id, u.username, u.photo
                           FROM user u
                           JOIN Appartenir a ON u.id = a.userId
                           WHERE a.projectId = ?");

    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($member = $result->fetch_assoc()) {
        $members[] = $member;
    }

    $stmt->close();
    $conn->close();

    return [
        'success' => true,
        'project' => $project,
        'members' => $members
    ];
}

// Update project
function updateProject($projectId, $name, $description, $dueDate, $memberIds = []) {
    $conn = getConnection();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update project
        $stmt = $conn->prepare("UPDATE Project SET name = ?, description = ?, dueDate = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing update statement: " . $conn->error);
        }

        $stmt->bind_param("sssi", $name, $description, $dueDate, $projectId);
        $stmt->execute();
        $stmt->close();

        // If memberIds provided, update members
        if (!empty($memberIds)) {
            // First, get the project owner
            $stmt = $conn->prepare("SELECT userId FROM Project WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error preparing owner select statement: " . $conn->error);
            }

            $stmt->bind_param("i", $projectId);
            $stmt->execute();
            $stmt->bind_result($ownerId);
            $stmt->fetch();
            $stmt->close();

            // Remove all existing members except owner
            $stmt = $conn->prepare("DELETE FROM Appartenir WHERE projectId = ? AND userId != ?");
            if (!$stmt) {
                throw new Exception("Error preparing delete statement: " . $conn->error);
            }

            $stmt->bind_param("ii", $projectId, $ownerId);
            $stmt->execute();
            $stmt->close();

            // Add new members
            $stmt = $conn->prepare("INSERT INTO Appartenir (userId, projectId) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing member insert statement: " . $conn->error);
            }

            foreach ($memberIds as $memberId) {
                // Skip if this is the owner
                if ($memberId == $ownerId) continue;

                $stmt->bind_param("ii", $memberId, $projectId);
                $stmt->execute();
            }

            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        $conn->close();

        return ['success' => true];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->close();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Delete project
function deleteProject($projectId, $userId) {
    $conn = getConnection();

    // Check if user is the owner
    $stmt = $conn->prepare("SELECT userId FROM Project WHERE id = ?");
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Error preparing statement'];
    }

    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $stmt->bind_result($ownerId);
    $stmt->fetch();
    $stmt->close();

    if ($ownerId != $userId) {
        $conn->close();
        return ['success' => false, 'message' => 'You do not have permission to delete this project'];
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete tasks
        $stmt = $conn->prepare("DELETE FROM task WHERE projectId = ?");
        if (!$stmt) {
            throw new Exception("Error preparing task delete statement: " . $conn->error);
        }

        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        // Delete members
        $stmt = $conn->prepare("DELETE FROM Appartenir WHERE projectId = ?");
        if (!$stmt) {
            throw new Exception("Error preparing member delete statement: " . $conn->error);
        }

        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        // Delete project
        $stmt = $conn->prepare("DELETE FROM Project WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparing project delete statement: " . $conn->error);
        }

        $stmt->bind_param("i", $projectId);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();
        $conn->close();

        return ['success' => true];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $conn->close();

        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Search users function moved to user_controller.php
