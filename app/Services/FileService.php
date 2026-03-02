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
        $file->move($tempPath, $chunkName);

        // kalau belum chunk terakhir
        if ($chunkIndex < $totalChunks - 1) {
            return ['sukses' => 1];
        }

        // =========================
        // MERGE SEMUA CHUNK
        // =========================

        $mergedFile = $tempPath . 'merged.tmp';
        $handle = fopen($mergedFile, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $tempPath . 'chunk_' . $i . '.part';

            if (!file_exists($chunkFile)) {
                fclose($handle);
                return [
                    'sukses' => 0,
                    'pesan'  => 'Chunk tidak lengkap'
                ];
            }

            $chunkHandle = fopen($chunkFile, 'rb');
            while ($buffer = fread($chunkHandle, 8192)) {
                fwrite($handle, $buffer);
            }
            fclose($chunkHandle);
        }

        fclose($handle);



        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $tempFileName = bin2hex(random_bytes(16)) . ($extension ? '.' . $extension : '');

        // Keep merged file in temp folder - don't move to public yet
        $finalTempFile = $tempPath . $tempFileName;

        if (!rename($mergedFile, $finalTempFile)) {
            return ['sukses' => 0, 'pesan' => 'Gagal menyimpan file'];
        }

        return [
            'sukses'      => 1,
            'isLastChunk' => true,
            'uploadId'    => $uploadId,
            'tempPath'    => $finalTempFile,
            'tempFileName' => $tempFileName,
            'originalname' => $originalName,
            'pesan'       => 'Upload berhasil'
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
            'pesan' => 'File berhasil disimpan'
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
        $file = model(FileModel::class)->find($fileId);

        if (!$file) {
            return ['sukses' => 0, 'pesan' => 'File tidak ditemukan'];
        }

        $filePath = FCPATH . 'uploads/' . $file['filedirectory'] . '/' . $file['filename'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        model(FileModel::class)->update($fileId, ['isActive' => false]);

        return ['sukses' => 1, 'pesan' => 'File berhasil dihapus'];
    }

    public function handleUpdate(int $fileId, ?string $newRealName, $newFile = null): array
    {
        $file = model(FileModel::class)->find($fileId);

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
            model(FileModel::class)->update($fileId, $updateData);
        }

        return ['sukses' => 1, 'pesan' => 'File berhasil diupdate'];
    }
}
