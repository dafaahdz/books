<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">Sistem Manajemen Buku</span>
        <div class="d-flex">
            <span class="navbar-text  text-light me-3"><?= session()->get('username') ?></span>
            <a href="<?= base_url('/logout') ?>" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>