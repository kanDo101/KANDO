<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to login page if not logged in
    header("Location: /KANDO/KANDO_MVC/VIEW/auth/signup.php");
    exit();
}

// Include project controller
require_once '../controllers/project_controller.php';

// Get user information
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$fullname = isset($_SESSION["fullname"]) ? $_SESSION["fullname"] : $username;
$email = isset($_SESSION["email"]) ? $_SESSION["email"] : "";

// Handle photo with fallback
$photo = "https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png";
if (isset($_SESSION["photo"]) && !empty($_SESSION["photo"])) {
    $photo = $_SESSION["photo"];
}

// Get user's projects using the controller functions
$projects = getProjectsByUserId($user_id);
$created_projects = $projects['created_projects'];
$collab_projects = $projects['collab_projects'];

// Get the current theme preference
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kando - Kanban Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">

    <script src="../public/js/toggle.js"></script>

</head>

<body>
    <div class="moving-circles" id="movingCircles"></div>

    <nav class="navbar" id="navbar">
        <a href="./auth/landing.html">
            <div class="logo"><i class="fas fa-tasks"></i> Kando</div>
        </a>
        <div class="nav-links">
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

    <div class="container">
        <div class="projects-container">
            <div class="project-header">
                <h2 class="section-title">My Projects</h2>
                <button class="add-project-btn" id="addProjectBtn">
                    <i class="fas fa-plus"></i> Add New Project
                </button>
            </div>

            <div class="project-grid">
                <?php if (!empty($created_projects)): ?>
                    <?php foreach ($created_projects as $project): ?>
                        <?php
                        $Appartenir = getProjectMembers($project['id']);
                        $progress = getProjectProgress($project['id']);
                        $color = $progress < 30 ? 'var(--accent-color)' : ($progress < 70 ? 'var(--primary-color)' : 'var(--secondary-color)');
                        ?>
                        <div class="project-card">
                            <div class="project-actions">
                                <button class="edit-project" data-project-id="<?php echo $project['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="delete-project" data-project-id="<?php echo $project['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="project-content"
                                onclick="window.location='project.php?projectId=<?php echo $project['id']; ?>'">
                                <h3 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                                <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                                <div class="project-meta">
                                    <div class="project-members">
                                        <?php foreach ($Appartenir as $member): ?>
                                            <img src="<?php echo htmlspecialchars($member); ?>" alt="Team Member"
                                                class="project-member">
                                        <?php endforeach; ?>
                                        <?php if ($project['member_count'] > 4): ?>
                                            <span style="margin-left: 5px;">+<?php echo $project['member_count'] - 4; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span>Due: <?php echo date('M d', strtotime($project['dueDate'])); ?></span>
                                </div>
                                <div class="project-progress">
                                    <div class="progress-bar"
                                        style="width: <?php echo $progress; ?>%; background-color: <?php echo $color; ?>;">
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-plus"></i>
                        <h3>No Projects Yet</h3>
                        <p>Create your first project to get started</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="projects-container">
            <h2 class="section-title">Collaborations</h2>
            <div class="project-grid">
                <?php if (!empty($collab_projects)): ?>
                    <?php foreach ($collab_projects as $project): ?>
                        <?php
                        $Appartenir = getProjectMembers($project['id']);
                        $progress = getProjectProgress($project['id']);
                        $color = $progress < 30 ? 'var(--accent-color)' : ($progress < 70 ? 'var(--primary-color)' : 'var(--secondary-color)');
                        ?>
                        <div class="project-card"
                            onclick="window.location='project.php?projectId=<?php echo $project['id']; ?>'">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                            <p class="project-description"><?php echo htmlspecialchars($project['description']); ?></p>
                            <div class="project-meta">
                                <div class="project-members">
                                    <?php foreach ($Appartenir as $member): ?>
                                        <img src="<?php echo htmlspecialchars($member); ?>" alt="Team Member"
                                            class="project-member">
                                    <?php endforeach; ?>
                                    <?php if ($project['member_count'] > 4): ?>
                                        <span style="margin-left: 5px;">+<?php echo $project['member_count'] - 4; ?></span>
                                    <?php endif; ?>
                                </div>
                                <span>Due: <?php echo date('M d', strtotime($project['dueDate'])); ?></span>
                            </div>
                            <div class="project-progress">
                                <div class="progress-bar"
                                    style="width: <?php echo $progress; ?>%; background-color: <?php echo $color; ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Collaborations Yet</h3>
                        <p>You haven't been added to any projects</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Add Project Modal -->
    <div id="projectModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Create New Project</h2>

            <form id="addProjectForm" method="POST" action="../controllers/project_controller.php?action=add_project">
                <div class="form-group">
                    <label for="projectName">Project Name</label>
                    <input type="text" id="projectName" name="projectName" required>
                </div>

                <div class="form-group">
                    <label for="projectDescription">Description</label>
                    <textarea id="projectDescription" name="projectDescription" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="dueDate">Due Date</label>
                    <input type="date" id="dueDate" name="dueDate" required>
                </div>

                <div class="form-group">
                    <label for="teamMembers">Team Members</label>
                    <div class="selected-members" id="selectedMembers"></div>
                    <input type="text" id="memberSearch" placeholder="Search users...">
                    <div id="searchResults" class="search-results"></div>
                    <input type="hidden" id="memberIds" name="memberIds">
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
                    <button type="submit" class="submit-btn">Create Project</button>
                </div>
            </form>
        </div>
    </div>
<div id="editProjectModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeEditModal">&times;</span>
        <h2>Edit Project</h2>

        <form id="editProjectForm" method="POST" action="../controllers/project_controller.php?action=update_project">
            <input type="hidden" id="editProjectId" name="projectId">
            <div class="form-group">
                <label for="editProjectName">Project Name</label>
                <input type="text" id="editProjectName" name="projectName" required>
            </div>

            <div class="form-group">
                <label for="editProjectDescription">Description</label>
                <textarea id="editProjectDescription" name="projectDescription" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="editDueDate">Due Date</label>
                <input type="date" id="editDueDate" name="dueDate" required>
            </div>

            <div class="form-group">
                <label for="editTeamMembers">Team Members</label>
                <div class="selected-members" id="editSelectedMembers"></div>
                <input type="text" id="editMemberSearch" placeholder="Search users...">
                <div id="editSearchResults" class="search-results"></div>
                <input type="hidden" id="editMemberIds" name="memberIds">
            </div>

            <div class="form-actions">
                <button type="button" class="cancel-btn" id="cancelEditBtn">Cancel</button>
                <button type="submit" class="submit-btn">Update Project</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteConfirmation" class="delete-confirmation">
    <div class="delete-confirmation-content">
        <h3>Delete Project</h3>
        <p>Are you sure you want to delete this project? This action cannot be undone.</p>
        <input type="hidden" id="deleteProjectId" value="">
        <div class="delete-confirmation-buttons">
            <button class="cancel-delete" id="cancelDeleteBtn">Cancel</button>
            <button class="confirm-delete" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

    <script>
        // Project Edit and Delete functionality
const editProjectModal = document.getElementById('editProjectModal');
const closeEditModal = document.getElementById('closeEditModal');
const cancelEditBtn = document.getElementById('cancelEditBtn');
const editProjectForm = document.getElementById('editProjectForm');
const deleteConfirmation = document.getElementById('deleteConfirmation');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
// Add this to your existing script section
document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const projectId = document.getElementById('editProjectId').value;
    const projectName = document.getElementById('editProjectName').value;
    const projectDescription = document.getElementById('editProjectDescription').value;
    const dueDate = document.getElementById('editDueDate').value;

    // Prepare data in the format expected by update_project.php
    const data = {
        id: parseInt(projectId),
        name: projectName,
        description: projectDescription,
        dueDate: dueDate
    };

    // Send as JSON
    fetch('../controllers/project_controller.php?action=update_project', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Close modal and reload page to see changes
            closeEditProjectModal();
            window.location.reload();
        } else {
            alert(result.message || 'Failed to update project');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the project');
    });
});

