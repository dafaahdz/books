<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
        }
        .select2-selection__rendered {
            line-height: 38px;
        }
        .select2-selection__arrow {
            height: 38px;
        }

    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <span class="fw-semibold">📚 Daftar Buku</span>

            <div class="d-flex gap-2 align-items-center">
                <select name="genre" id="genre" class="form-select" style="min-width: 200px">
                    <option value=""></option>
                </select>

                <button id="btnExportExcel" class="btn btn-success btn-sm">
                    ⬇ Export Excel
                </button>

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
        <div class="d-flex align-items-center gap-2 mt-3 mb-2 mx-3 d-none" id="exportWrapper">
            <div class="progress flex-grow-1" style="height: 28px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated fw-bold" id="exportStatus"
                style="width:0%">0%</div>
            </div>

            <button
                type="button"
                id="btnCancelExport"
                class="btn btn-outline-danger btn-sm">
                X Cancel
            </button>
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

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables core -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- DataTables Bootstrap 5 -->
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>

let table
let exportTotal = 0
let exportCanceled = false
let exportBtn = null
let exportBtnHtml = ''


    
$(function () {
    $('#genre').select2({
        placeholder: 'Pilih genre',
        width: '100%',
        allowClear: true,
        ajax: {
            url: '<?= base_url('genres/search') ?>',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    term: params.term || ''
                }
            },
            processResults: function(data) {
                return {
                    results: data
                };
            }
        }
    });

    $('#bookModal').on('hidden.bs.modal', function () {
        $('#genreSelect').select2('destroy');
    });

    table = $('#booksTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "<?= base_url('books/datatables') ?>",
            type: "POST",
            data: function (d) {
                d.genre_id = $('#genre').val();
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
    });

    $('#booksTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');

        $.get(`<?= base_url('books/show') ?>/${id}`, function (res) {
            $('#bookId').val(res.id);
            $('#title').val(res.title);
            $('#author').val(res.author);
            $('#price').val(res.price);
            $('#modalTitle').text('Edit Buku');

            $('#bookModal').data('editGenreId', res.genre_id);
            $('#bookModal').data('editGenreName', res.genre_name);

            $('#bookModal').modal('show');
        });
    });

    $('#bookModal').on('shown.bs.modal', function () {
        const genreId = $(this).data('editGenreId');
        const genreName = $(this).data('editGenreName');

        $('#genreSelect').select2({
            dropdownParent: $('#bookModal'),
            width: '100%',
            placeholder: 'Pilih genre',
            allowClear: true,
            ajax: {
                url: '<?= base_url('genres/search') ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term || ''
                    }
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
        });

        if (genreId && genreName) {
            const option = new Option(genreName, genreId, true, true);
            $('#genreSelect').append(option).trigger('change');
        }

        $(this).removeData('editGenreId').removeData('editGenreName');
    });

    $('#bookModal').on('hidden.bs.modal', function () {
        try {
            if ($('#genreSelect').hasClass('select2-hidden-accessible')) {
                $('#genreSelect').select2('destroy');
            }
        } catch (e) {}
        $('#bookForm')[0].reset();
    });


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

            }
        })
    })
});

$('#btnExportPdf').on('click', function () {
    const genreId = $('#genre').val();

    let url = "<?= base_url('books/export-pdf') ?>";

    if (genreId) {
        url += '?genre_id=' + genreId;
    }

    window.open(url, '_blank');
});

$('#btnExportCsv').on('click', function () {
    const genreId = $('#genre').val();

    let url = "<?= base_url('books/export-csv') ?>";

    if (genreId) {
        url += '?genre_id=' + genreId;
    }

    window.open(url, '_blank');
});

$('#btnExportExcel').on('click', function () {
    exportCanceled = false
    exportBtn = $(this)
    const genreId = $('#genre').val()
    exportBtnHtml = exportBtn.html()
    
    exportBtn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-1"></span> Exporting data...')
    
    let url = "<?= base_url('books/export-init') ?>"
    if(genreId) {
        url += '?genre_id=' + genreId
    }

    $('#exportWrapper').removeClass('d-none')
    $('#exportStatus').removeClass('d-none')
    $('#btnExportExcel').prop('disabled', true)

    updateProgress(0)

    $.post(url, function (res) {
        exportTotal = res.total
        processChunk()
    })
})

$('#btnCancelExport').on('click', function () {
    if(!confirm('Batalkan proses export?')) return
    
    exportCanceled = true

    $.post("<?= base_url('books/export-reset') ?>", function () {
        $('#exportWrapper').addClass('d-none')
        updateProgress(0)

        $('#btnExportExcel').prop('disabled', false)

    }).always(() => {
        exportBtn.prop('disabled', false)
            .html(exportBtnHtml)
    })
})

function processChunk() {

    
    $.post("<?= base_url('books/export-chunk') ?>", function (res) {
        
        if(exportCanceled) return

        if(res.done) {
            updateProgress(100);

            setTimeout(() => {
                $('#exportWrapper').addClass('d-none');
                $('#exportStatus').addClass('d-none');
                updateProgress(0);
                $('#btnExportExcel').prop('disabled', false)
                                    .html(exportBtnHtml);
                window.location = "<?= base_url('books/export-download') ?>"
            }, 800);

            return;
        }

        let percent = (res.processed / res.total) * 100;
        percent = Math.min(percent, 95);

        updateProgress(percent)
        setTimeout(processChunk, 300)
    })
}

function updateProgress(percent) {
    $('#exportWrapper .progress-bar')
        .css('width', percent + '%')
        .text(Math.floor(percent) + '%');
}

$('#booksTable').on('click', '.btn-delete', function () {
    const id = $(this).data('id');

    if(!confirm('Yakin mau hapus buku ini?')) return;

    $.post('<?= base_url('books/delete') ?>', {id}, function () {
        table.ajax.reload(null, false)
    })
})

$('#genre').on('change.select2', function () {
    table.ajax.reload()
})

window.addEventListener('beforeunload', function () {
    navigator.sendBeacon(
        "<?= base_url('books/export-reset') ?>"
    );
});


</script>


</body>
</html>
