<?php
// session_start();

// Load projects
$projects_file = 'data/projects.json';
$projects = file_exists($projects_file) ? json_decode(file_get_contents($projects_file), true) : [];
$active_project = null;

// Find the active project
foreach ($projects as $project) {
    if ($project['status'] === 'active') {
        $active_project = $project;
        break;
    }
}
?>

<!-- Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
        <span class="brand-text font-weight-light">My Dashboard</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if ($active_project): ?>
                    <li class="nav-item">
                        <a href="page_builder.php?id=<?php echo $active_project['id']; ?>" class="nav-link">
                        <i class="nav-icon fas fa-file"></i>
                            <p>Page Builder</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="menu_builder.php?id=<?php echo $active_project['id']; ?>" class="nav-link">
                        <i class="nav-icon fas fa-bars"></i>
                            <p>Menu Builder</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="delete_project.php?id=<?php echo $active_project['id']; ?>" class="nav-link">
                            <i class="nav-icon fas fa-plug"></i>
                            <p>Plugins</p>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
