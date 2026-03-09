<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FileModel;
use App\Services\FileService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class FileController extends BaseController
{
    public function index()
    {
        return view('files/index');
    }

    public function list()
    {
        $model = new FileModel();
        $request = service('request');

        $draw = $this->request->getPost('draw');
        $start = $this->request->getPost('start');
        $length = $this->request->getPost('length');
        $search = $this->request->getPost('search')['value'] ?? '';
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 1;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'desc';

        $data = $model->getDatatables($start, $length, $search, $orderColumnIndex, $orderDir);

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $model->countAllData(),
            'recordsFiltered' => $model->countFiltered($search),
            'data' => $data
        ];

        return $this->response->setJSON($response);
    }

    public function show($id)
    {
        $model = new FileModel();
        $file = $model->findWithCreator($id);

        if (!$file) {
            return $this->response->setJSON(['error' => 'File tidak ditemukan'])->setStatusCode(404);
        }

        return $this->response->setJSON($file);
    }

    public function view($id)
    {
        $model = new FileModel();
        $file = $model->find($id);

        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = FCPATH . 'uploads/' . $file['filedirectory'] . '/' . $file['filename'];

        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($path);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $file['filerealname'] . '"')
            ->setBody(file_get_contents($path));
    }

    public function update()
    {
        $service = new FileService();

        $fileId = (int) $this->request->getPost('fileid');
        $newRealName = $this->request->getPost('filerealname');
        $newFile = $this->request->getFile('file');

        $result = $service->handleUpdate($fileId, $newRealName, $newFile);

        return $this->response->setJSON($result);
    }

    public function updateFile()
    {
        $service = new FileService;

        $fileId = (int) $this->request->getPost('fileid');
        $newRealName = $this->request->getPost('filerealname');
        $files = json_decode($this->request->getPost('files'), true);

        if ($files && count($files) > 0) {
            $result = $service->replaceFile($fileId, $newRealName, $files);
            if ($result['sukses'] == 1) {
                if (file_exists($files[0]['tempPath'])) {
                    unlink($files[0]['tempPath']);
                }
            }
        } else {
            $result = $service->renameFile($fileId, $newRealName);
        }

        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $service = new FileService();
        $fileId = (int) $this->request->getPost('fileid');

        $result = $service->deleteFile($fileId);

        return $this->response->setJSON($result);
    }

    public function download($id)
    {
        $model = new FileModel();
        $file = $model->find($id);

        if (!$file || !$file['isActive']) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan');
        }

        $filePath = FCPATH . 'uploads/' . $file['filedirectory'] . '/' . $file['filename'];

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound('File tidak ditemukan');
        }

        return $this->response
            ->download($filePath, null)
            ->setFileName($file['filerealname']);
    }


    public function chunkUpload()
    {
        $service = new FileService();

        $result = $service->handleChunk(
            $this->request->getFile('file'),
            $this->request->getPost('uploadId'),
            (int)$this->request->getPost('chunkIndex'),
            (int)$this->request->getPost('totalChunks'),
            $this->request->getPost('originalName'),
            session()->get('user_id')
        );

        return $this->response->setJSON($result);
    }

    public function saveFiles()
    {
        $service = new FileService();
        $files = json_decode($this->request->getPost('files'), true);

        $savedCount = 0;
        foreach ($files as $file) {
            $result = $service->saveTempFile(
                $file['tempPath'],
                $file['originalname'],
                session()->get('user_id')
            );
            if ($result['sukses'] == 1) {
                $savedCount++;
                if (file_exists($file['tempPath'])) {
                    unlink($file['tempPath']);
                }
            }
        }

        return $this->response->setJSON([
            'sukses' => 1,
            'pesan' => "$savedCount file berhasil disimpan",
        ]);
    }

    public function cleanupUpload()
    {
        $service = new FileService();
        $uploadId = $this->request->getPost('uploadId');

        $service->cancelUpload($uploadId);

        return $this->response->setJSON(['sukses' => 1]);
    }
}
