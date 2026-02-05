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

    public function getDatatables($start, $length, $search, $genreId = null, $orderColumn, $orderDir)
    {
        $builder = $this->baseQuery();

        if ($search) {
            $builder->groupStart()
                ->like('b.title', $search)
                ->orLike('b.author', $search)
                ->orLike('g.name', $search)
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
        $builder = $this->db->table('books b')
            ->join('genres g', 'g.id = b.genre_id');

        if($search) {
            $builder->groupStart()
                ->like('b.title', $search)
                ->orLike('b.author', $search)
                ->orLike('g.name', $search)
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
}
