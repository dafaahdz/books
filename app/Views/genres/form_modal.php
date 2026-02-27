<div class="modal fade" id="genreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Genre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="genreForm">
                    <input type="hidden" name="id" id="genreId">

                    <div class="mb-3">
                        <label class="form-label">Nama Genre</label>
                        <input type="text" name="name" id="genreName" class="form-control" required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>