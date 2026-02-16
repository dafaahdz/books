<?php
$currentUrl = current_url();
$isBooks = strpos($currentUrl, 'books') != false;
$isGenres = strpos($currentUrl, 'genres') != false;
?>

<div class="sidebar">
    <a href="<?= base_url('/') ?>" class="<?= $isBooks ? 'active' :  '' ?>">Buku</a>
    <a href="<?= base_url('/genres') ?>" class="<?= $isGenres ? 'active' :  '' ?>">Genre</a>
</div>