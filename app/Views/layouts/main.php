<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Sistem Manajemen Buku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            flex-grow: 1;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: white;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #495057;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            background: #f8f9fa;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>
    <?= view('partials/header') ?>
    <div class="wrapper">
        <?= view('partials/sidebar') ?>
        <main class="content">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
    <?= view('partials/footer') ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>