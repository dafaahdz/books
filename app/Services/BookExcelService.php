<?php

namespace App\Services;

use App\Models\BookModel;
use CodeIgniter\Cache\CacheInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BookExcelService
{
    private string $filePath;
    private CacheInterface $cache;
    private string $exportCacheKey = 'excel_export_state';
    private string $importCacheKey = 'excel_import_state';
    private int $limit = 500;
    private int $ttl = 3600;

    public function __construct()
    {
        $this->cache = service('cache');
        $this->filePath = WRITEPATH . 'exports/books.xlsx';

        if (!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0777, true);
        }
    }

    public function startImport(string $filePath): array
    {
        $state = $this->getImportState();

        if ($state && ($state['running'] ?? false)) {
            return [
                'status' => 'error',
                'error' => 'Import already running'
            ];
        }

        if (!file_exists($filePath)) {
            return ['status' => 'error', 'error' => 'File not found'];
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Move temp file to persistent location
        $persistentPath = WRITEPATH . 'uploads/import_' . time() . '_' . basename($filePath);
        if (!is_dir(dirname($persistentPath))) {
            mkdir(dirname($persistentPath), 0777, true);
        }
        
        if (!copy($filePath, $persistentPath)) {
            return ['status' => 'error', 'error' => 'Gagal menyimpan file import'];
        }
        
        $totalRows = $sheet->getHighestRow();
        $validRows = max(0, $totalRows - 3); // Exclude first 3 rows
        
        $this->saveImportState([
            'running' => true,
            'row' => 4, // Start from row 4 (after title and headers)
            'total' => $totalRows,
            'file' => $persistentPath,
            'inserted' => 0
        ]);

        return [
            'status' => 'started',
            'total' => $validRows // Return only valid rows count
        ];
    }

    public function processImportChunk(BookModel $model)
    {
        $state = $this->getImportState();
        
        if (!$state || !$state['running']) {
            return ['done' => true];
        }
        
        // Load genre model for mapping
        $genreModel = new \App\Models\GenreModel();
        
        if (!file_exists($state['file'])) {
            throw new \Exception('Import file not found: ' . $state['file']);
        }
        
        $spreadsheet = IOFactory::load($state['file']);
        $sheet = $spreadsheet->getActiveSheet();

        $row = $state['row'];
        $limit = $this->limit;
        $lastRow = min($row + $limit - 1, $state['total']);

        $batch = [];

        $batch = [];
        $batch = [];
        for ($i = $row; $i <= $lastRow; $i++) {
            try {
                $data = $sheet->rangeToArray("A{$i}:D{$i}", null, true, false)[0];
                
                // Validasi data
                $title = trim($data[0]);
                $author = trim($data[1]);
                $genreName = trim($data[2]);
                $price = $data[3];
                
                // Skip kalau title kosong
                if (empty($title)) {
                    continue;
                }
                
                // Validasi author
                if (empty($author)) {
                    continue;
                }
                
                // Auto-create genre jika tidak ditemukan
                $genreRecord = $genreModel->findByName($genreName);
                $genreId = $genreRecord ? $genreRecord['id'] : null;
                
                if (!$genreId) {
                    $genreId = $genreModel->insert(['name' => $genreName]);
                }
                
                // Validasi price
                if (!is_numeric($price) || $price <= 0) {
                    continue;
                }
                
                // Validasi judul duplikat (case-sensitive exact match)
                $existingBook = $model->select('id')->where('title', $title)->first();
                if ($existingBook) {
                    continue;
                }

                $batch[] = [
                    'title' => $title,
                    'author' => $author,
                    'genre_id' => $genreId,
                    'price' => (float) $price,
                ];
                
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($batch) {
            try {
                $model->insertBatch($batch);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $newRow = $lastRow + 1;
        $done = $newRow > $state['total'];

        if ($done) {
            // Clean up the file
            if (file_exists($state['file'])) {
                unlink($state['file']);
            }
            
            $this->resetState($this->importCacheKey);
        } else {
            $this->saveImportState([
                ...$state,
                'row' => $newRow,
                'inserted' => $state['inserted'] + count($batch)
            ]);
        }

        $actualInserted = count($batch);
        $totalInserted = $state['inserted'] + $actualInserted;
        $validRows = $state['total'] - 3; // Exclude header rows

        return [
            'done' => $done,
            'processed' => $totalInserted,
            'total' => $validRows,
            'skipped' => ($lastRow - $row + 1) - $actualInserted
        ];
    }

    public function startExport(BookModel $model, ?int $genreId): array
    {
        $state = $this->getExportState();
        if ($state !== null && ($state['running'] ?? false)) {
            return [
                'status' => 'error',
                'error' => 'Export already running'
            ];
        }

        $total = $model->countForExport($genreId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header title "DATA BUKU" with dark gray background and bold
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'DATA BUKU');
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'color' => ['rgb' => 'FFFFFF'],
                'bold' => true,
                'size' => 14
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '404040']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Row 2 empty for spacing
        // Row 3 headers
        $sheet->fromArray(
            ['Judul', 'Penulis', 'Genre', 'Price'],
            null,
            'A3'
        );
        
        // Style headers
        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'F2F2F2']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);
        
        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        (new Xlsx($spreadsheet))->save($this->filePath);

        $this->saveExportState([
            'running' => true,
            'offset' => 0,
            'row' => 4, // Start data from row 4
            'total' => $total,
            'genre_id' => $genreId
        ]);

        return [
            'status' => 'started',
            'total' => $total
        ];
    }

    public function exportProcessChunk(BookModel $model): array
    {
        $state = $this->getExportState();

        if ($state === null || ($state['running'] ?? false) !== true) {
            return ['done' => true];
        }

        $limit = $this->limit;
        $offset = $state['offset'];
        $row = $state['row'];
        $genreId = $state['genre_id'];

        $data = $model->getBooksChunk($limit, $offset, $genreId);

        if (empty($data)) {
            $this->resetState($this->exportCacheKey);
            return ['done' => true];
        }

        $spreadsheet = IOFactory::load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($data as $item) {
            $sheet->fromArray(
                [
                    $item['title'],
                    $item['author'],
                    $item['genre_name'],
                    $item['price']
                ],
                null,
                'A' . $row
            );
            $row++;
        }

        (new Xlsx($spreadsheet))->save($this->filePath);

        $newOffset = $offset + count($data);
        $this->saveExportState([
            'running' => true,
            'offset' => $newOffset,
            'row' => $row,
            'total' => $state['total'],
            'genre_id' => $genreId
        ]);

        $processed = min($newOffset, $state['total']);
        $done = $newOffset >= $state['total'];

        return [
            'done' => $done,
            'processed' => $processed,
            'total' => $state['total']
        ];
    }

    public function isExportRunning(): bool
    {
        $state = $this->getExportState();
        return $state !== null && ($state['running'] ?? false) === true;
    }

    public function resetState(string $key): void
    {
        // If resetting import state, clean up the file
        if ($key === $this->importCacheKey) {
            $state = $this->getState($key);
            if ($state && isset($state['file']) && file_exists($state['file'])) {
                unlink($state['file']);
            }
        }
        
        $this->cache->delete($key);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    private function getState(string $key): ?array
    {
        $state = $this->cache->get($key);
        return $state ?: null;
    }

    private function saveState(string $key, array $state): void
    {
        $this->cache->save($key, $state, $this->ttl);
    }

    private function getImportState(): ?array
    {
        return $this->getState($this->importCacheKey);
    }

    private function getExportState(): ?array
    {
        return $this->getState($this->exportCacheKey);
    }

    private function saveImportState(array $state): void
    {
        $this->saveState($this->importCacheKey, $state);
    }

    private function saveExportState(array $state): void
    {
        $this->saveState($this->exportCacheKey, $state);
    }
}
