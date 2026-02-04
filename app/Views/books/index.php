<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <span class="fw-semibold">📚 Daftar Buku</span>

            <div class="d-flex gap-2 align-items-center">
                <select id="filterGenre" class="form-select form-select-sm" style="min-width: 180px">
                    <option value="">-- Semua Genre --</option>
                </select>

                <button id="btnExportPdf" class="btn btn-danger btn-sm">
                    ⬇ Export PDF
                </button>

                <button id="btnExportCsv" class="btn btn-warning btn-sm">
                    ⬇ Export CSV
                </button>

                <button id="btnAdd" class="btn btn-primary btn-sm">
                    + Tambah Buku
                </button>
            </div>
        </div>


        <div class="card-body">
            <table id="booksTable" class="table table-striped table-bordered w-100">
                <thead class="table-dark">
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Genre</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= view('books/form_modal') ?>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables core -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- DataTables Bootstrap 5 -->
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>

let table



    
$(function () {

loadGenres(null, '#filterGenre');

    table = $('#booksTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "<?= base_url('books/datatables') ?>",
            type: "POST",
            data: function (d) {
                d.genre_id = $('#filterGenre').val();
            }
        },
        columns: [
            { data: 'title' },
            { data: 'author' },
            { data: 'genre_name' },
            { 
                data: 'price',
                className: 'text-end',
                render: data => 'Rp ' + parseFloat(data).toLocaleString('id-ID')
            },
            {
                data:null,
                orderable: false,
                searchable: false,
                render: function (row) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit" data-id="${row.id}">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}">
                            Delete
                        </button>
                    `
                }
            }
        ]
    });

    $('#btnAdd').on('click', function () {
        $('#bookForm')[0].reset();
        $('#bookId').val('');
        $('#modalTitle').text('Tambah Buku');
        $('#bookModal').modal('show');
        loadGenres();
    });

    $('#booksTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');

        $.get(`<?= base_url('books/show') ?>/${id}`, function (res) {
            $('#bookId').val(res.id);
            $('#title').val(res.title);
            $('#author').val(res.author);
            $('#price').val(res.price);

            loadGenres(res.genre_id);

            $('#modalTitle').text('Edit Buku');
            $('#bookModal').modal('show')
        })
    } )

    $('#bookForm').on('submit', function (e) {

        e.preventDefault();

        const id = $('#bookId').val();
        const url = id
            ? "<?= base_url('books/update') ?>"
            : "<?= base_url('books/store') ?>";

        $.post(url, $(this).serialize(), function () {
            $('#bookModal').modal('hide');
            table.ajax.reload(null, false);
        })
    })

    $('#btnAddGenre').on('click', function () {
        $('#addGenreWrapper').toggleClass('d-none');
        $('#newGenreName').focus();
    });

    $('#saveGenreBtn').on('click', function () {
        const name = $('#newGenreName').val().trim();

        if(!name) {
            alert('Nama genre wajib diisi');
            return
        }


        $.ajax({
            url: "<?= base_url('genres/store') ?>",
            method: "POST",
            data: { name },
            dataType: "json",
            success: function (res) {
                $('#newGenreName').val('');
                $('#addGenreWrapper').addClass('d-none');


                loadGenres(res.id);
            }
        })
    })
});

$('#btnExportPdf').on('click', function () {
    const genreId = $('#filterGenre').val();

    let url = "<?= base_url('books/export-pdf') ?>";

    if (genreId) {
        url += '?genre_id=' + genreId;
    }

    window.open(url, '_blank');
});

$('#btnExportCsv').on('click', function () {
    const genreId = $('#filterGenre').val();

    let url = "<?= base_url('books/export-csv') ?>";

    if (genreId) {
        url += '?genre_id=' + genreId;
    }

    window.open(url, '_blank');
});


function loadGenres(selectedId = null, target = '#genreSelect') {
    $.get("<?= base_url('genres/list') ?>", function (res) {
        const select = $(target);
        select.empty().append('<option value="">-- Semua Genre --</option>');

        res.forEach(g => {
            const selected = selectedId == g.id ? 'selected' : '';
            select.append(`<option value="${g.id}" ${selected}>${g.name}</option>`)
        })
    })
}

$('#booksTable').on('click', '.btn-delete', function () {
    const id = $(this).data('id');

    if(!confirm('Yakin mau hapus buku ini?')) return;

    $.post('<?= base_url('books/delete') ?>', {id}, function () {
        table.ajax.reload(null, false)
    })
})

$('#filterGenre').on('change', function () {
    table.ajax.reload()
})
</script>


</body>
</html>
