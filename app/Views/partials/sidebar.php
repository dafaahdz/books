<?php $activeMenu = $this->renderSection('active_menu', true) ?? ''; ?>

<div class="sidebar">
    <a href="<?= base_url('/') ?>" class="<?= $activeMenu === 'books' ? 'active' :  '' ?>">Buku</a>
    <a href="<?= base_url('/genres') ?>" class="<?= $activeMenu === 'genres' ? 'active' :  '' ?>">Genre</a>
    <a href="<?= base_url('/files') ?>" class="<?= $activeMenu === 'files' ? 'active' :  '' ?>">Files</a>
</div>