<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Models\GenreModel;
use Fpdf\Fpdf;
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


        $this->book->insert([
            'title'  => $this->request->getPost('title'),
            'author' => $this->request->getPost('author'),
            'price'  => $this->request->getPost('price'),
            'genre_id'  => $genreId
        ]);

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

        $data = $this->book->getDatatables($start, $length, $search, $genreId);

        $response = [
            "draw" => $draw,
            "recordsTotal" => $this->book->countAll(),
            "recordsFiltered" => $this->book->countFiltered($search, $genreId),
            "data" => $data
        ];

        return $this->response->setJSON($response);
    }

    public function show($id)
    {
        return $this->response->setJSON(
            $this->book->find($id)
        );
    }

    public function exportPdf()
    {
        $genreFilter = $this->request->getGet('genre_id');

        $db = \Config\Database::connect();
        $builder = $db->table('books b')
            ->select('b.title, b.author, b.price, g.name AS genre')
            ->join('genres g', 'g.id = b.genre_id', 'left');

        if (!empty($genreFilter)) {
            $builder->where('b.genre_id', $genreFilter);
        }

        $books = $builder->get()->getResultArray();

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->AddPage();


        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(43, 25, '', 1, 0, 'C');
        $pdf->Image('assets/upload/image.png', 20, 15, 21, 15);
        $pdf->Cell(67, 25, 'FORM LAPORAN DATA BUKU', 1, 0, 'C');

        $xRight = $pdf->GetX();
        $yTop   = $pdf->GetY();

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetXY($xRight, $yTop);
        $pdf->Cell(21, 6.3, 'Dokumen', 1, 1);
        $pdf->SetX($xRight);
        $pdf->Cell(21, 6.3, 'Revisi', 1, 1);
        $pdf->SetX($xRight);
        $pdf->Cell(21, 6.3, 'Tanggal Terbit', 1, 1);
        $pdf->SetX($xRight);
        $pdf->Cell(21, 6, 'Halaman', 1, 0);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($xRight + 21, $yTop);
        $pdf->Cell(29, 6.3, '04.1-FRM-BKS', 1, 1);
        $pdf->SetX($xRight + 21);
        $pdf->Cell(29, 6.3, '001', 1, 1);
        $pdf->SetX($xRight + 21);
        $pdf->Cell(29, 6.3, date('d F Y'), 1, 1);
        $pdf->SetX($xRight + 21);
        $pdf->Cell(29, 6, '1', 1, 1);

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($xRight + 50, $yTop);
        $pdf->MultiCell(28, 3.1, "Disetujui oleh:\nManager Mutu", 1, 'C');
        $pdf->SetX($xRight + 50);
        $pdf->Cell(28, 12.8, '', 1, 1);
        $pdf->Image('assets/upload/sig.png', $xRight + 55, $yTop + 8, 20, 10);
        $pdf->SetX($xRight + 50);
        $pdf->Cell(28, 6, 'Winna Oktavia P.', 1, 1, 'C');

        $pdf->Ln(5);


        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Laporan Data Buku', 0, 1, 'C');
        $pdf->Ln(5);


        $pdf->SetFont('Arial', '', 10);

        $pdf->Cell(35, 6, 'No Keluhan', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, '032/MKT-EMIINDO/I/2026', 0, 1);

        $pdf->Cell(35, 6, 'Nama Customer', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, 'PUSKESMAS KEBAYORAN LAMA', 0, 1);

        $pdf->Cell(35, 6, 'Nama Pemohon', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, 'Fanny', 0, 1);

        $pdf->Cell(35, 6, 'Telp', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, '089531410074', 0, 1);

        $pdf->Cell(35, 6, 'Alamat', 0, 0);
        $pdf->Cell(5, 6, ':', 0, 0);
        $pdf->Cell(0, 6, 'Jl. Ciputat Raya, Kebayoran Lama, Jakarta Selatan', 0, 1);

        $pdf->Ln(5);

        $pdf->MultiCell(190, 5,
            "Deskripsi:\nLampu indikator timbangan kedap kedip sudah di ganti baterai baru",
            1
        );

        $pdf->MultiCell(190, 5,
            "Hasil Laporan:\nNew Data",
            1
        );

        $pdf->Ln(5);


        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(10,8,'No',1,0,'C');
        $pdf->Cell(60,8,'Judul Buku',1,0,'C');
        $pdf->Cell(60,8,'Penulis',1,0,'C');
        $pdf->Cell(40,8,'Genre',1,0,'C');
        $pdf->Cell(20,8,'Harga',1,0,'C');
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        $no = 1;

        foreach ($books as $b) {
            $pdf->Cell(10,8,$no++,1,0,'C');
            $pdf->Cell(60,8,$b['title'],1,0);
            $pdf->Cell(60,8,$b['author'],1,0);
            $pdf->Cell(40,8,$b['genre'],1,0);
            $pdf->Cell(20,8,number_format($b['price'],0,',','.'),1,0,'R');
            $pdf->Ln();
        }


        $pdf->Ln(5);
        $pdf->Cell(60, 6, 'Jakarta, 22 Januari 2026', 0, 1, 'C');
        $pdf->Cell(60, 6, 'Diterima oleh,', 0, 1, 'C');
        $pdf->Ln(15);
        $pdf->Cell(60, 6, 'DIAN MEDINA', 0, 1, 'C');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="buku.pdf"')
            ->setBody($pdf->Output('S'));
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

        $db = \Config\Database::connect();
        $chunkSize = 500;
        $offset = 0;
        while(true) {
            $builder = $db->table('books b')
                ->select('b.title, b.author, g.name AS genre_name, b.price')
                ->join('genres g', 'g.id = b.genre_id')
                ->orderBy('b.id ASC')
                ->limit($chunkSize)
                ->offset($offset);
    
            if(!empty($genreFilter)) {
                $builder->where('b.genre_id', $genreFilter);
            }
    
            $books = $builder->get()->getResultArray();
    
            if(empty($books)){
                break;
            }
            
            foreach($books as $book) {
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
        $db = \Config\Database::connect();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $currentRow = 2;
        $chunkSize = 500;
        $offset = 0;

        $sheet->fromArray(
            ['Title', 'Author', 'Genre', 'Price'],
            null,
            'A1'
        );
        $builder = $db->table('books b')
                ->select('b.title, b.author, g.name AS genre, b.price')
                ->join('genres g', 'g.id = b.genre_id')
                ->orderBy('b.id ASC');

        while(true) {
            $query = clone $builder;

            $data = $query->limit($chunkSize)
                    ->offset($offset)
                    ->get()
                    ->getResultArray();
            if(empty($data)) {
                break;
            }

            foreach($data as $book) {
                $sheet->fromArray([$book['title'], $book['author'], $book['genre'], $book['price']], null, 'A' . $currentRow);
                $currentRow++;
            }
            $offset += $chunkSize;
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="books.xlsx"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;


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
                'author'    => trim($title),
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

}
