<?php $this->extend('layouts/main') ?>
<?php $this->section('title') ?>Data File<?php $this->endSection() ?>
<?php $this->section('active_menu') ?>files<?php $this->endSection() ?>
<?php $this->section('content') ?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <span class="fw-semibold">Daftar File</span>
            <button class="btn btn-primary btn-sm" id="btnAdd">
                + Tambah File
            </button>
        </div>

        <div class="card-body">
            <table id="filesTable" class="table table-striped table-bordered w-100">
                <thead class="table-dark">
                    <tr>
                        <th>File Name</th>
                        <th>Created At</th>
                        <th>Created By</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= view('files/form_modal') ?>

<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

<script>
    let table;

    $(function() {
        table = $('#filesTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "<?= base_url('files/list') ?>",
                type: 'POST'
            },
            columns: [{
                    data: 'filerealname'
                },
                {
                    data: 'created_at',
                    render: function(data) {
                        if (!data) return '-';
                        const date = new Date(data);
                        return date.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    }
                },
                {
                    data: 'created_by_name'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(row) {
                        return `
                            <a href="<?= base_url('files/download') ?>/${row.fileid}" class="btn btn-sm btn-info text-white btn-view">
                                View
                            </a>
                            <button class="btn btn-sm btn-warning btn-edit" data-id="${row.fileid}">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="${row.fileid}">
                                Delete
                            </button>
                        `
                    }
                }
            ]
        })

        Dropzone.autoDiscover = false

        $('#btnAdd').on('click', function() {
            $('#fileModal').modal('show')
        });

        $('#btnCloseModal, #btnTutup').on('click', function() {
            $('#fileModal').modal('hide');
        });

        $('#fileModal').on('hide.bs.modal', function(e) {
            const dropzone = window.dropzoneAdd
            if (!dropzone) return true;

            const isUploading = dropzone.getUploadingFiles().length > 0
            const hasQueued = dropzone.getQueuedFiles().length > 0

            if (isUploading || hasQueued) {
                e.preventDefault()
                showWarning('Mohon tunggu hingga upload selesai')
                return false
            }

            const uploadedFiles = window.uploadedFiles || [];

            if (uploadedFiles.length > 0) {
                e.preventDefault();

                showConfirm({
                    title: 'Perubahan belum disimpan!',
                    text: 'Apakah kamu yakin membuang perubahan? File yang diupload akan dihapus.',
                    confirmButtonText: 'Ya, tutup',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.uploadedFiles.forEach(function(file) {
                            $.ajax({
                                url: '<?= base_url('files/cleanup-upload') ?>',
                                method: 'POST',
                                data: {
                                    uploadId: file.uploadId
                                },
                                async: false
                            });
                        });

                        window.uploadedFiles = [];
                        dropzone.removeAllFiles();
                        $('#btnSimpan').prop('disabled', true);
                        $('#fileModal').modal('hide');
                    }
                });

                return false;
            }

            window.dropzoneAdd?.removeAllFiles();
            window.uploadedFiles = [];
            $('#btnSimpan').prop('disabled', true);
            $('#modalTitle').text('Tambah File');
        });

        initDropzoneAdd()

        $('#filesTable').on('click', '.btn-edit', function() {
            const id = $(this).data('id')

            $.ajax({
                url: `<?= base_url('files/show') ?>/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#fileId').val(res.fileid);
                    $('#filerealname').val(res.filerealname);
                    $('#currentFileName').text(res.filerealname);
                    $('#currentFilePath').val(res.filedirectory + '/' + res.filename);
                    $('#modalTitle').text('Edit File');
                    $('#fileModal').modal('show');
                },
                error: function() {
                    showError('Gagal mengambil data file')
                }
            })
        })

        $('#fileModal').on('shown.bs.modal', function() {
            const modalTitle = $('#modalTitle').text()
            if (modalTitle === 'Tambah File') {
                $('#btnSimpan').prop('disabled', true);
                window.uploadedFiles = [];
            }
            if (modalTitle === 'Edit File' && !window.dropzoneEdit) {
                initDropzoneEdit()
            }
        })

        $('#fileForm').on('submit', function(e) {
            e.preventDefault()

            const id = $('#fileId').val()
            const url = id ? "<?= base_url('files/update') ?>" : "<?= base_url('files/update') ?>";

            const formData = new FormData(this)

            if (id) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#fileModal').modal('hide')
                        table.ajax.reload(null, false)
                    },
                    error: function() {
                        showError('Gagal menyimpan data file')
                    }
                })
            } else {
                if (window.dropzoneAdd && window.dropzoneAdd.getQueuedFiles().length > 0) {
                    window.dropzoneAdd.processQueue()
                }
            }
        })

        $('#filesTable').on('click', '.btn-delete', function() {
            const id = $(this).data('id')

            if (!confirm('Yakin mau hapus file ini?')) return;

            $.ajax({
                url: '<?= base_url('files/delete') ?>',
                method: 'POST',
                data: {
                    fileid: id
                },
                success: function() {
                    table.ajax.reload(null, false);
                },
                error: function() {
                    showError('Gagal menghapus file');
                }
            })
        })
    })

    $('#btnSimpan').on('click', function() {
        if (window.dropzoneAdd) {
            const isUploading = window.dropzoneAdd.getUploadingFiles()
            const hasQueued = window.dropzoneAdd.getQueuedFiles()

            if (isUploading.length > 0 || hasQueued.length > 0) {
                showWarning('Tunggu semua file selesai diupload')
                return
            }
        }

        if (!window.uploadedFiles || window.uploadedFiles.length === 0) {
            showWarning('Upload filenya dulu')
            return
        }

        $.ajax({
            url: '<?= base_url('files/save-files') ?>',
            method: 'POST',
            data: {
                files: JSON.stringify(window.uploadedFiles)
            },
            success: function() {
                window.uploadedFiles = []
                $('#fileModal').modal('hide')
                table.ajax.reload(null, false)
                window.dropzoneAdd.removeAllFiles()
                $('#btnSimpan').prop('disabled', true)
            },
            error: function(jqXHR) {
                const msg = jqXHR.responseJSON?.pesan || 'Gagal menyimpan file';
                showError(msg)
            }
        })
    })

    function initDropzoneAdd() {

        if (Dropzone.forElement('#myDropzone')) {
            Dropzone.forElement('#myDropzone').destroy()
        }

        window.dropzoneAdd = new Dropzone('#myDropzone', {
            url: "<?= base_url('files/chunk-upload') ?>",
            chunking: true,
            chunkSize: 5 * 1024 * 1024,
            maxFilesize: 1000,
            addRemoveLinks: true,
            dictDefaultMessage: 'Drop file di sini atau klik untuk upload',
            params: function(files, xhr, chunk) {
                const fileName = files && files.length > 0 ? files[0].name : '';
                const uploadId = chunk ? chunk.file.upload.uuid : files[0].upload.uuid;

                if (chunk) {
                    return {
                        uploadId: uploadId,
                        chunkIndex: chunk.index,
                        totalChunks: chunk.file.upload.totalChunkCount,
                        originalName: fileName
                    }
                }
                return {
                    uploadId: uploadId,
                    originalName: fileName
                }
            },
            init: function() {
                this.on('addfile', function(file) {
                    isUploading = true;
                });

                this.on('success', function(file, response) {
                    if (!window.uploadedFiles) {
                        window.uploadedFiles = []
                    }

                    window.uploadedFiles.push({
                        tempPath: response.tempPath,
                        originalname: response.originalname,
                        uploadId: response.uploadId
                    });

                    const queued = window.dropzoneAdd.getQueuedFiles();
                    const uploading = window.dropzoneAdd.getUploadingFiles();
                    if (queued.length === 0 && uploading.length === 0) {
                        isUploading = false;
                        hasUploadedFiles = true;
                        $('#btnSimpan').prop('disabled', false);
                        showSuccess('Upload selesai! Klik Simpan untuk menyimpan.');
                    }
                })
                this.on('error', function(file, message) {
                    isUploading = false
                    let errorMsg = typeof message === 'object' ? (message.message || JSON.stringify(message)) : message;
                    showError(errorMsg || 'Upload gagal')
                })
            }
        })
    }
</script>

<?php $this->endSection() ?>