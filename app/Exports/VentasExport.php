<?php

namespace App\Exports;

use App\Models\Ventas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class VentasExport implements FromView, WithStyles
{
    public $fechaInicio, $fechaFin, $idCliente;

    public function __construct($fecIni, $fecFin, $idClie)
    {
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;
        $this->idCliente = $idClie;
    }

    public function view(): View
    {
        // dd('Si llego aca');
        $query = DB::table('vista_ventas');

        if($this->fechaInicio and $this->fechaFin and $this->idCliente){
            $query->where('idCliente',$this->idCliente)
                   ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin]);

            // return view('exports.reportes.ventas', [
            //     'ventass' => DB::table('vista_ventas')
            //     ->where('idCliente',$this->idCliente)
            //     ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
            //     ->orderby('FechaEmision')
            //     ->orderBy('pasajero')
            //     ->orderBy('tipo')
            //     ->get()
            // ]);
        }
        if($this->fechaInicio and $this->fechaFin and !$this->idCliente){
            $query->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin]); 
            
            // return view('exports.reportes.ventas', [
            //     'ventass' => DB::table('vista_ventas')
            //     ->whereBetween('FechaEmision',[$this->fechaInicio, $this->fechaFin])
            //     ->orderby('FechaEmision')
            //     ->orderBy('pasajero')
            //     ->orderBy('tipo')
            //     ->get()
            // ]);
        }
        $datos = $query
                ->orderBy('FechaEmision')
                ->orderBy('pasajero')
                ->orderBy('tipo')
                ->get();
        
        return view('exports.reportes.ventas',['ventass' => $datos]);
        
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

        
        $sheet->getStyle('A1:AB1')->applyFromArray([
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
