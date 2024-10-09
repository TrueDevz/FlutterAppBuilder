<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Load active project details
$projects_file = 'data/projects.json';
$projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];
$active_project = null;

foreach ($projects as $project) {
    if ($project['status'] === 'active') {
        $active_project = $project;
        break;
    }
}

// If no active project, redirect back
if (!$active_project) {
    header("Location: dashboard.php");
    exit();
}

// Define the pages directory within the activated Flutter project
$pages_dir = "projects/{$active_project['id']}_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $active_project['name']) . "/lib/pages/";

// Ensure the pages directory exists
if (!is_dir($pages_dir)) {
    mkdir($pages_dir, 0777, true); // Create it if it doesn't exist
}

// Load existing pages (Dart files) from the lib/pages/ directory
$pages = array_diff(scandir($pages_dir), array('.', '..'));

// Handle form submission to add or edit a page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $_POST['page_name']);
    $appbar_color = $_POST['appbar_color'];
    $edit_page = isset($_POST['edit_page']) ? $_POST['edit_page'] : null;

    // Create or edit page Dart file content
    $page_content = <<<EOD
import 'package:flutter/material.dart';

class {$page_name}Page extends StatelessWidget {
    @override
    Widget build(BuildContext context) {
        return Scaffold(
            appBar: AppBar(
                title: Text('$page_name'),
                backgroundColor: Color(0x$appbar_color), // Convert hex to Color
            ),
            body: Center(
                child: Text('This is the $page_name page.'),
            ),
        );
    }
}
EOD;

    // Save new or edited page
    if ($edit_page) {
        // Update existing file
        file_put_contents($pages_dir . $edit_page, $page_content);
    } else {
        // Save new page
        file_put_contents($pages_dir . $page_name . '_page.dart', $page_content);
    }

    // Refresh the page to reflect the updated or added page
    header("Location: page_builder.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_page'])) {
    $delete_page = $_GET['delete_page'];
    $file_to_delete = $pages_dir . $delete_page;
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
    // Refresh after deletion
    header("Location: page_builder.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Include navigation -->
        <?php include 'templates/navigation.php'; ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Page Builder</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">
                        <!-- Loop through existing pages and display them in cards -->
                        <?php foreach ($pages as $page): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo str_replace('_page.dart', '', $page); ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <p>Page file: <?php echo $page; ?></p>
                                    </div>
                                    <div class="card-footer">
                                        <!-- Edit Button (Opens Edit Modal) -->
                                        <button class="btn btn-warning edit-button" data-toggle="modal" data-target="#editPageModal" data-page-name="<?php echo str_replace('_page.dart', '', $page); ?>" data-page-file="<?php echo $page; ?>">Edit</button>
                                        <!-- Delete Button (Triggers delete) -->
                                        <a href="page_builder.php?delete_page=<?php echo $page; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this page?');">Delete</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Add New Page Card -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Add New Page</h3>
                                </div>
                                <div class="card-body">
                                    <form action="page_builder.php" method="POST">
                                        <div class="form-group">
                                            <label for="page_name">Page Name</label>
                                            <input type="text" name="page_name" id="page_name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="appbar_color">AppBar Color</label>
                                            <input type="text" name="appbar_color" id="appbar_color" class="form-control" required placeholder="Enter Hex (e.g., 42A5F5)">
                                        </div>
                                        <button type="submit" class="btn btn-success">Save Page</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Page Modal -->
    <div class="modal fade" id="editPageModal" tabindex="-1" role="dialog" aria-labelledby="editPageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPageModalLabel">Edit Page</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="page_builder.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="edit_page" id="edit_page_name">
                        <div class="form-group">
                            <label for="page_name">Page Name</label>
                            <input type="text" name="page_name" id="edit_page_input" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="appbar_color">AppBar Color</label>
                            <input type="text" name="appbar_color" id="edit_appbar_color" class="form-control" required placeholder="Enter Hex or MaterialColor code">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- AdminLTE Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set modal input values when opening edit modal
            $('#editPageModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var pageName = button.data('page-name'); // Extract info from data-* attributes
                var pageFile = button.data('page-file'); // Extract page file name
                var modal = $(this);
                modal.find('#edit_page_name').val(pageFile); // Use the filename for editing
                modal.find('#edit_page_input').val(pageName);
                modal.find('#edit_appbar_color').val('#42A5F5'); // Default color; update as necessary
            });
        });
    </script>
</body>
</html>
