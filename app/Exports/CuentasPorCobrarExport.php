<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CuentasPorCobrarExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $cargos;
    protected $moneda;

    public function __construct($cargos, $moneda)
    {
        $this->cargos = $cargos;
        $this->moneda = $moneda;
    }

    public function collection()
    {
        return $this->cargos;
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Documento Identidad',
            'Monto Total',
            'Deuda Vencida',
            'Días de Atraso',
            'Total Cargos'
        ];
    }

    public function map($cargo): array
    {
        return [
            $cargo->razonSocial,
            $cargo->numeroDocumentoIdentidad,
            (float)$cargo->monto_total,
            (float)$cargo->deuda_vencida,
            (int)$cargo->max_dias_atraso,
            (int)$cargo->total_cargos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '36648B']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Cuentas por Cobrar ' . $this->moneda;
    }
}