<?php

namespace App\Services;

use App\Models\BookModel;

class BookCsvService 
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = WRITEPATH . "exports/books_" . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ".csv";

        if(!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0777, true);
        }
    }

    public function generate(BookModel $book, ?int $genreId): string
    {
        $handle = fopen($this->filePath, 'w');
        if($handle ===  false) {
            throw new \RuntimeException('Failed to create CSV file');
        }

        fputcsv($handle, ['title', 'author', 'genre', 'price']);

        $chunkSize = 500;
        $offset = 0;

        while (true) {
            $rows = $book->getBooksChunk($chunkSize, $offset, $genreId);

            if( empty($rows)) {
                break;
            }

            foreach($rows as $row) {
                fputcsv($handle, [
                    $row['title'],
                    $row['author'],
                    $row['genre_name'],
                    $row['price'],
                ]);
            }

            $offset += $chunkSize;
        }

        fclose($handle);

        return $this->filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}