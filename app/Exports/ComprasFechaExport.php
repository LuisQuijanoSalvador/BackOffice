<?php

namespace App\Exports;

use App\Models\Compra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Opcional: para autoajustar el ancho de las columnas
use Maatwebsite\Excel\Concerns\WithMapping; // Para mapear los datos de la colección
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles; // Importa esta interfaz
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Importa Worksheet
use PhpOffice\PhpSpreadsheet\Style\Fill; // Importa Fill
use PhpOffice\PhpSpreadsheet\Style\Border; // Importa Border

class ComprasFechaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles 
{
    protected $compras;

    public function __construct($compras)
    {
        $this->compras = $compras;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->compras;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tipo Documento',
            'Serie',
            'Número',
            'Fecha Emisión',
            'RUC Proveedor',
            'Razón Social Proveedor',
            'Moneda',
            'Afecto',
            'Inafecto',
            'IGV',
            'Total',
            'Observación',
            'Estado',
        ];
    }

    /**
     * @var Compra $compra
     */
    public function map($compra): array
    {
        return [
            $compra->id,
            $compra->tipoDocumentoR->descripcion ?? 'N/A',
            $compra->serie,
            $compra->numero,
            \Carbon\Carbon::parse($compra->fechaEmision)->format('d/m/Y'),
            $compra->proveedor->numeroDocumentoIdentidad ?? 'N/A',
            $compra->proveedor->razonSocial ?? 'N/A',
            $compra->moneda,
            number_format($compra->subTotal, 2, '.', ''), // Formato para Excel
            number_format($compra->inafecto, 2, '.', ''),
            number_format($compra->igv, 2, '.', ''),
            number_format($compra->total, 2, '.', ''),
            $compra->observacion,
            $compra->estadoR->descripcion ?? 'N/A',
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
        $styles = [];

        // Estilos para la fila de encabezados (fila 1)
        $styles[1] = [
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
        ];

        // Estilos para todas las celdas de datos (incluyendo la cabecera para los bordes)
        // Se aplica a todo el rango para asegurar que todos los bordes estén presentes
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

        // Estilos condicionales para filas con estado "Anulado"
        // Las filas de datos comienzan desde la fila 2 (después del encabezado)
        $rowIndex = 2;
        foreach ($this->compras as $compra) {
            // Asumiendo que el nombre del estado 'Anulado' es 'Anulado'
            if (($compra->estadoR->descripcion ?? '') === 'ANULADO') {
                $styles[$rowIndex] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'fc4b3f'], // Rojo claro para filas anuladas (puedes elegir otro rojo)
                                                              // O 'FF0000' para un rojo más intenso
                    ],
                ];
            }
            $rowIndex++;
        }

        return $styles;
    }
}