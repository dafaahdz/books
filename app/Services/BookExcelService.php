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
    private string $cacheKey = 'excel_export_state';
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

    public function startExport(BookModel $model, ?int $genreId): array
    {
        $state = $this->getState();
        if ($state !== null && ($state['running'] ?? false)) {
            return [
                'status' => 'error',
                'error' => 'Export already running'
            ];
        }

        $total = $model->countForExport($genreId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(
            ['Title', 'Author', 'Genre', 'Price'],
            null,
            'A1'
        );
        (new Xlsx($spreadsheet))->save($this->filePath);

        $this->saveState([
            'running' => true,
            'offset' => 0,
            'row' => 2,
            'total' => $total,
            'genre_id' => $genreId
        ]);

        return [
            'status' => 'started',
            'total' => $total
        ];
    }

    public function processChunk(BookModel $model): array
    {
        $state = $this->getState();

        if ($state === null || ($state['running'] ?? false) !== true) {
            return ['done' => true];
        }

        $limit = $this->limit;
        $offset = $state['offset'];
        $row = $state['row'];
        $genreId = $state['genre_id'];

        $data = $model->getBooksChunk($limit, $offset, $genreId);

        if (empty($data)) {
            $this->resetExport();
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
        $this->saveState([
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
        $state = $this->getState();
        return $state !== null && ($state['running'] ?? false) === true;
    }

    public function resetExport(): void
    {
        $this->cache->delete($this->cacheKey);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    private function getState(): ?array
    {
        $state = $this->cache->get($this->cacheKey);
        return $state ?: null;
    }

    private function saveState(array $state): void
    {
        $this->cache->save($this->cacheKey, $state, $this->ttl);
    }
}
