<?php
namespace App\Services;

use App\Models\BookModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BookExcelService
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = WRITEPATH . 'exports/books.xlsx';

        if(!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0777, true);
        }
    }

    public function init(BookModel $book, ?int $genreId):int
    {
        $total = $book->countForExport($genreId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(
            ['Title', 'Author', 'Genre', 'Price'],
            null,
            'A1'
        );

        (new Xlsx($spreadsheet))->save($this->filePath);

        return $total;
    }

    public function processChunk(BookModel $book, int $limit, int $offset, int $row, ?int $genreId): int
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $data = $book->getBooksChunk($limit, $offset, $genreId);

        foreach($data as $item) {
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

        return count($data);
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

}