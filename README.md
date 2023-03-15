# What is it?
This package contains a set of code examples(bare minimum) for signing existing PDF documents. The aim is to provide features similar to Hellosign's platform. The examples cover different aspects, such as:

- Identifying where to sign the document with an administrator's input.
- Enabling users to sign documents at designated positions.
- Adding signatures images to positions specified by users.
- Adding footers to all pages of a PDF.
- Combining multiple PDF documents.
- Deleting specific pages from an existing PDF.

Overall, this package provides a comprehensive solution for signing PDF documents and related functionalities.

The examples in this package use PHP, but similar techniques can be applied to other programming languages with PDF manipulation libraries.


# Dependencies
- PHP (Laravel is optional but helpful for testing)
- [propa/tcpdi](https://github.com/pauln/tcpdi): loads existing PDF files (dependent on tcpdf with full functionality)
- tcpdf: loaded by [propa/tcpdi](https://github.com/pauln/tcpdi)
- [spatie/pdf-to-image](https://github.com/spatie/pdf-to-image): converts PDFs to images
- [Jquery Droppable](https://jqueryui.com/droppable)

Overall, these dependencies provide the necessary tools to load, manipulate, and convert PDF files in a PHP environment.

# Features

## Identifying where to sign the document with an administrator's input

### Code
```php
    // load the pdf
    $original_filename = 'filename';
    $store_in = public_path("pdf-images/{$original_filename}.png");
    $pdf = new \Spatie\PdfToImage\Pdf("{$original_filename}.pdf");
    $pdf->saveImage($store_in);

    return view('welcome', [
        'file_path' => "pdf-images/{$original_filename}.png"
    ]);
    
    
    // send the pdf
    $x = $request->x;
    $y = $request->y;
    session(['x' => $x > 0 ? $x : 0, 'y' => $y > 0 ? $y : 0]);
    
    return redirect()->to('/user');
```

### Explaination

- inside ```routes/web.php``` the route ```/admin``` is meant for this purpose.
- what we do there is we change pdf to image and sent the image to the ```welcome.blade.php```.
- then on the image we use query to drag and drop desired position.
- then we submit the pdf to store the position of x and y in session for later use.

## Enabling users to sign documents at designated positions

### Code

```php
    // load for user
    $original_filename = 'MAS Screening - bin-3';

    return view('welcome-user', [
        'file_path' => "pdf-images/{$original_filename}.png",
        'x' => session('x'),
        'y' => session('y'),
    ]);
```

### What it does

- after admin has marked the position from ```/admin``` route
- the x and y position is stored and session and sent it to the user menu with ```/user``` route along with the pdf file itself
- only then in frontend we add the html **Click here to signature** html element on the provided x, y position

## Adding signatures images to positions specified by users

### Code

```php
    $x = floatval(session('x'));
    $y = session('y');
    /** y axis has a margin but it's not a constant margin instead it's more like 0.2% of each position */
    $y = floatval($y) - ($y * .2);

    $pdf = new TCPDI('L', 'px', PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Add a page from a PDF by file path.
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $pdf->setSourceFile('/path/to/file');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    $pdf->Image('myint_oo_signature_62f4c002732ec.png', $x, $y, '', '', '', '', '', false, 300);

    $pdf->Output('WorksheetTest.pdf', 'I');
```

### What it does

- after user has added their signature, the position won't be change because user should not normally has the ability to change the position
- in ```routes/web.php``` route ```/sign``` we use **tcpdi** method to load the existing pdf and **tcpdf** ```Image``` method to add the signature image.

## Adding footers to all pages of a PDF.

### Code
```php
    $pdf = new TCPDIExtension('L', 'px', PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->AddPage();
    $pdf->setSourceFile('/path/to/file');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->Output('WorksheetTest.pdf', 'I');
```

### What it does

to customize the footer we need to extends the TCPDI and then overwrites ```Footer``` method.


## Combining multiple PDF documents

### Code

```php
    $pdf = new TCPDIExtension('L', 'pt', PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    $pdf->setSourceFile('/path/to/file');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    $pdf->AddPage();
    $pdf->setSourceFile('/path/to/another/file');
    $idx = $pdf->importPage(1);
    $pdf->useTemplate($idx);
    $pdf->Output('WorksheetTest.pdf', 'I');
```

## Deleting specific pages from an existing PDF.

### Code

```php
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
```