// Setup edit buttons
document.querySelectorAll('.edit-project').forEach(button => {
    button.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the click from propagating to the project card
        const projectId = button.getAttribute('data-project-id');
        loadProjectDetails(projectId);
    });
});

// Setup delete buttons
document.querySelectorAll('.delete-project').forEach(button => {
    button.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent the click from propagating to the project card
        const projectId = button.getAttribute('data-project-id');
        document.getElementById('deleteProjectId').value = projectId;
        deleteConfirmation.style.display = 'flex';
    });
});

// Close edit modal
function closeEditProjectModal() {
    editProjectModal.style.display = 'none';
    // Reset form
    document.getElementById('editProjectForm').reset();
    document.getElementById('editSelectedMembers').innerHTML = '';
    document.getElementById('editMemberIds').value = '';
}

closeEditModal.addEventListener('click', closeEditProjectModal);
cancelEditBtn.addEventListener('click', closeEditProjectModal);

// Close edit modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === editProjectModal) {
        closeEditProjectModal();
    }
    if (e.target === deleteConfirmation) {
        deleteConfirmation.style.display = 'none';
    }
});

// Close delete confirmation
cancelDeleteBtn.addEventListener('click', () => {
    deleteConfirmation.style.display = 'none';
});

// Confirm delete
confirmDeleteBtn.addEventListener('click', () => {
    const projectId = document.getElementById('deleteProjectId').value;
    if (projectId) {
        // Send delete request
        fetch('../controllers/project_controller.php?action=delete_project', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `projectId=${projectId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show the updated project list
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting project');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while trying to delete the project');
        });
    }
    deleteConfirmation.style.display = 'none';
});

// Load project details for editing
function loadProjectDetails(projectId) {
    fetch(`../controllers/project_controller.php?action=get_project_details&projectId=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate form fields
                document.getElementById('editProjectId').value = data.project.id;
                document.getElementById('editProjectName').value = data.project.name;
                document.getElementById('editProjectDescription').value = data.project.description || '';
                document.getElementById('editDueDate').value = data.project.dueDate;

                // Clear existing members
                document.getElementById('editSelectedMembers').innerHTML = '';
                const selectedMembersContainer = document.getElementById('editSelectedMembers');
                let editSelectedMemberIds = [];

                // Add team members
                if (data.members && data.members.length > 0) {
                    data.members.forEach(member => {
                        editSelectedMemberIds.push(member.id);

                        const memberElement = document.createElement('div');
                        memberElement.className = 'selected-member';
                        memberElement.innerHTML = `
                            <img src="${member.photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}" alt="${member.username}">
                            <span>${member.username}</span>
                            <span class="remove-member" data-id="${member.id}">×</span>
                        `;

                        memberElement.querySelector('.remove-member').addEventListener('click', function() {
                            const userId = parseInt(this.getAttribute('data-id'));
                            editSelectedMemberIds = editSelectedMemberIds.filter(id => id !== userId);
                            document.getElementById('editMemberIds').value = JSON.stringify(editSelectedMemberIds);
                            memberElement.remove();
                        });

                        selectedMembersContainer.appendChild(memberElement);
                    });

                    document.getElementById('editMemberIds').value = JSON.stringify(editSelectedMemberIds);
                }

                // Show the modal
                editProjectModal.style.display = 'flex';

                // Setup member search for edit modal
                setupEditMemberSearch(editSelectedMemberIds);
            } else {
                alert(data.message || 'Error loading project details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while trying to load project details');
        });
}

// Setup member search for edit modal
function setupEditMemberSearch(selectedIds) {
    const memberSearch = document.getElementById('editMemberSearch');
    const searchResults = document.getElementById('editSearchResults');
    const selectedMembers = document.getElementById('editSelectedMembers');
    const memberIdsInput = document.getElementById('editMemberIds');
    let editSelectedMemberIds = selectedIds || [];

    memberSearch.addEventListener('input', () => {
        const searchTerm = memberSearch.value.trim();

        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        // Fetch users that match search term
        fetch(`../controllers/project_controller.php?action=search_users&term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(users => {
                if (users.length > 0) {
                    searchResults.innerHTML = '';
                    users.forEach(user => {
                        // Skip if already selected
                        if (editSelectedMemberIds.includes(user.id)) return;

                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <img src="${user.photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}" alt="${user.username}">
                            <span>${user.username}</span>
                        `;

                        item.addEventListener('click', () => {
                            addEditTeamMember(user, editSelectedMemberIds, memberIdsInput, selectedMembers);
                            searchResults.style.display = 'none';
                            memberSearch.value = '';
                        });

                        searchResults.appendChild(item);
                    });
                    searchResults.style.display = 'block';
                } else {
                    searchResults.innerHTML = '<div class="search-result-item">No users found</div>';
                    searchResults.style.display = 'block';
                }
            });
    });
}

