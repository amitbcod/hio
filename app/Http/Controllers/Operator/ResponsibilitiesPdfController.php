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

    public function agreement()
    {
        // Generate a simple HIO Service Agreement PDF
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, "HIO Service Agreement\n\nThis document describes the HIO Service Agreement options and terms.\n\n- Listing Only\n- OTO\n- Widget Only\n- OTO + Widget\n- Full Service\n\nPlease contact support for full legal terms.");
        $pdf->Output('hio_service_agreement.pdf', 'I');
        exit;
    }
}
