<?php

namespace App\Exports;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Counter;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class CargosSheet implements FromView, WithStyles
{
    public $idCliente, $fechaInicio, $fechaFin, $razonSocial, $esVacio;

    public function __construct($id, $fecIni, $fecFin, $esVacio = false)
    {
        $this->idCliente = $id;
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;
        $this->esVacio = $esVacio;
    }

    public function view(): View
    {
        if ($this->esVacio) {
            return view('exports.ctasCobrar.estado-cuentas-vacio'); // vista alternativa
        }

        $cliente = Cliente::find($this->idCliente);
        $counter = Counter::find($cliente->counter);
        
        $suma = Cargo::where('idCliente', $this->idCliente)
            ->where('idEstado', 1)
            ->where('saldo', '>', 0)
            ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->sum('total');

        $this->razonSocial = $cliente->razonSocial;

        $vista = $cliente->tipoFacturacion == 1 
            ? 'vista_estadocuenta' 
            : 'vista_estadocuenta_acumulado';

        $cargos = DB::table($vista)
            ->where('idCliente', $this->idCliente)
            ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->get();

        return view('exports.ctasCobrar.estado-cuentas', [
            'cargos' => $cargos,
            'cliente' => $cliente,
            'counter' => $counter,
            'suma' => $suma,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Título de la hoja = Razón social del cliente
        $sheet->setTitle(substr($this->razonSocial ?? 'Cliente', 0, 31)); // Excel limita a 31 chars

        // Aquí van tus estilos originales (A1:Z60, A2:K2, etc.)
        $sheet->getStyle('A1:Z60')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFF'],
            ],
        ]);

        $sheet->getStyle('A2:K2')->applyFromArray([
            'font' => ['bold' => true, 'size' => '20', 'color' => ['argb' => '06136e']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFF'],
            ],
        ]);

        $sheet->getStyle('P2:Q6')->applyFromArray([
            'font' => ['bold' => true, 'size' => '9', 'color' => ['argb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '06136e'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle('R2:R6')->applyFromArray([
            'font' => ['bold' => true, 'size' => '9', 'color' => ['argb' => '000000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '9bc3ff'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle('B11:S11')->applyFromArray([
            'font' => ['bold' => true, 'size' => '9', 'color' => ['argb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '06136e'],
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