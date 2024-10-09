<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the project ID to delete
if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);
    
    // Load projects from JSON file
    $projects_file = 'data/projects.json';
    $projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];

    // Find the project in the array and remove it
    foreach ($projects as $key => $project) {
        if ($project['id'] === $project_id) {
            // Remove the project folder
            $project_folder = "projects/{$project_id}_". preg_replace('/[^a-zA-Z0-9_]/', '_', $project['name']);
            if (is_dir($project_folder)) {
                // Function to delete folder and its contents
                function deleteDir($dir) {
                    if (!is_dir($dir)) {
                        return;
                    }
                    $objects = scandir($dir);
                    foreach ($objects as $object) {
                        if ($object != "." && $object != "..") {
                            if (is_dir($dir . "/" . $object)) {
                                deleteDir($dir . "/" . $object);
                            } else {
                                unlink($dir . "/" . $object);
                            }
                        }
                    }
                    rmdir($dir);
                }

                // Delete the project folder
                deleteDir($project_folder);
            }

            // Remove the project from the array
            unset($projects[$key]);
            break;
        }
    }

    // Re-index array and save updated projects to the JSON file
    $projects = array_values($projects);
    file_put_contents($projects_file, json_encode($projects));

    // Redirect to dashboard after deletion
    header("Location: dashboard.php");
    exit();
}
?>
