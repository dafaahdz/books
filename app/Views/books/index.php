<?php $this->extend('layouts/main') ?>
<?php $this->section('title') ?>Data Buku<?php $this->endSection() ?>
<?php $this->section('content') ?>

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

<?php $this->endSection() ?>


<?php $this->section('scripts') ?>
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



    $(function() {
        $('#genre').select2({
            placeholder: 'Pilih genre',
            width: '100%',
            allowClear: true,
            ajax: {
                url: '<?= base_url('genres/search') ?>',
                dataType: 'json',
                delay: 250,
                data: function(params) {
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

        $('#bookModal').on('hidden.bs.modal', function() {
            $('#genreSelect').select2('destroy');
        });

        table = $('#booksTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "<?= base_url('books/datatables') ?>",
                type: "POST",
                data: function(d) {
                    d.genre_id = $('#genre').val();
                }
            },
            columns: [{
                    data: 'title'
                },
                {
                    data: 'author'
                },
                {
                    data: 'genre_name'
                },
                {
                    data: 'price',
                    className: 'text-end',
                    render: data => 'Rp ' + parseFloat(data).toLocaleString('id-ID')
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(row) {
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

        $('#btnAdd').on('click', function() {
            $('#bookForm')[0].reset();
            $('#bookId').val('');
            $('#modalTitle').text('Tambah Buku');
            $('#bookModal').modal('show');
        });

        $('#booksTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `<?= base_url('books/show') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#bookId').val(res.id);
                    $('#title').val(res.title);
                    $('#author').val(res.author);
                    $('#price').val(res.price);
                    $('#modalTitle').text('Edit Buku');

                    $('#bookModal').data('editGenreId', res.genre_id);
                    $('#bookModal').data('editGenreName', res.genre_name);

                    $('#bookModal').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data buku')
                }
            });
        });

        $('#bookModal').on('shown.bs.modal', function() {
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
                    data: function(params) {
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

        $('#bookModal').on('hidden.bs.modal', function() {
            try {
                if ($('#genreSelect').hasClass('select2-hidden-accessible')) {
                    $('#genreSelect').select2('destroy');
                }
            } catch (e) {}
            $('#bookForm')[0].reset();
        });


        $('#bookForm').on('submit', function(e) {

            e.preventDefault();

            const id = $('#bookId').val();
            const url = id ?
                "<?= base_url('books/update') ?>" :
                "<?= base_url('books/store') ?>";

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function() {
                    $('#bookModal').modal('hide');
                    table.ajax.reload(null, false);
                },
                error: function() {
                    alert('Gagal menyimpan data buku');
                }
            })
        })

        $('#btnAddGenre').on('click', function() {
            $('#addGenreWrapper').toggleClass('d-none');
            $('#newGenreName').focus();
        });

        $('#saveGenreBtn').on('click', function() {
            const name = $('#newGenreName').val().trim();

            if (!name) {
                alert('Nama genre wajib diisi');
                return
            }


            $.ajax({
                url: "<?= base_url('genres/store') ?>",
                method: "POST",
                data: {
                    name
                },
                dataType: "json",
                success: function(res) {
                    $('#newGenreName').val('');
                    $('#addGenreWrapper').addClass('d-none');

                }
            })
        })
    });

    $('#btnExportPdf').on('click', function() {
        const genreId = $('#genre').val();

        let url = "<?= base_url('books/export-pdf') ?>";

        if (genreId) {
            url += '?genre_id=' + genreId;
        }

        window.open(url, '_blank');
    });

    $('#btnExportCsv').on('click', function() {
        const genreId = $('#genre').val();

        let url = "<?= base_url('books/export-csv') ?>";

        if (genreId) {
            url += '?genre_id=' + genreId;
        }

        window.open(url, '_blank');
    });

    $('#btnExportExcel').on('click', function() {
        exportCanceled = false
        exportBtn = $(this)
        const genreId = $('#genre').val()
        exportBtnHtml = exportBtn.html()

        exportBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span> Exporting data...')

        let url = "<?= base_url('books/export-init') ?>"
        if (genreId) {
            url += '?genre_id=' + genreId
        }

        $('#exportWrapper').removeClass('d-none')
        $('#exportStatus').removeClass('d-none')
        $('#btnExportExcel').prop('disabled', true)

        updateExportProgress(0)

        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            success: function(res) {
                exportTotal = res.total
                processChunk()
            },
            error: function() {
                alert('Gagal memulai export');
            }
        })
    })

    $('#btnCancelExport').on('click', function() {
        if (!confirm('Batalkan proses export?')) return

        exportCanceled = true

        $.ajax({
            url: "<?= base_url('books/export-reset') ?>",
            method: 'POST',
            success: function() {
                $('#exportWrapper').addClass('d-none')
                updateProgress(0)

                $('#btnExportExcel').prop('disabled', false)
            },
            error: function() {
                alert('Gagal membatalkan export');
            }
        }).always(() => {
            exportBtn.prop('disabled', false)
                .html(exportBtnHtml)
        })
    })

    function processChunk() {
        $.ajax({
            url: "<?= base_url('books/export-chunk') ?>",
            method: 'POST',
            dataType: 'json',
            success: function(res) {

                if (exportCanceled) return

                if (res.done) {
                    updateExportProgress(100);

                    setTimeout(() => {
                        $('#exportWrapper').addClass('d-none');
                        $('#exportStatus').addClass('d-none');
                        updateExportProgress(0);
                        $('#btnExportExcel').prop('disabled', false)
                            .html(exportBtnHtml);
                        window.location = "<?= base_url('books/export-download') ?>"
                    }, 800);

                    return;
                }

                let percent = (res.processed / res.total) * 100;
                percent = Math.min(percent, 95);

                updateExportProgress(percent)
                setTimeout(processChunk, 300)
            },
            error: function() {
                alert('Error saat memproses export');
                $('#btnExportExcel')
                    .prop('disabled', false)
                    .html(exportBtnHtml);
            }
        })
    }

    function updateExportProgress(percent) {
        $('#exportWrapper .progress-bar')
            .css('width', percent + '%')
            .text(Math.floor(percent) + '%');
    }

    $('#booksTable').on('click', '.btn-delete', function() {
        const id = $(this).data('id');

        if (!confirm('Yakin mau hapus buku ini?')) return;

        $.ajax({
            url: '<?= base_url('books/delete') ?>',
            method: 'POST',
            data: {
                id
            },
            success: function() {
                table.ajax.reload(null, false)
            }
        })
    })

    $('#genre').on('change.select2', function() {
        table.ajax.reload()
    })

    window.addEventListener('beforeunload', function() {
        navigator.sendBeacon(
            "<?= base_url('books/export-reset') ?>"
        );
    });
</script>

<!-- Import Excel Functions -->
<script>
    function downloadTemplate() {
        window.open('<?= base_url('books/download-template') ?>', '_blank');
    }

    async function startImport() {
        const fileInput = document.getElementById('excelFile');
        const file = fileInput.files[0];

        if (!file) {
            alert('Pilih file Excel terlebih dahulu');
            return;
        }

        try {
            // Reset any existing import state first
            await resetImportState();

            const formData = new FormData();
            formData.append('file', file);

            // Reset progress UI
            document.getElementById('importProgress').classList.remove('d-none');
            document.getElementById('importSection').querySelectorAll('button:not([onclick])').forEach(btn => btn.disabled = true);
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressText').textContent = 'Mempersiapkan...';

            // Start import
            const response = await fetch('<?= base_url('books/import-init') ?>', {
                method: 'POST',
                body: formData
            });

            let result;
            try {
                result = await response.json();
            } catch (e) {
                const responseText = await response.text();
                throw new Error(`Server returned non-JSON response: ${responseText.substring(0, 200)}`);
            }

            if (response.status !== 200) {
                throw new Error(result.error || `HTTP ${response.status}: Gagal memulai import`);
            }

            // Initialize progress
            if (result.total > 0) {
                updateImportProgress(0);
            } else {
                document.getElementById('progressText').textContent = 'Tidak ada data untuk diimport';
                document.getElementById('importProgress').classList.add('d-none');
                return;
            }

            // Process chunks
            processImportChunks(result.total);

        } catch (error) {
            alert(error.message);
            resetImportForm();
        }
    }

    async function resetImportState() {
        try {
            await fetch('<?= base_url('books/import-reset') ?>', {
                method: 'POST'
            });
        } catch (error) {
            // Silently handle reset errors
        }
    }

    function cancelImport() {
        if (confirm('Batalkan proses import?')) {
            resetImportState();
            resetImportForm();
        }
    }

    function processImportChunks(total) {
        // Make sure progress is visible before processing
        document.getElementById('importProgress').classList.remove('d-none');
        updateImportProgress(0);

        function processChunk() {
            fetch('<?= base_url('books/import-chunk') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.done) {
                        // Always show 100% progress even if all data skipped
                        updateImportProgress(100);

                        setTimeout(() => {
                            // Show accurate summary
                            const skipped = result.total - result.processed;
                            let message = `Import selesai! ${result.processed} data berhasil diimport`;
                            if (skipped > 0) {
                                message += `, ${skipped} data dilewati (invalid/genre tidak ditemukan)`;
                            }
                            alert(message);

                            // Refresh ONLY DataTable
                            if (typeof table !== 'undefined' && typeof table.ajax.reload === 'function') {
                                table.ajax.reload(null, false);
                            }

                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('bookModal'));
                            if (modal) {
                                modal.hide();
                            }

                            resetImportForm();
                        }, 800);

                        return;
                    }

                    // Calculate progress based on total rows processed, not just inserted
                    let percent = (result.processed / result.total) * 100;

                    // If all skipped but processed count is 0, show minimal progress
                    if (result.processed === 0 && result.total > 0) {
                        percent = 10; // Show some progress to indicate processing happened
                    }

                    percent = Math.min(percent, 95);

                    updateImportProgress(percent);
                    setTimeout(processChunk, 300);
                })
                .catch(error => {
                    alert('Error saat import: ' + error.message);
                    resetImportForm();
                });
        }

        processChunk();
    }

    function updateImportProgress(percent) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');

        if (!progressBar || !progressText) {
            return;
        }

        progressBar.style.width = percent + '%';
        progressText.textContent = Math.floor(percent) + '%';
    }

    function resetImportForm() {
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importSection').querySelectorAll('button').forEach(btn => btn.disabled = false);
        document.getElementById('excelFile').value = '';
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 0 data';
    }
</script>
<?php $this->endSection() ?>