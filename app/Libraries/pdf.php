<?php
namespace App\Libraries;
use Fpdf\Fpdf;

class PDF extends Fpdf
{
    function BooksTable($data) 
    {
        //Header
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 8, 'No',1,0,'C');
        $this->Cell(60, 8, 'Judul Buku',1,0,'C');
        $this->Cell(60, 8, 'Penulis',1,0,'C');
        $this->Cell(40, 8, 'Genre',1,0,'C');
        $this->Cell(20, 8, 'Harga',1,1,'C');

        //Data 
        $this->SetFont('Arial', '', 10);
        $no = 1;
        foreach($data as $row) {
            $this->Cell(10, 8, $no++,1,0,'C');
            $this->Cell(60, 8,$row['title'],1);
            $this->Cell(60, 8,$row['author'],1);
            $this->Cell(40, 8,$row['genre'],1);
            $this->Cell(20, 8, number_format($row['price'],0,',','.'),1,1,'R');
        }
    }
}

