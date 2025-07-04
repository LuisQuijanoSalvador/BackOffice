<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class AbonosReporteExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $abonos;

    public function __construct($abonos)
    {
        $this->abonos = $abonos;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->abonos;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número Abono',
            'Fecha Abono',
            'Monto Total Abonado',
            'Cliente',
            'Documentos de Cargo',
        ];
    }

    /**
     * @var mixed $abono
     */
    public function map($abono): array
    {
        return [
            $abono->numeroAbono,
            Carbon::parse($abono->fechaAbono)->format('d/m/Y'),
            number_format($abono->montoTotalAbonado, 2, '.', ''),
            $abono->nombreCliente,
            $abono->documentosCargos,
        ];
    }

    /**
     * Aplica estilos a la hoja de cálculo.
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Estilos para la fila de encabezados (fila 1)
        $styles = [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'], // Blanco
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D3557'], // Azul Oscuro
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'], // Negro
                    ],
                ],
            ],
        ];

        // Estilos para todas las celdas de datos (bordes)
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $fullRange = 'A1:' . $lastColumn . $lastRow;

        $styles[$fullRange] = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // Negro
                ],
            ],
        ];

        return $styles;
    }
}
