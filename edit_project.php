<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Load projects from JSON
$projects_file = 'data/projects.json';
$projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];

// Get the project ID from the URL
if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);
    $project_to_edit = null;

    // Find the project to edit
    foreach ($projects as $project) {
        if ($project['id'] === $project_id) {
            $project_to_edit = $project;
            break;
        }
    }

    // If form is submitted, update the project details
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updated_name = $_POST['name'];
        $updated_description = $_POST['description'];

        // Update project details
        foreach ($projects as &$project) {
            if ($project['id'] === $project_id) {
                $project['name'] = $updated_name;
                $project['description'] = $updated_description;
                break;
            }
        }

        // Save updated projects back to JSON
        file_put_contents($projects_file, json_encode($projects));

        // Redirect back to dashboard
        header("Location: dashboard.php");
        exit();
    }
} else {
    // If no project ID is provided, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

?>

<!-- HTML for the Edit Project form -->
<?php include 'templates/header.php'; ?>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Include navigation -->
        <?php include 'templates/navigation.php'; ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Edit Project</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <?php if ($project_to_edit): ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="name">Project Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($project_to_edit['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Project Description</label>
                                <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($project_to_edit['description']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php else: ?>
                        <p>Project not found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
