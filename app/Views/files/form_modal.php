<div class="modal fade" id="fileModal" data-backdrop="static" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah File</h5>
                <button type="button" class="btn-close" id="btnCloseModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fileId">
                <input type="hidden" id="currentFilePath">

                <div id="editFields" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">Nama File</label>
                        <span id="currentFileName" class="d-block fw-bold"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Nama</label>
                        <input type="text" class="form-control" id="filerealname" placeholder="Masukkan nama file baru">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti File (Opsional)</label>
                        <div id="myDropzoneEdit" class="dropzone">
                            <div class="dz-message">
                                <button class="dz-button" type="button">Drop file di sini atau klik untuk upload</button>
                            </div>
                        </div>
                    </div>
                </div>


                <div id="addFields">
                    <div id="myDropzone" class="dropzone">
                        <div class="dz-message">
                            <button class="dz-button" type="button">
                                Drop files here to upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="button" id="btnSimpan" class="btn btn-primary " disabled>Simpan</button>
                <button type="button" id="btnTutup" class="btn btn-secondary">Tutup</button>
            </div>
        </div>
    </div>
</div>