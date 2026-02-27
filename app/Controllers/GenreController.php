<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookModel;
use App\Models\GenreModel;
use CodeIgniter\HTTP\ResponseInterface;

class GenreController extends BaseController
{
    protected $genre;
    protected $book;

    public function __construct()
    {
        $this->genre = new GenreModel();
        $this->book = new BookModel();
    }

    public function index()
    {
        return view('genres/index');
    }

    public function datatables()
    {
        $request = $this->request;

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'] ?? '';
        $orderColumn = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

        $columns = ['name', 'book_count', 'action'];
        $orderBy = $columns[$orderColumn] ?? 'name';

        $genreTotal = $this->genre->countAll();

        $builder = $this->genre;

        if (!empty($search)) {
            $builder->like('name', $search, 'both', null, true);
        }

        $filteredRecords = $builder->countAllResults(false);

        $genres = $builder
            ->orderBy($orderBy, $orderDir)
            ->limit((int) $length, (int) $start)
            ->findAll();

        foreach ($genres as &$genre) {
            $genre['book_count'] = $this->book
                ->where('genre_id', $genre['id'])
                ->countAllResults();
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $genreTotal,
            'recordsFiltered' => $filteredRecords,
            'data' => $genres
        ]);
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

        foreach ($results as $row) {
            $data[] = [
                'id' => $row['id'],
                'text' => $row['name']
            ];
        }

        return $this->response->setJSON($data);
    }

    public function show($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['error' => 'ID tidak valid'], 400);
        }

        $genre = $this->genre->find($id);

        if (!$genre) {
            return $this->response->setJSON(['error' => 'Genre tidak ditemukan'], 404);
        }

        return $this->response->setJSON($genre);
    }


    public function store()
    {
        $name = trim($this->request->getPost('name'));

        if (empty($name)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nama genre wajib diisi'
            ], 400);
        }

        $exists = $this->genre->findByName($name);
        if ($exists) {
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

    public function update()
    {
        $id = $this->request->getPost('id');
        $name = trim($this->request->getPost('name'));

        if (empty($id) || empty($name)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ID dan nama genre wajib diisi'
            ], 400);
        }

        $genre = $this->genre->find($id);
        if (!$genre) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Genre tidak ditemukan'
            ], 404);
        }

        // Check if name already exists (excluding current genre)
        $existing = $this->genre
            ->where('name', $name)
            ->where('id !=', $id)
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Nama genre sudah ada'
            ], 400);
        }

        $this->genre->update($id, ['name' => $name]);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Genre berhasil diupdate'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        if (empty($id)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ID genre wajib diisi'
            ], 400);
        }

        $genre = $this->genre->find($id);
        if (!$genre) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Genre tidak ditemukan'
            ], 404);
        }

        // Check if genre has books
        $bookCount = $this->book
            ->where('genre_id', $id)
            ->countAllResults();

        if ($bookCount > 0) {
            return $this->response->setJSON([
                'status' => false,
                'message' => "Genre ini memiliki {$bookCount} buku. Hapus buku terlebih dahulu."
            ], 400);
        }

        $this->genre->delete($id);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Genre berhasil dihapus'
        ]);
    }

    public function books($id = null)
    {
        if (!$id) {
            return $this->response->setJSON(['error' => 'ID tidak valid'], 400);
        }

        $genre = $this->genre->find($id);
        if (!$genre) {
            return $this->response->setJSON(['error' => 'Genre tidak ditemukan'], 404);
        }

        $books = $this->book
            ->select('books.*, genres.name as genre_name')
            ->join('genres', 'genres.id = books.genre_id')
            ->where('books.genre_id', $id)
            ->orderBy('books.title', 'ASC')
            ->findAll();

        return $this->response->setJSON($books);
    }
}
