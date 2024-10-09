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
$pages_directory = "projects/{$active_project['id']}_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $active_project['name']) . "/lib/pages/";
$pages = [];

// Fetch existing pages
if (is_dir($pages_directory)) {
    $files = scandir($pages_directory);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'dart') {
            $pages[] = pathinfo($file, PATHINFO_FILENAME);
        }
    }
}

// Handle save menu action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the order of pages from the form
    $ordered_pages = isset($_POST['pages']) ? json_decode($_POST['pages'], true) : [];

    // Create or update menu file
    $menu_file = "projects/{$active_project['id']}_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $active_project['name']) . "/lib/menu.dart";
    
    // Prepare the menu content
    $menu_content = "import 'package:flutter/material.dart';\n\n";
    $menu_content .= "class AppMenu {\n";
    $menu_content .= "  static List<Map<String, String>> items = [\n";
    
    foreach ($ordered_pages as $page) {
        $menu_content .= "    {'name': '$page', 'route': '/$page'},\n";
    }
    
    $menu_content .= "  ];\n";
    $menu_content .= "}\n";

    // Save the menu file without overwriting existing folders or files
    if (file_exists($menu_file)) {
        // If the menu file exists, update its contents
        file_put_contents($menu_file, $menu_content);
    } else {
        // If it doesn't exist, create it
        file_put_contents($menu_file, $menu_content);
    }

    // Refresh the page to reflect changes
    header("Location: menu_builder.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .sortable { list-style-type: none; padding: 0; }
        .sortable li { margin: 10px; padding: 15px; border: 1px solid #ccc; background-color: #f9f9f9; cursor: move; }
        .card { margin: 10px; }
    </style>
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
                            <h1 class="m-0">Menu Builder</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <h3>Drag and Drop Pages</h3>
                            <ul id="sortable" class="sortable">
                                <?php foreach ($pages as $page): ?>
                                    <li class="card" data-page="<?php echo htmlspecialchars($page); ?>">
                                        <div class="card-body">
                                            <?php echo htmlspecialchars($page); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button id="saveMenu" class="btn btn-success">Save Menu</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
     <?php include 'templates/footer.php'; ?>
         <!-- AdminLTE Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>                               
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            // Make the list sortable
            $("#sortable").sortable();
            $("#sortable").disableSelection();

            // Save button functionality
            $("#saveMenu").on('click', function() {
                const orderedPages = [];
                $("#sortable li").each(function() {
                    orderedPages.push($(this).data('page'));
                });

                // Send the ordered pages to the server
                $.post("menu_builder.php", { pages: JSON.stringify(orderedPages) }, function(response) {
                    // Reload the page after saving
                    location.reload();
                });
            });
        });
    </script>
</body>
</html>
