<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- TABS -->
                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#tabManual">
                            Form Manual
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#tabExcel">
                            Import Excel
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- TAB FORM MANUAL -->
                    <div class="tab-pane fade show active" id="tabManual">

                        <form id="bookForm">
                            <input type="hidden" name="id" id="bookId">

                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Penulis</label>
                                <input type="text" name="author" id="author" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Genre</label>
                                <br>
                                <select name="genre_id" id="genreSelect" class="form-select w-100" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="price" id="price" class="form-control" required>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" id="saveBtn">
                                    Simpan
                                </button>
                            </div>
                        </form>

                    </div>

                    <!-- TAB IMPORT EXCEL -->
                    <div class="tab-pane fade" id="tabExcel">

                        <div id="importSection">
                            <div class="mb-3">
                                <label class="form-label">Upload File Excel</label>
                                <input type="file"
                                    id="excelFile"
                                    class="form-control"
                                    accept=".xlsx,.xls"
                                    required>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <button class="btn btn-outline-primary btn-sm" onclick="downloadTemplate()">
                                    📥 Download Template
                                </button>
                                <button class="btn btn-success" onclick="startImport()">
                                    📤 Import Excel
                                </button>
                            </div>

                            <!-- Progress Bar -->
                            <div id="importProgress" class="d-none">
                                <div class="mb-2">
                                    <small class="text-muted">Memproses import...</small>
                                </div>
                                <div class="progress mb-2">
                                    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div id="progressText" class="small text-muted">0 / 0 data</div>
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelImport()">
                                        ❌ Cancel
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>