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
        $genreId = $this->request->getGet('genre_id');
        $service = new BookExcelService();

        $result = $service->startExport($this->book, $genreId);

        if (isset($result['error'])) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON(['error' => $result['error']]);
        }

        return $this->response->setJSON([
            'status' => $result['status'],
            'total' => $result['total']
        ]);
    }

    public function exportChunk()
    {
        $service = new BookExcelService();
        $result = $service->exportProcessChunk($this->book);

        return $this->response->setJSON($result);
    }

    public function exportDownload()
    {
        $service = new BookExcelService();

        return $this->response->download(
            $service->getFilePath(),
            null
        );
    }

    public function exportReset()
    {
        $service = new BookExcelService();
        $service->resetState('excel_export_state');

        return $this->response->setJSON([
            'status' => 'reset'
        ]);
    }

    public function importInit()
    {
        $file = $this->request->getFile('file');
        
        if (!$file || !$file->isValid()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'File tidak valid']);
        }

        $filePath = $file->getTempName();
        $service = new BookExcelService();
        
        $result = $service->startImport($filePath);

        if (isset($result['error'])) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON(['error' => $result['error']]);
        }

        return $this->response->setJSON([
            'status' => $result['status'],
            'total' => $result['total']
        ]);
    }

    public function importChunk()
    {
        try {
            $service = new BookExcelService();
            $result = $service->processImportChunk($this->book);

            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function importReset()
    {
        $service = new BookExcelService();
        $service->resetState('excel_import_state');

        return $this->response->setJSON([
            'status' => 'reset'
        ]);
    }

    public function downloadTemplate()
    {
        $service = new BookExcelService();
        
        // Buat template mirip seperti export (reuse logic)
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header title "DATA BUKU" dengan style yang sama
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
        
        // Row 2 kosong
        // Row 3 headers
        $sheet->fromArray(
            ['Judul', 'Penulis', 'Genre', 'Price'],
            null,
            'A3'
        );
        
        // Style headers yang sama dengan export
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
        
        // Sample data untuk panduan
        $sheet->fromArray([
            ['Contoh Judul Buku', 'Nama Penulis', 'Fiksi', 50000],
            ['Laskar Pelangi', 'Andrea Hirata', 'Romance', 75000]
        ], null, 'A4');
        
        // Style sample data
        $sheet->getStyle('A4:D5')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD']
                ]
            ]
        ]);
        
        // Auto-size columns (sama seperti export)
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Direct download to browser
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();
        
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="template_import_buku.xlsx"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setHeader('Pragma', 'public')
            ->setBody($excelContent);
    }
}
