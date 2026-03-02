<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('files/chunk-upload') ?>" class="dropzone" id="myDropzone" id="fileForm">
                    <div class=" dz-message">
                        <button class="dz-button" type="button">Drop files here to upload</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" id="btnSimpan" class="btn btn-primary " disabled>Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>