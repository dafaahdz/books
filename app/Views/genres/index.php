<?php $this->extend('layouts/main') ?>
<?php $this->section('title') ?>Data Genre<?php $this->endSection() ?>
<?php $this->section('active_menu') ?>get_defined_vars<?php $this->endSection() ?>
<?php $this->section('content') ?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">🏷️ Daftar Genre</span>
            <button id="btnAdd" class="btn btn-primary btn-sm">
                + Tambah Genre
            </button>
        </div>

        <div class="card-body">
            <table id="genresTable" class="table table-striped table-bordered w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Genre</th>
                        <th>Jumlah Buku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= view('genres/form_modal') ?>
<?= view('genres/books_modal') ?>

<?php $this->endSection() ?>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<?php $this->section('scripts') ?>
<script>
    let table;
    let booksTable;

    $(function() {
        table = $('#genresTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "<?= base_url('genres/datatables') ?>",
                type: "POST"
            },
            columns: [{
                    data: 'name'
                },
                {
                    data: 'book_count',
                    className: 'text-center',
                    render: data => data + ' buku'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(row) {
                        return `
                            <button class="btn btn-sm btn-info btn-view" data-id="${row.id}">
                                View
                            </button>
                            <button class="btn btn-sm btn-warning btn-edit" data-id="${row.id}">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                                Delete
                            </button>
                        `;
                    }
                }
            ]
        });

        // Add Genre
        $('#btnAdd').on('click', function() {
            $('#genreForm')[0].reset();
            $('#genreId').val('');
            $('#modalTitle').text('Tambah Genre');
            $('#genreModal').modal('show');
        });

        // Edit Genre
        $('#genresTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `<?= base_url('genres/show') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#genreId').val(res.id);
                    $('#genreName').val(res.name);
                    $('#modalTitle').text('Edit Genre');
                    $('#genreModal').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data genre');
                }
            });
        });

        // View Books by Genre
        $('#genresTable').on('click', '.btn-view', function() {
            const id = $(this).data('id');

            // Load genre name
            $.ajax({
                url: `<?= base_url('genres/show') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(genre) {
                    $('#booksModalTitle').text('Buku dengan Genre: ' + genre.name);

                    // Destroy existing DataTable if exists
                    if ($.fn.DataTable.isDataTable('#booksByGenreTable')) {
                        $('#booksByGenreTable').DataTable().destroy();
                    }

                    // Initialize DataTable for books
                    booksTable = $('#booksByGenreTable').DataTable({
                        processing: true,
                        responsive: true,
                        ajax: {
                            url: `<?= base_url('genres/books') ?>/${id}`,
                            dataSrc: ''
                        },
                        columns: [{
                                data: 'title'
                            },
                            {
                                data: 'author'
                            },
                            {
                                data: 'price',
                                className: 'text-end',
                                render: data => 'Rp ' + parseFloat(data).toLocaleString('id-ID')
                            }
                        ]
                    });

                    $('#booksModal').modal('show');
                }
            });
        });

        // Save Genre (Add/Update)
        $('#genreForm').on('submit', function(e) {
            e.preventDefault();

            const id = $('#genreId').val();
            const url = id ? "<?= base_url('genres/update') ?>" : "<?= base_url('genres/store') ?>";
            const data = id ? $(this).serialize() : {
                name: $('#genreName').val()
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#genreModal').modal('hide');
                        table.ajax.reload(null, false);
                    } else {
                        alert(res.message || 'Gagal menyimpan genre');
                    }
                },
                error: function() {
                    alert('Gagal menyimpan genre');
                }
            });
        });

        // Delete Genre
        $('#genresTable').on('click', '.btn-delete', function() {
            const id = $(this).data('id');

            if (!confirm('Yakin mau hapus genre ini?')) return;

            $.ajax({
                url: '<?= base_url('genres/delete') ?>',
                method: 'POST',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        table.ajax.reload(null, false);
                    } else {
                        alert(res.message || 'Gagal menghapus genre');
                    }
                },
                error: function() {
                    alert('Gagal menghapus genre');
                }
            });
        });
    });
</script>
<?php $this->endSection() ?>