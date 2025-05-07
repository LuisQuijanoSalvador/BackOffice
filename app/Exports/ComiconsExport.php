<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class ComiconsExport implements FromView, WithStyles
{
    public $comi;

    public function __construct($comisiones)
    {
        $this->comi = $comisiones;
    }

    public function view(): View
    {
        return view('exports.reportes.comisionCons', [
            'ventass' => $this->comi
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos y colores aquí
        $sheet->getStyle('A1:Z60')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ],
        ]);

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => '20',
                'color' => [
                    'argb' => '06136e',
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ],
        ]);
        $sheet->getStyle('A4:K4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => '9',
                'color' => [
                    'argb' => 'FFFFFF',
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '06136e',
                ],
            ],
        ]);
    }
}
