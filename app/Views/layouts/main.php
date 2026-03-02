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
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script>
        function showAlert(options) {
            const defaults = {
                title: 'Peringatan',
                text: '',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            };
            return Swal.fire({
                ...defaults,
                ...options
            });
        }

        function showSuccess(text, title = 'Berhasil') {
            return showAlert({
                title: title,
                text: text,
                icon: 'success'
            });
        }

        function showError(text, title = 'Gagal') {
            return showAlert({
                title: title,
                text: text,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }

        function showWarning(text, title = 'Peringatan') {
            return showAlert({
                title: title,
                text: text,
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
        }

        function showConfirm(options) {
            const defaults = {
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d'
            };
            return Swal.fire({
                ...defaults,
                ...options
            });
        }
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>