function addEditTeamMember(user, selectedMemberIds, memberIdsInput, selectedMembers) {
    if (selectedMemberIds.includes(user.id)) return;

    selectedMemberIds.push(user.id);
    memberIdsInput.value = JSON.stringify(selectedMemberIds);

    const memberElement = document.createElement('div');
    memberElement.className = 'selected-member';
    memberElement.innerHTML = `
        <img src="${user.photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}" alt="${user.username}">
        <span>${user.username}</span>
        <span class="remove-member" data-id="${user.id}">×</span>
    `;

    memberElement.querySelector('.remove-member').addEventListener('click', function() {
        const userId = parseInt(this.getAttribute('data-id'));
        const newSelectedIds = selectedMemberIds.filter(id => id !== userId);
        // Update the original array by reference
        selectedMemberIds.length = 0;
        newSelectedIds.forEach(id => selectedMemberIds.push(id));
        memberIdsInput.value = JSON.stringify(selectedMemberIds);
        memberElement.remove();
    });

    selectedMembers.appendChild(memberElement);
}

        // Modal functionality
        const modal = document.getElementById('projectModal');
        const addProjectBtn = document.getElementById('addProjectBtn');
        const closeModal = document.querySelector('.close-modal');
        const cancelBtn = document.getElementById('cancelBtn');

        addProjectBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
        });

        function closeProjectModal() {
            modal.style.display = 'none';
            // Reset form
            document.getElementById('addProjectForm').reset();
            document.getElementById('selectedMembers').innerHTML = '';
            document.getElementById('memberIds').value = '';
        }

        closeModal.addEventListener('click', closeProjectModal);
        cancelBtn.addEventListener('click', closeProjectModal);

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeProjectModal();
            }
        });

        // Team member search functionality
        const memberSearch = document.getElementById('memberSearch');
        const searchResults = document.getElementById('searchResults');
        const selectedMembers = document.getElementById('selectedMembers');
        const memberIdsInput = document.getElementById('memberIds');
        let selectedMemberIds = [];

        memberSearch.addEventListener('input', () => {
            const searchTerm = memberSearch.value.trim();

            if (searchTerm.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            // Fetch users that match search term
            fetch(`../controllers/project_controller.php?action=search_users&term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(users => {
                    if (users.length > 0) {
                        searchResults.innerHTML = '';
                        users.forEach(user => {
                            // Skip if already selected
                            if (selectedMemberIds.includes(user.id)) return;

                            const item = document.createElement('div');
                            item.className = 'search-result-item';
                            item.innerHTML = `
                        <img src="${user.photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}" alt="${user.username}">
                        <span>${user.username}</span>
                    `;

                            item.addEventListener('click', () => {
                                addTeamMember(user);
                                searchResults.style.display = 'none';
                                memberSearch.value = '';
                            });

                            searchResults.appendChild(item);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.innerHTML = '<div class="search-result-item">No users found</div>';
                        searchResults.style.display = 'block';
                    }
                });
        });

        function addTeamMember(user) {
            if (selectedMemberIds.includes(user.id)) return;

            selectedMemberIds.push(user.id);
            memberIdsInput.value = JSON.stringify(selectedMemberIds);

            const memberElement = document.createElement('div');
            memberElement.className = 'selected-member';
            memberElement.innerHTML = `
        <img src="${user.photo || 'https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg'}" alt="${user.username}">
        <span>${user.username}</span>
        <span class="remove-member" data-id="${user.id}">×</span>
    `;

            memberElement.querySelector('.remove-member').addEventListener('click', function () {
                const userId = parseInt(this.getAttribute('data-id'));
                selectedMemberIds = selectedMemberIds.filter(id => id !== userId);
                memberIdsInput.value = JSON.stringify(selectedMemberIds);
                memberElement.remove();
            });

            selectedMembers.appendChild(memberElement);
        }

        // Avatar dropdown functionality
        const avatarImg = document.getElementById('avatarImg');
        const userDropdown = document.getElementById('userDropdown');

        avatarImg.addEventListener('click', () => {
            userDropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!avatarImg.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // Create animated background circles
        function createCircles() {
            const movingCircles = document.getElementById('movingCircles');
            const circleCount = 5;
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

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', () => {
            createCircles();
        });
    </script>
</body>

</html>

