<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class SegmentosExport implements FromView, WithStyles
{
    public $fechaInicio, $fechaFin, $idConsolidador;

    public function __construct($fecIni, $fecFin, $idCons)
    {
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;
        $this->idConsolidador = $idCons;
    }

    public function view(): View
    {
        if($this->fechaInicio and $this->fechaFin and $this->idConsolidador){
            return view('exports.reportes.segmentos', [
                'ventass' => DB::table('vista_segmentos')
                ->where('idConsolidador',$this->idConsolidador)
                ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFin])
                ->orderby('fechaEmision')
                ->orderby('numeroBoleto')
                ->get()
            ]);
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idConsolidador){
            return view('exports.reportes.segmentos', [
                'ventass' => DB::table('vista_segmentos')
                ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFin])
                ->orderby('fechaEmision')
                ->orderby('numeroBoleto')
                ->get()
            ]);
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicar estilos y colores aquí
        $sheet->getStyle('A1:AE150')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ],
            'font' => [
                'bold' => true,
                'size' => '9',
                'color' => [
                    'argb' => '000000',
                ],
            ],
        ]);

        
        $sheet->getStyle('A1:M1')->applyFromArray([
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
