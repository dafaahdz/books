<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GenreModel;
use CodeIgniter\HTTP\ResponseInterface;

class GenreController extends BaseController
{
    protected $genre;

    public function __construct()
    {
        $this->genre = new GenreModel();
    }

    public function list()
    {
        $genres = $this->genre
                ->orderBy('name', 'ASC')
                ->findAll();

        return $this->response->setJSON($genres);
    }


    public function store()
    {
        $name = trim($this->request->getPost('name'));

        if($name === '') {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nama genre wajib diisi'
            ]);
        }


        $exists = $this->genre->where('name', $name)->first();
        if($exists) {
            return $this->response->setJSON([
                'status' => true,
                'id' => $exists['id'],
                'name' => $exists['name']
            ]);
        }

        $id = $this->genre->insert([
            'name' => $name
        ], true);

        return $this->response->setJSON([
            'status' => true,
            'id' => $id,
            'name' => $name
        ]);
    }
}
