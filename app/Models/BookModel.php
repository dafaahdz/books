<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $allowedFields = ['title', 'author', 'genre_id', 'price'];

    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'author' => 'required|min_length[2]|max_length[255]',
        'genre_id' => 'required|is_natural_no_zero',
        'price' => 'required|numeric|greater_than[0]',
    ];

    private function baseQuery()
    {
        return $this->db->table('books b')
            ->select('b.id, b.title, b.author, b.price, g.name AS genre_name')
            ->join('genres g', 'g.id = b.genre_id');
    }

    public function createBook(array $data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        return $this->insert($data);
    }

    public function getDatatables($start, $length, $search, $genreId = null, $orderColumn, $orderDir)
    {
        $builder = $this->baseQuery();

        if ($search) {
            $builder->groupStart()
                ->like('b.title', $search, 'both', null, true)
                ->orLike('b.author', $search, 'both', null, true)
                ->orLike('g.name', $search, 'both', null, true)
            ->groupEnd();
        }

        if($genreId) {
            $builder->where('b.genre_id', $genreId);
        }

        $builder->orderBy($orderColumn, $orderDir);

        return $builder
            ->limit($length, $start)
            ->get()
            ->getResultArray();
    }

    public function countFiltered($search, $genreId)
    {
        $builder = $this->baseQuery();

        if($search) {
            $builder->groupStart()
                ->like('b.title', $search, 'both', null, true)
                ->orLike('b.author', $search, 'both', null, true)
                ->orLike('g.name', $search, 'both', null, true)
            ->groupEnd();
        }
        
        if($genreId) {
            $builder->where('b.genre_id', $genreId);
        }

        return $builder->countAllResults();
    }

    public function countAllData()
    {
        return $this->db->table('books')->countAll();
    }

    public function getBooks($genreId = null) 
    {
        $builder = $this->baseQuery();

        if($genreId) {
            $builder->where('b.genre_id', $genreId);
        }

        return $builder->get()->getResultArray();
    }

    public function getBooksChunk($limit, $offset, $genreId = null)
    {
        $builder = $this->baseQuery()   
            ->orderBy('b.id ASC')
            ->limit($limit, $offset);

            if($genreId) {
                $builder->where('b.genre_id', $genreId);
            }

            return $builder->get()->getResultArray();
    }

    public function findWithGenre(int $id) 
    {
        return $this->db->table('books b')
            ->select('b.id, b.title, b.author, b.price, b.genre_id, g.name AS genre_name')
            ->join('genres g', 'g.id = b.genre_id')
            ->where('b.id', $id)
            ->get()
            ->getRowArray();
    }

    public function getBooksForExport($genreId = null)
    {
        $builder = $this->baseQuery()
            ->select('b.title, b.author, b.price, g.name AS genre');

        if($genreId) {
            $builder->where('b.genre_id', $genreId);
        }

        return $builder->get()->getResultArray();
    }

    public function countForExport(?int $genreId = null): int
    {
        $builder = $this->db->table('books');

        if($genreId) {
            $builder->where('genre_id', $genreId);
        }

        return $builder->countAllResults();
    }


}
