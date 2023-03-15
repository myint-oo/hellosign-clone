<?php

use App\Extensions\TCPDIExtension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin', function () {
    $original_filename = 'MAS Screening - bin-3';
    $store_in = public_path("pdf-images/{$original_filename}.png");
    $pdf = new \Spatie\PdfToImage\Pdf("{$original_filename}.pdf");
    $pdf->saveImage($store_in);

    return view('welcome', [
        'file_path' => "pdf-images/{$original_filename}.png"
    ]);
});

Route::get('/user', function () {
    $original_filename = 'MAS Screening - bin-3';

    return view('welcome-user', [
        'file_path' => "pdf-images/{$original_filename}.png",
        'x' => session('x'),
        'y' => session('y'),
    ]);
});

Route::post('/send', function(Request $request) {
    $x = $request->x;
    $y = $request->y;
    session(['x' => $x > 0 ? $x : 0, 'y' => $y > 0 ? $y : 0]);
    
    return redirect()->to('/user');
});

Route::post('/sign', function(Request $request) {
    $x = floatval(session('x'));

    $y = session('y');
    /** y axis has a margin but it's not a constant margin instead it's more like 0.2% of each position */
    $y = floatval($y) - ($y * .2);

    $pdf = new TCPDI('L', 'px', PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Add a page from a PDF by file path.
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $pdf->setSourceFile('MAS Screening - bin-3.pdf');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    $pdf->Image('myint_oo_signature_62f4c002732ec.png', $x, $y, '', '', '', '', '', false, 300);

    $pdf->Output('WorksheetTest.pdf', 'I');
});


Route::get('/add-footer', function(Request $request) {
    $pdf = new TCPDIExtension('L', 'px', PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->AddPage();
    $pdf->setSourceFile('MAS Screening - bin-3.pdf');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->Output('WorksheetTest.pdf', 'I');
});

Route::get('/combine-two-pdf', function(Request $request) {
    $pdf = new TCPDIExtension('L', 'pt', PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->setSourceFile('MAS Screening - bin-3.pdf');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->AddPage();
    $pdf->setSourceFile('MAS Screening - bin-3.pdf');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);
    $pdf->Output('WorksheetTest.pdf', 'I');
});

Route::get('/delete-last-page', function(Request $request) {
    $pdf = new TCPDIExtension('L', 'pt', PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf_data = file_get_contents('WorksheetTest.pdf'); // Simulate only having raw data available.
    $page_count = $pdf->setSourceData($pdf_data);

    // loads all pages from the source document
    for ($i = 1; $i <= $page_count; $i++) {
        $tplidx = $pdf->importPage($i);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->useTemplate($tplidx);
    }
    
    $last_page = $pdf->getPage();
    $pdf->deletePage($last_page);

    $pdf->Output('WorksheetTest.pdf', 'I');
});