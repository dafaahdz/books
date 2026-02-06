<?php

namespace App\Services;

use App\Models\BookModel;
use App\Models\GenreModel;
use CodeIgniter\Database\BaseConnection;

class BookService
{
    protected BookModel $book;
    protected GenreModel $genre;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->book = new BookModel();
        $this->genre= new GenreModel();
        $this->db  = \Config\Database::connect();
    }

    public function create(array $data, ?string $newGenre = null): bool
    {
        $this->db->transBegin();

        if($newGenre) {
            $genreId = $this->genre->insert([
                'name' => trim($newGenre)
            ], true);

            if(!$genreId) {
                $this->db->transRollback();
                return false;
            }


            $data['genre_id'] = $genreId;
        }

        if(!$this->book->createBook($data)) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();

        return $this->db->transStatus();
    }

    public function update(int $id, array $data):bool {
        $this->db->transBegin();

        if(!$this->book->find($id)) {
            $this->db->transRollback();
            return false;
        }

        if(!$this->book->update($id, $data)) {
            $this->db->transRollback();
            return false;
        }
        
        $this->db->transCommit();
        return $this->db->transStatus();
    }

    public function delete(int $id):bool {
        $this->db->transBegin();

        if(!$this->book->find($id)) {
            $this->db->transRollback();
            return false;
        }

        if(!$this->book->delete($id)) {
            $this->db->transRollback();
            return false;
        }
        
        $this->db->transCommit();
        return $this->db->transStatus();
    }
}

?>