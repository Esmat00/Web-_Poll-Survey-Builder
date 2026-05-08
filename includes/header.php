<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . " - Poll & Survey Builder" : "Poll & Survey Builder" ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="logo" href="index.php">Poll Builder</a>

            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="create.php">Create Poll</a>
                <a href="admin.php">Manage Polls</a>
            </div>
        </nav>
    </header>
    <div class="animated-bg">
        <span class="bg-shape shape-poll"></span>
        <span class="bg-shape shape-check"></span>
        <span class="bg-shape shape-bars">
            <i></i><i></i><i></i>
        </span>
        <span class="bg-shape shape-card"></span>
        <span class="bg-shape shape-dot"></span>
    </div>
    <main class="main-content">