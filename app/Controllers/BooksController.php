<?php

namespace App\Controllers;

use App\Libraries\PDF;
use App\Models\BookModel;
use App\Models\GenreModel;
use App\Services\BookExportService;
use App\Services\BookPdfService;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef\Offset;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class BooksController extends BaseController
{
    protected $book;
    protected $genre;
    protected $db;

    public function __construct()
    {
        $this->book = new BookModel();
        $this->genre = new GenreModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        return view('books/index');
    }

    public function store()
    {
        $genreId = $this->request->getPost('genre_id');
        $newGenre = trim($this->request->getPost('new_genre'));

        if($newGenre) {
            $genreId = $this->genre->insert([
                'name' => $newGenre
            ], true);
        }

        $data = [
            'title'  => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'price'  => $this->request->getPost('price'),
            'genre_id'  => $genreId
        ];

        if(!$this->book->validate($data)) {
            return $this->response->setJSON(['status' => false]);
        }


        $this->book->insert($data);

        return $this->response->setJSON(['status' => true]);
    }


    public function datatables()
    {
        $request = service('request');

        $draw   = $request->getPost('draw');
        $start  = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'];
        $genreId = $request->getPost('genre_id');
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'b.title',
            1 => 'b.author',
            2 => 'g.name',
            3 => 'b.price',
        ];

        $orderColumn = $columnMap[$orderColumnIndex] ?? 'b.title';

        $data = $this->book->getDatatables($start, $length, $search, $genreId, $orderColumn, $orderDir);

        $response = [
            "draw" => $draw,
            "recordsTotal" => $this->book->countAllData(),
            "recordsFiltered" => $this->book->countFiltered($search, $genreId),
            "data" => $data
        ];

        return $this->response->setJSON($response);
    }

    public function show($id)
    {
        $book = $this->book->findWithGenre($id);

        return $this->response->setJSON($book);
    }

    public function exportPdf()
    {
        $genreFilter = $this->request->getGet('genre_id');

        $books = $this->book->getBooksForExport($genreFilter);

        $pdfService = new BookPdfService();
        $content = $pdfService->generate($books);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="buku.pdf"')
            ->setBody($content);
    }


    public function testChunks() {
        $chunkSize = 100;
        $offset = 0;
        $currentChunk = 1;
        $currentRow = 0;
        $result = [];

        while(true) {
            $data = $this->db->table('books b')
                            ->select('b.id, b.title, b.author, g.name AS genre_name, b.price')
                            ->join('genres g', 'g.id = b.genre_id')
                            ->orderBy('b.id ASC')
                            ->limit($chunkSize)
                            ->offset($offset)
                            ->get()
                            ->getResultArray();

            if(empty($data)){
                break;
            }

            foreach($data as $tunggal) {
                $result[$currentRow] = $tunggal;
                $currentRow++;
            }
            $result[$currentRow] = "CHUNK " . $currentChunk;
            $currentChunk++;
            $currentRow ++;
            $offset += $chunkSize;
            
            }
            
        return $this->response->setJSON($result);
    }
    
    public function exportCsv() 
    {

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="books.csv"');

        $output=fopen('php://output', 'w');

        fputcsv($output, ['title', 'author', 'genre_id', 'price']);

        $genreFilter = $this->request->getGet('genre_id');
        $chunkSize = 500;
        $offset = 0;

        while (true) {
            $books = $this->book->getBooksChunk($chunkSize, $offset, $genreFilter);

            if(empty($books)) break;

            foreach ($books as $book) {
                fputcsv($output, [
                    $book['title'],
                    $book['author'],
                    $book['genre_name'],
                    $book['price'],
                ]);
            }

            $offset += $chunkSize;
        }

        fclose($output);
        exit;
    } 

    public function exportExcel()
    {   
       $service = new BookExportService();
       $file = WRITEPATH . 'exports/books.xlsx';

       $service->exportExcelChunked(
        $this->book,
        $file,
        $this->request->getGet('genre_id')
       );

       return $this->response->download($file, null);
    }

    public function importCsv() 
    {
        helper(['form']);

        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $file = $this->request->getFile('csv_file');

        if(!$file || $file->isValid() || $file->getClientExtension() !== 'csv') {
            return redirect()->back()->with('error', 'File CSV tidak valid');
        }

        $handle = fopen($file->getTempName(), 'r');
        if($handle === false) {
            return redirect()->back()->with('error', 'CSV tidak bisa dibaca');
        }

        $expectedHeader =  ['title', 'author', 'genre_name', 'price'];
        $header = fgetcsv($handle);

        if ($header !== $expectedHeader) {
            fclose($handle);
            return redirect()->back()->with(
                'error',
                'Header CSV tidak sesuai. Harus: ' . implode(', ', $expectedHeader)
            );
        }

        $batch = [];
        $batchSize = 500;

        $this->db->transStart();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                continue;
            }

            [$title, $author, $genreName, $price] = $row;

            $genre = $this->genre
                    ->where('name', trim($genreName))
                    ->first();

            if(!$genre) {
                $genreId = $this->genre->insert([
                    'name' => trim($genreName)
                ], true);
            } else {
                $genreId = $genre['id'];
            }


            $batch[] = [
                'title'     => trim($title),
                'author'    => trim($author),
                'genre_id'    => $genreId,
                'price'    => (int) $price,
            ];

            if (count($batch) === $batchSize) {
                $this->book->insertBatch($batch);
                $batch = [];
            }
        }

        if(!empty($batch)) {
            $this->book->insertBatch($batch);
        }

        fclose($handle);

        $this->db->transComplete();

        if($this->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Import gagal');
        }

        return redirect()->back()->with('success', 'Import CSV berhasil');
    }






    
    public function update()
    {
        $id = $this->request->getPost('id');

        $this->book->update($id, [
            'title' => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'genre_id' => $this->request->getPost('genre_id'),
            'price' => $this->request->getPost('price'),
        ]);

        return $this->response->setJSON(['status' => true]);

    }

    public function delete() 
    {
        $this->book->delete(
            $this->request->getPost('id')
        );

        return $this->response->setJSON(['status' => true]);
    }


    public function exportInit() 
    {
        $session = session();
        $db = \Config\Database::connect();

        if($session->get('export_running') === true) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'error' => 'Export already running'
                ]);
        }

        $builder = $db->table('books');

        $genreId = $this->request->getGet('genre_id');

        if(!empty($genreId)) (
            $builder->where('genre_id', $genreId)
        );

        $total = $builder->countAllResults();

        $session->set([
            'export_running' => true,
            'export_offset' => 0,
            'export_row' => 2,
            'export_total' => $total,
            'genre_id' => $genreId,
            'export_done' => false
        ]);

        return $this->response->setJSON([
            'status' => 'started',
            'total' => $total
        ]);
    }

    public function exportChunk()
    {
        $db = \Config\Database::connect();
        $session = session();

        $chunkSize = 500;
        $offset = $session->get('export_offset');
        $row = $session->get('export_row');
        $genreId = $session->get('genre_id');
        $total = $session->get('export_total');


        $file = WRITEPATH . 'exports\books.xlsx';

        if($offset === 0) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(
                ['Title', 'Author', 'Genre', 'Price'],
                null,
                'A1'
            );
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
        }

        $builder = $db->table('books b')
                ->select('b.title, b.author, g.name AS genre, b.price')
                ->join('genres g', 'g.id = b.genre_id')
                ->orderBy('b.id ASC')
                ->limit($chunkSize)
                ->offset($offset);
         

        if(!empty($genreId)) {
            $builder->where('b.genre_id', $genreId);
        } 

        $data = $builder->get()->getResultArray();
        

        if(empty($data)) {
            $session->set('export_done', true);
            $session->set('export_running', false);
            return $this->response->setJSON(['done' => true]);
        }

        foreach($data as $book) {
            $sheet->fromArray(
                [$book['title'], $book['author'], $book['genre'], $book['price']],
                null,
                'A' . $row
            );
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($file);

        $session->set([
            'export_offset' => $offset + $chunkSize,
            'export_row' => $row
        ]);

        $processed = min(
            $offset + count($data),
            $total
        );

        return $this->response->setJSON([
            'done' => false,
            'processed' => $processed,
            'total' => $total
        ]);
    }

    public function exportDownload()
    {
        if(!is_dir(WRITEPATH . 'exports')) {
            mkdir(WRITEPATH . 'exports', 0777, true);
        }
        return $this->response->download(
            WRITEPATH . 'exports\books.xlsx',
            null
        );
    }

    public function exportReset()
    {
        $session = session();
        $session->remove([
            'export_running',
            'export_offset',
            'export_row',
            'export_total',
            'genre_id',
            'export_done',
        ]);

        return $this->response->setJSON([
            'status' => 'reset'
        ]);
    }

}
