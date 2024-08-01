<?php

namespace App\Exports;

use App\Models\Cargo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Cliente;
use App\Models\Counter;
use Illuminate\Support\Facades\DB;


class CargosExport implements  FromView, WithStyles
{
    public $idCliente, $fechaInicio, $fechaFin, $razonSocial;

    public function __construct($id, $fecIni, $fecFin)
    {
        $this->idCliente = $id;
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;

        
    }

    public function view(): View
    {
        $cliente = Cliente::find($this->idCliente);
        $counter = Counter::find($cliente->counter);
        $suma = Cargo::where('idCliente', $this->idCliente)
                        ->where('idEstado',1)
                        ->where('saldo','>',0)
                        ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
                        ->sum('total');
        // dd($suma);
        $this->razonSocial = $cliente->razonSocial;
        return view('exports.ctasCobrar.estado-cuentas', [
            'cargos' => DB::table('vista_estadocuenta')
                            ->where('idCliente', $this->idCliente)
                            ->whereBetween('fechaEmision',[$this->fechaInicio, $this->fechaFin])
                            ->get()
        ],compact('cliente','counter','suma'));
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

        $sheet->getStyle('A2:K2')->applyFromArray([
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
        $sheet->getStyle('P2:Q6')->applyFromArray([
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
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle('R2:R6')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => '9',
                'color' => [
                    'argb' => '000000',
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '9bc3ff',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle('B11:Q11')->applyFromArray([
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
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);
    }
}
