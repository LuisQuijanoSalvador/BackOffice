<?php

namespace App\Exports;

use App\Models\Ventas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class AlltechExport implements FromView, WithStyles
{
    public $fechaInicio, $fechaFin, $idCliente;

    public function __construct($fecIni, $fecFin, $idClie)
    {
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;
        $this->idCliente = $idClie;
    }

    public function  view(): View
    {
        if($this->fechaInicio and $this->fechaFin and $this->idCliente){
            return view('exports.reportes.alltech', [
                'ventass' => DB::table('vista_alltech')
                ->where('idCliente',$this->idCliente)
                ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                ->orderby('FechaEmision')
                ->get()
            ]);
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idCliente){
            return view('exports.reportes.alltech', [
                'ventass' => DB::table('vista_alltech')
                ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
                ->orderby('FechaEmision')
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

        
        $sheet->getStyle('A1:U1')->applyFromArray([
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
