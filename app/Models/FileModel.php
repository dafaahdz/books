<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table            = 'files';
    protected $primaryKey       = 'fileid';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'filename',
        'filerealname',
        'filedirectory',
        'created_by',
        'updated_by',
        'isActive'
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    private function baseQuery()
    {
        return $this->db->table('files f')
            ->select('f.fileid, f.filename, f.filerealname, f.filedirectory, f.created_at, u.username AS created_by_name')
            ->join('users u', 'u.id = f.created_by', 'left')
            ->where('f.isActive', true);
    }

    public function getDatatables($start, $length, $search, $orderColumn, $orderDir)
    {
        $builder = $this->baseQuery();

        if ($search) {
            $builder->groupStart()
                ->like('f.filerealname', $search, 'both', null, true)
                ->orLike('u.username', $search, 'both', true)
                ->groupEnd();
        }

        $columnMap = [
            0 => 'f.filerealname',
            1 => 'f.created_at',
            2 => 'u.username'
        ];

        $orderColumn = $columnMap[$orderColumn] ?? 'f.created_at';

        $builder->orderBy($orderColumn, $orderDir);

        return $builder
            ->limit($length, $start)
            ->get()
            ->getResultArray();
    }

    public function countFiltered($search)
    {
        $builder = $this->baseQuery();

        if ($search) {
            $builder->groupStart()
                ->like('f.filerealname', $search, 'both', null, true)
                ->orLike('u.username', $search, 'both', null, true)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function countAllData()
    {
        return $this->db->table('files')
            ->where('isActive', true)
            ->countAllResults();
    }

    public function findWithCreator(int $id)
    {
        return $this->db->table('files f')
            ->select('f.fileid, f.filename, f.filerealname, f.filedirectory, f.created_at, f.created_by, u.username AS created_by_name')
            ->join('users u', 'u.id = f.created_by', 'left')
            ->where('f.fileid', $id)
            ->where('f.isActive', true)
            ->get()
            ->getRowArray();
    }
}
