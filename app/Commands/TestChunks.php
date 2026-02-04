<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class TestChunks extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:chunks';
    protected $description = 'Test DB chunking via CLI';

    public function run(array $params)
    {
        $db = Database::connect();

        $chunkSize    = 100;
        $offset       = 0;
        $currentChunk = 1;
        $currentRow   = 0;
        $result       = [];

        while (true) {
            $data = $db->table('books b')
                ->select('b.id, b.title, b.author, g.name AS genre_name, b.price')
                ->join('genres g', 'g.id = b.genre_id')
                ->orderBy('b.id', 'ASC')
                ->limit($chunkSize)
                ->offset($offset)
                ->get()
                ->getResultArray();

            if (empty($data)) {
                break;
            }


            CLI::write("== CHUNK {$currentChunk} ==", 'yellow');

            foreach ($data as $row) {
                $result[$currentRow] = $row;
                print $currentRow . "\n";
                
                CLI::write(
                    "{$row['id']} | {$row['title']} | {$row['author']}"
                );
                $currentRow++;
            }

            $result[$currentRow] = "CHUNK " . $currentChunk;
            $currentRow++;

            $offset += $chunkSize;
            print $offset;
            $currentChunk++;
            print $currentChunk;
        }

        CLI::newLine();
        CLI::write('DONE. Total rows: ' . count($result), 'green');
    }
}
