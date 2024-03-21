<?php
namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Venturo
{

    /**
     * Start DB transaction
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @return void
     */
    protected function beginTransaction()
    {
        DB::beginTransaction();
    }

    /**
     * Commit DB transaction
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @return void
     */
    protected function commitTransaction()
    {
        DB::commit();
    }

    /**
     * Generate nama file
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @param UploadedFile $file object file upload Illuminate\Http\UploadedFile
     * @param string $fileName custom nama file tanpa ekstensi, contohnya : PHOTO_WAHYU
     * @return void
     */
    protected function generateFileName(UploadedFile $file, string $fileName = '') :string
    {
        $fileName = empty($fileName) ? 'FILE_' . date('Ymdhis') : $fileName;

        return $fileName . '.' . $file->extension();
    }

    /**
     * Load view untuk digenerate ke PDF
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @param string $filePath path ke file blade di folder resource/views/generate/pdf
     * @param string $data data yang akan dirender pada view
     * @param array $paperSize ukuran kertas dan orientasinya
     * @return object
     */
    protected function loadPdf($filePath, $data, $paperSize = ['paper' => 'a4', 'orientation' => 'potrait'])
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('generate/pdf/' . $filePath, compact('data'));
        $pdf->setPaper($paperSize['paper'], $paperSize['orientation']);

        return $pdf;
    }

    /**
     * Download PDF ke device Pengguna
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @param string $filePath path ke file blade di folder resource/views/generate/pdf
     * @param string $data data yang akan dirender pada view
     * @param string $title judul / nama file ketika di download
     * @param array $paperSize ukuran kertas dan orientasinya
     * @return void
     */
    protected function pdfDownload($filePath, $data, $title = 'no-title.pdf', $paperSize = ['paper' => 'a4', 'orientation' => 'potrait'])
    {
        return self::loadPdf($filePath, $data, $title, $paperSize)->download($title);
    }

    /**
     * Stream PDF (view PDF di browser tanpa download file)
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @param string $filePath path ke file blade di folder resource/views/generate/pdf
     * @param string $data data yang akan dirender pada view
     * @param string $title judul / nama file ketika di download
     * @param array $paperSize ukuran kertas dan orientasinya
     * @return void
     */
    protected function pdfView($filePath, $data, $title = 'no-title.pdf', $paperSize = ['paper' => 'a4', 'orientation' => 'potrait'])
    {
        return self::loadPdf($filePath, $data, $title, $paperSize)->stream($title);
    }

    /**
     * Print Halaman dari folder resources/views/generate
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @param string $filePath path ke file blade di folder resource/views/generate/
     * @param string $data data yang akan dirender pada view
     * @return void
     */
    protected function print($filePath, $data)
    {
        $view = (string) view('generate/' . $filePath, compact('data'));
        $view .= '<script>window.print();</script>';

        return $view;
    }

    /**
     * Rollback DB transaction
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     *
     * @return void
     */
    protected function rollbackTransaction()
    {
        DB::rollBack();
    }
}

