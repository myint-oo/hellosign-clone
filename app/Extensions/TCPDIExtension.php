<?php

namespace App\Extensions;

use TCPDI;

class TCPDIExtension extends TCPDI
{
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('helvetica', '', 12);
        // Page number
        $this->Cell(0, 10, 'Doc ID: dskdiek823892932', 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}