<?php

namespace App\Services;

use App\Models\FileModel;


class FileService
{
    protected $model;

    public function __construct()
    {
        $this->model = new FileModel();
    }

    public function handleChunk($file, $uploadId, $chunkIndex, $totalChunks, $originalName, $createdBy)
    {
        if (!$file->isValid()) {
            return [
                'sukses' => 0,
                'pesan'  => 'Chunk tidak valid'
            ];
        }

        $tempPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . $uploadId . DIRECTORY_SEPARATOR;

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $chunkName = 'chunk_' . $chunkIndex . '.part';
        $chunkPath = $tempPath . $chunkName;

        // jika chunk sudah ada (retry dari Dropzone), abaikan
        if (!file_exists($chunkPath)) {
            $file->move($tempPath, $chunkName);
        }

        // cek jumlah chunk yang sudah diupload
        $uploadedChunks = glob($tempPath . 'chunk_*.part');

        if (count($uploadedChunks) < $totalChunks) {
            return ['sukses' => 1];
        }

        // lock supaya merge tidak dijalankan 2x
        $lockFile = $tempPath . 'merge.lock';

        if (file_exists($lockFile)) {
            return ['sukses' => 1];
        }

        touch($lockFile);

        // =========================
        // MERGE SEMUA CHUNK
        // =========================

        $mergedFile = $tempPath . 'merged.tmp';
        $handle = fopen($mergedFile, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {

            $chunkFile = $tempPath . 'chunk_' . $i . '.part';

            if (!file_exists($chunkFile)) {

                fclose($handle);

                if (file_exists($lockFile)) {
                    unlink($lockFile);
                }

                return [
                    'sukses' => 0,
                    'pesan'  => 'Chunk tidak lengkap'
                ];
            }

            $chunkHandle = fopen($chunkFile, 'rb');

            while (!feof($chunkHandle)) {
                fwrite($handle, fread($chunkHandle, 1048576)); // 1MB buffer
            }

            fclose($chunkHandle);
        }

        fclose($handle);

        // hapus semua chunk
        foreach (glob($tempPath . 'chunk_*.part') as $chunk) {
            unlink($chunk);
        }

        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $tempFileName = bin2hex(random_bytes(16)) . ($extension ? '.' . $extension : '');

        $finalTempFile = $tempPath . $tempFileName;

        if (!rename($mergedFile, $finalTempFile)) {
            return [
                'sukses' => 0,
                'pesan'  => 'Gagal menyimpan file'
            ];
        }

        return [
            'sukses'       => 1,
            'isLastChunk'  => true,
            'uploadId'     => $uploadId,
            'tempPath'     => $finalTempFile,
            'tempFileName' => $tempFileName,
            'originalname' => $originalName,
            'pesan'        => 'Upload berhasil'
        ];
    }

    public function saveTempFile($tempPath, $originalName, $createdBy)
    {
        if (!file_exists($tempPath)) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $datePath = date('Y/m/d');
        $publicPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $datePath . DIRECTORY_SEPARATOR;

        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0775, true);
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $hashName = bin2hex(random_bytes(16)) . ($extension ? '.' . $extension : '');

        $finalPublicFile = $publicPath . $hashName;

        if (!rename($tempPath, $finalPublicFile)) {
            return ['sukses' => 0, 'pesan' => 'Gagal menyimpan file'];
        }

        $this->model->insert([
            'filename' => $hashName,
            'filerealname'  => $originalName,
            'filedirectory' => $datePath,
            'created_by'    => $createdBy,
            'isActive'      => true
        ]);

        return [
            'sukses' => 1,
            'filename' => $hashName,
            'filedirectory' => $datePath,
            'pesan' => 'File berhasil disimpan'
        ];
    }

    public function moveTempToPublic($tempPath, $originalName)
    {
        if (!file_exists($tempPath)) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $datePath = date('Y/m/d');
        $publicPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $datePath . DIRECTORY_SEPARATOR;

        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0775, true);
        }

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $hashName = bin2hex(random_bytes(16)) . ($extension ? '.' . $extension : '');

        $finalPublicFile = $publicPath . $hashName;

        if (!rename($tempPath, $finalPublicFile)) {
            return ['sukses' => 0, 'pesan' => 'Gagal menyimpan file'];
        }

        return [
            'sukses' => 1,
            'filename' => $hashName,
            'filedirectory' => $datePath
        ];
    }

    public function cancelUpload($uploadId)
    {
        $tempPath = WRITEPATH . 'uploads/temp/' . $uploadId . '/';

        if (is_dir($tempPath)) {
            foreach (glob($tempPath . '*') as $f) {
                unlink($f);
            }
            rmdir($tempPath);
        }
    }

    public function deleteFile($fileId)
    {
        $file = $this->model->find($fileId);

        if (!$file) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $filePath = FCPATH . 'uploads/' . $file['filedirectory'] . '/' . $file['filename'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->model->update($fileId, ['isActive' => false]);

        return ['sukses' => 1, 'pesan' => 'File berhasil dihapus'];
    }

    public function handleUpdate(int $fileId, ?string $newRealName, $newFile = null): array
    {
        $file = $this->model->find($fileId);

        if (!$file) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $updateData = [];

        if ($newRealName && $newRealName != $file['filerealname']) {
            $updateData['filerealname'] = $newRealName;
        }

        if ($newFile && $newFile->isValid()) {
            $oldFilePath = FCPATH . 'uploads/' . $file['filedirectory'] . '/' . $file['filename'];

            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            $extension = pathinfo($newFile->getClientName(), PATHINFO_EXTENSION);
            $hashName = bin2hex(random_bytes(16)) . '.' . $extension;

            $newFile->move(FCPATH . 'uploads/' . $file['filedirectory'] . '/', $hashName);

            $updateData['filename'] = $hashName;
        }

        if (!empty($updateData)) {
            $updateData['updated_by'] = session()->get('user_id');
            $this->model->update($fileId, $updateData);
        }

        return ['sukses' => 1, 'pesan' => 'File berhasil diupdate'];
    }

    public function replaceFile($fileId, $newRealName, $files)
    {
        $oldFile = $this->model->find($fileId);

        if (!$oldFile) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $oldPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $oldFile['filedirectory'] . DIRECTORY_SEPARATOR . $oldFile['filename'];

        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        if (empty($files)) {
            return ['sukses' => 0, 'pesan' => 'File baru tidak ditemukan'];
        }

        $newFile = $files[0];
        $result = $this->moveTempToPublic($newFile['tempPath'], $newFile['originalname']);

        if ($result['sukses'] != 1) {
            return $result;
        }

        $this->model->update($fileId, [
            'filerealname' => $files[0]['originalname'],
            'filename' => $result['filename'],
            'filedirectory' => $result['filedirectory']
        ]);

        return ['sukses' => 1, 'pesan' => 'File berhasil diupdate'];
    }
}
