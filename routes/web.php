<?php

use Illuminate\Support\Facades\Route;
use App\Models\Certificate;

Route::get('/', function () {
    return view('welcome');
});

// Certificate template preview (HTML)
Route::get('/certificate-preview/{id}/{template?}', function ($id, $template = 'iedc') {
    $certificate = Certificate::findOrFail($id);
    return view("certificates.{$template}", ['certificate' => $certificate]);
});

// Certificate PDF preview
Route::get('/certificate-pdf/{id}/{template?}', function ($id, $template = 'iedc') {
    $certificate = Certificate::findOrFail($id);
    $html = view("certificates.{$template}", ['certificate' => $certificate])->render();
    
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    return response($dompdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="certificate-preview.pdf"');
});
