<?php

namespace App\Traits;

use PDF;

trait OrderPdf
{
    function order_pdf($view, $order) {
        // Limpia el buffer de salida para cargar correctamente el PDF
        if (ob_get_level()) ob_end_clean();

        $config = ['instanceConfigurator' => function($mpdf) {
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->autoLangToFont = true;
        }];

        try {
            $pdf = PDF::loadView($view, compact('order'), [], $config);

            return $pdf->stream($order->order_number.'.pdf');

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getPDF($view, $data, $title)
    {
        $config = ['instanceConfigurator' => function($mpdf) {
            $mpdf->autoScriptToLang = true;
            $mpdf->baseScript = 1;
            $mpdf->autoVietnamese = true;
            $mpdf->autoArabic = true;
            $mpdf->autoLangToFont = true;
        }];

        $pdf = PDF::loadView($view, $data,[],$config);
        return $pdf->stream($title.'.pdf');
    }



}
