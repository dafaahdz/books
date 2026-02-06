<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Models\GenreModel;
use App\Services\BookCsvService;
use App\Services\BookExcelService;
use App\Services\BookPdfService;
use App\Services\BookService;

class BooksController extends BaseController
{
    protected $book;
    protected $genre;

    public function __construct()
    {
        $this->book = new BookModel();
        $this->genre = new GenreModel();
    }

    public function index()
    {
        return view('books/index');
    }

    public function store()
    {

        $service = new BookService();
        $data = [
            'title'     => $this->request->getPost('title'),
            'author'    => $this->request->getPost('author'),
            'price'     => $this->request->getPost('price'),
            'genre_id'  => $this->request->getPost('genre_id'),
        ];

        $newGenre = trim($this->request->getPost('new_genre'));

        $status = $service->create($data, $newGenre);

        return $this->response->setJSON(['status' => $status]);
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
    
    public function exportCsv() 
    {
        $genreId = $this->request->getGet('genre_id');

        $service = new BookCsvService();
        $filePath = $service->generate($this->book, $genreId);

        return $this->response->download($filePath, null);
    }
    
    public function update()
    {
        $service = new BookService();
        
        $id = (int) $this->request->getPost('id');

        $data = [
            'title'     => $this->request->getPost('title'),
            'author'    => $this->request->getPost('author'),
            'genre_id'  => $this->request->getPost('genre_id'),
            'price'     => $this->request->getPost('price'),
        ];

        $status = $service->update($id, $data);
        
        return $this->response->setJSON(['status' => $status]);

    }

    public function delete() 
    {
        $service = new BookService();
        $id = (int) $this->request->getPost('id');

        $status = $service->delete($id);

        return $this->response->setJSON(['status' => $status]);
    }


    public function exportInit() 
    {
        $session = session();

        if($session->get('export_running') === true) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'error' => 'Export already running'
                ]);
        }

        $genreId = $this->request->getGet('genre_id');
        $service = new BookExcelService();

        $total = $service->init($this->book, $genreId);

        $session->set([
            'export_running' => true,
            'export_offset' => 0,
            'export_row' => 2,
            'export_total' => $total,
            'genre_id' => $genreId,
        ]);

        return $this->response->setJSON([
            'status' => 'started',
            'total' => $total
        ]);
    }

    public function exportChunk()
    {
        $session = session();

        if ($session->get('export_running') !== true) {
            return $this->response->setJSON(['done' => true]);
        }


        $limit = 500;
        $offset = $session->get('export_offset');
        $row = $session->get('export_row');
        $total = $session->get('export_total');
        $genreId = $session->get('genre_id');

        $service = new BookExcelService();

        $processed = $service->processChunk(
            $this->book,
            $limit,
            $offset,
            $row,
            $genreId,
        );

        if($processed === 0) {
            $session->remove(['export_running']);
            return $this->response->setJSON(['done' => true]);
        }

        $session->set([
            'export_offset' => $offset + $processed,
            'export_row' => $row
        ]);

        return $this->response->setJSON([
            'done' => false,
            'processed' => min($offset + $processed, $total),
            'total' => $total
        ]);
        
    }

    public function exportDownload()
    {
        $service = new BookExcelService();
        $filePath = $service->getFilePath();
        
        return $this->response->download(
            $service->getFilePath(),
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
