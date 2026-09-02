<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfReportService
{
    /**
     * Genera PDF con resultados de conciliación
     */
    public function generateReport(Collection $results, string $lastFourDigits, string $fechaInicio, string $fechaFin): string
    {
        $pdf = Pdf::loadView('reports.conciliacion-tarjetas', [
            'results' => $results,
            'lastFourDigits' => $lastFourDigits,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'totalMovimientos' => $results->count(),
            'totalCoincidencias' => $results->where('coincidencia', true)->count(),
            'totalSinCoincidencia' => $results->where('coincidencia', false)->count(),
            'montoTotal' => $results->sum('monto_tarjeta'),
        ]);
        
        $filename = 'conciliacion_tc_' . $lastFourDigits . '_' . date('Ymd_His') . '.pdf';
        $pdf->save(storage_path('app/public/' . $filename));
        
        return $filename;
    }
}