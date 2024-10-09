<?php
$project_dir = "D:\FlutterProjects";
$output = [];
$result_code = 0;
exec('flutter create ' . escapeshellarg($project_dir) . ' 2>&1', $output, $result_code);

echo "<pre>";
echo "Command Output:\n";
print_r($output); // Display the command output
echo "\nResult Code: $result_code";
echo "</pre>";

if ($result_code !== 0) {
    echo 'Failed to create new Flutter project.';
}
?>
