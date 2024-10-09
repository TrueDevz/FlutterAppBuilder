<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Path to the projects JSON file
$projects_file = 'data/projects.json';

// Load projects from the JSON file
$projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];

// Handle project creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $app_name = escapeshellcmd($_POST['app_name']); // Escaping for security
    $description = escapeshellcmd($_POST['description']);

    // Generate a valid Flutter project name (remove spaces and special characters)
    $valid_app_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $app_name);
    $project_id = count($projects) + 1; // Unique project ID based on the count
    $project_dir = "projects/{$project_id}_{$valid_app_name}"; // Directory for the new project

    // Run the flutter create command to create the new Flutter project
    $output = [];
    $result_code = 0;
    exec("flutter create $project_dir", $output, $result_code);

    // Check if the flutter create command was successful
    if ($result_code !== 0) {
        die('Failed to create new Flutter project. Please check your Flutter installation and try again.');
    }
    // Add the new project to the projects array
    $projects[] = [
        'id' => $project_id,
        'name' => $app_name,
        'description' => $description,
        'status' => 'inactive'  // All new projects start as inactive
    ];

    // Save the updated projects array to the JSON file
    file_put_contents($projects_file, json_encode($projects, JSON_PRETTY_PRINT));

    // Redirect to refresh the page
    header("Location: dashboard.php");
    exit();
}

// Handle project activation
if (isset($_GET['activate_id'])) {
    $activate_id = $_GET['activate_id'];

    // Mark the selected project as active and others as inactive
    foreach ($projects as &$project) {
        $project['status'] = ($project['id'] == $activate_id) ? 'active' : 'inactive';
    }

    // Save the updated projects back to the JSON file
    file_put_contents($projects_file, json_encode($projects, JSON_PRETTY_PRINT));

    // Redirect to refresh the page
    header("Location: dashboard.php");
    exit();
}
?>

<?php include 'templates/header.php'; ?>
<?php include 'templates/navigation.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard | Flutter Automation Builder</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Card for creating a new project -->
                <div class="col-lg-4 col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Create New Project</h3>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-plus-circle fa-3x mb-3"></i>
                            <p>Start creating your new Flutter project here.</p>
                            <button class="btn btn-success" data-toggle="modal" data-target="#createProjectModal">
                                <i class="fas fa-plus"></i> New Project
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Loop through generated projects -->
                <?php foreach ($projects as $project): ?>
                    <div class="col-lg-4 col-md-6 project-card">
                        <div class="card <?php echo $project['status'] == 'active' ? 'card-success' : 'card-secondary'; ?>">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h3>
                            </div>
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($project['description']); ?></p>
                                <p>Status: <strong><?php echo ucfirst($project['status']); ?></strong></p>
                            </div>
                            <div class="card-footer">
                                <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                                <a href="?activate_id=<?php echo $project['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-toggle-on"></i> Activate
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modal for creating a new project -->
<div class="modal fade" id="createProjectModal" tabindex="-1" role="dialog" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="dashboard.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel">Create New Flutter Project</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="app_name">Project Name</label>
                        <input type="text" class="form-control" id="app_name" name="app_name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="create_project" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
