<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Fpdf\Fpdf;

class TestPdf extends BaseController
{
    public function index()
    {
        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $curX = $pdf->GetX();

        $pdf->Cell(47, 20, 'LOGO', 1, 0, 'C');
        $pdf->Cell(94, 20, 'Judul Form', 1, 0, 'C');
        $pdf->Cell(47, 20, 'DOK', 1, 1, 'C');
        $pdf->SetX(151);
        $pdf->Cell(47, 10, 'REV :', 1, 1, 'L');
        $pdf->SetX(151);
        $pdf->Cell(47, 10, 'TGl :', 1, 1, 'L');

        


        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="produk.pdf"')
            ->setBody($pdf->output('S'));
    }
}
