<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class ResponsibilitiesPdfController extends Controller
{
    public function download()
    {
        // For demonstration, generate a simple PDF with static text
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'Operator Responsibilities\n\n1. Provide accurate information.\n2. Comply with all legal requirements.\n3. Maintain up-to-date records.\n4. Cooperate with platform audits.');
        $pdf->Output('responsibilities.pdf', 'D');
        exit;
    }
}
