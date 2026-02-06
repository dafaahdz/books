<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BookExportService
{
    public function exportExcelChunked($bookModel, $filePath, $genreId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Title', 'Author', 'Genre', 'Price'], null, 'A1');

        $row = 2;
        $limit = 500;
        $offset = 0;

        while (true) {
            $data = $bookModel->getBooksChunk($limit, $offset, $genreId);
            if(empty($data)) break;

            foreach ($data as $book) {
                $sheet->fromArray(
                    [$book['title'], $book['author'], $book['genre_name'], $book['price']],
                    null,
                    'A' . $row++
                );
            }

            $offset += $limit;
        }

        (new Xlsx($spreadsheet))->save($filePath);
    }
}