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
                                data-bs-target="#tabCsv">
                            Import CSV
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

                    <!-- TAB IMPORT CSV -->
                    <div class="tab-pane fade" id="tabCsv">

                        <form action="<?= base_url('books/import-csv') ?>"
                              method="post"
                              enctype="multipart/form-data">
                              

                            <div class="mb-3">
                                <label class="form-label">Upload File CSV</label>
                                <input type="file"
                                       name="csv_file"
                                       class="form-control"
                                       accept=".csv"
                                       required>
                            </div>

                            <div class="alert alert-info small">
                                Format CSV wajib:
                                <code>title,author,genre_id,price</code>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-success" type="submit">
                                    Import CSV
                                </button>
                            </div>

                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
