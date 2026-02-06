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

    public function search()
    {
        $term = $this->request->getGet('term');

        $results = $this->genre->search($term);

        $data = [];
        
        foreach($results as $row) {
            $data[] = [
                'id' => $row['id'],
                'text' => $row['name']
            ];
        }

        return $this->response->setJSON($data);
    }


    public function store()
    {
        $name = trim($this->request->getPost('name'));

        $exists = $this->genre->findByName($name);
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
