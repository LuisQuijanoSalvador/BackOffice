<?php

namespace App\Exports;

use App\Models\Egreso;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EgresosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return Egreso::with(['banco', 'documento.proveedor', 'estado'])
            ->when($this->filters['fecha_inicio'] ?? null, function ($query, $fecha) {
                $query->whereDate('created_at', '>=', $fecha);
            })
            ->when($this->filters['fecha_fin'] ?? null, function ($query, $fecha) {
                $query->whereDate('created_at', '<=', $fecha);
            })
            ->when($this->filters['banco_id'] ?? null, function ($query, $bancoId) {
                $query->where('idBanco', $bancoId);
            })
            ->when($this->filters['proveedor_id'] ?? null, function ($query, $proveedorId) {
                $query->whereHas('documento.proveedor', function ($q) use ($proveedorId) {
                    $q->where('id', $proveedorId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha Registro',
            'Proveedor',
            'RUC',
            'Documento',
            'Serie',
            'Número',
            'Banco',
            'Moneda',
            'Monto',
            'Estado',
            'Usuario Creación',
        ];
    }

    public function map($egreso): array
    {
        return [
            $egreso->id,
            $egreso->created_at->format('d/m/Y H:i'),
            $egreso->documento->proveedor->razonSocial ?? 'N/A',
            $egreso->documento->proveedor->numeroDocumentoIdentidad ?? 'N/A',
            $egreso->documento->tipoDocumento->nombre ?? 'N/A',
            $egreso->documento->serie ?? 'N/A',
            $egreso->documento->numero ?? 'N/A',
            $egreso->banco->nombre ?? 'N/A',
            $egreso->moneda,
            $egreso->monto,
            $egreso->estado->descripcion ?? 'N/A',
            $egreso->idUsuarioCreacion ? \App\Models\User::find($egreso->idUsuarioCreacion)->name ?? 'N/A' : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo del encabezado
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
            // Título del reporte (fila 0 antes del encabezado)
        ];
    }

    public function title(): string
    {
        return 'Egresos';
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // Columna Monto con 2 decimales
        ];
    }
}