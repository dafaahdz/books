<?php
namespace App\Services;

use App\Libraries\PDF;

class BookPdfService
{
    public function generate(array $books): string
    {
        $pdf = new PDF('P', 'mm', 'A4');
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
        $pdf->BooksTable($books);


        $pdf->Ln(5);
        $pdf->Cell(60, 6, 'Jakarta, 22 Januari 2026', 0, 1, 'C');
        $pdf->Cell(60, 6, 'Diterima oleh,', 0, 1, 'C');
        $pdf->Ln(15);
        $pdf->Cell(60, 6, 'DIAN MEDINA', 0, 1, 'C');

        return $pdf->Output('S');
    }
}