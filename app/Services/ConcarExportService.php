<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CuentaContable;
use App\Models\Subdiario;
use App\Models\Proveedor;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ConcarExportService
{
    private $spreadsheet;
    private $sheet;
    private $row = 1;
    private $comprobanteCounter = 1;

    // Cuentas contables fijas según el sistema
    const CUENTA_IGV = '401111';
    const CUENTA_POR_PAGAR = '421202';
    const CUENTA_DETRACCION = '421203';
    const CUENTA_HONORARIOS = '414102';

    // Códigos de tipo de documento estándar
    const TIPO_DOC_MAP = [
        '01' => 'FT', // Factura
        '03' => 'BK', // Boleta
        '07' => 'NC', // Nota de Crédito
        '08' => 'ND', // Nota de Débito
        'RH' => 'RH', // Recibo por Honorarios
    ];

    public function generate(array $comprasIds, string $fechaInicio, string $fechaFin): string
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Asiento Compras');

        $this->setHeaders();

        // CORRECCIÓN: Quitamos 'documento' del with()
        $compras = Compra::with(['proveedor', 'cuentaContable', 'subdiario'])
            ->whereIn('id', $comprasIds)
            ->whereBetween('fechaEmision', [$fechaInicio, $fechaFin])
            ->orderBy('fechaEmision', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($compras as $compra) {
            $this->processCompra($compra);
        }

        $this->adjustColumnWidths();

        $filename = 'concar_compras_' . Carbon::parse($fechaInicio)->format('Ymd') . '_' .
            Carbon::parse($fechaFin)->format('Ymd') . '_' . time() . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);

        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);

        return $filename;
    }

    private function setHeaders(): void
    {
        $headers = [
            'Sub Diario',
            'Número de Comprobante',
            'Fecha de Comprobante',
            'Código de Moneda',
            'Glosa Principal',
            'Tipo de Cambio',
            'Tipo de Conversión',
            'Flag de Conversión de Moneda',
            'Fecha Tipo de Cambio',
            'Cuenta Contable',
            'Código de Anexo',
            'Código de Centro de Costo',
            'Debe / Haber',
            'Importe Original',
            'Importe en Dólares',
            'Importe en Soles',
            'Tipo de Documento',
            'Número de Documento',
            'Fecha de Documento',
            'Fecha de Vencimiento',
            'Código de Area',
            'Glosa Detalle',
            'Código de Anexo Auxiliar',
            'Medio de Pago',
            'Tipo de Documento de Referencia',
            'Número de Documento Referencia',
            'Fecha Documento Referencia',
            'Nro Máq. Registradora Tipo Doc. Ref.',
            'Base Imponible Documento Referencia',
            'IGV Documento Provisión',
            'Tipo Referencia en estado MQ',
            'Número Serie Caja Registradora',
            'Fecha de Operación',
            'Tipo de Tasa',
            'Tasa Detracción/Percepción',
            'Importe Base Detracción/Percepción Dólares',
            'Importe Base Detracción/Percepción Soles',
            'Tipo Cambio para F',
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $this->sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Estilo de encabezados
        $this->sheet->getStyle('A1:AL1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        $this->sheet->getRowDimension(1)->setRowHeight(30);
        $this->row = 2;
    }

    private function processCompra(Compra $compra): void
    {
        $subdiario = $compra->subdiario;
        $cuentaGasto = $compra->cuentaContable;
        $proveedor = $compra->proveedor;

        if (!$subdiario || !$cuentaGasto || !$proveedor) {
            return;
        }

        $mes = Carbon::parse($compra->fechaEmision)->format('m');
        $numeroComprobante = $mes . str_pad($this->comprobanteCounter++, 4, '0', STR_PAD_LEFT);
        $fechaComprobante = Carbon::parse($compra->fechaEmision)->format('d/m/Y');
        $moneda = $compra->moneda === 'USD' ? 'US' : 'SO';
        $glosa = strtoupper($proveedor->razonSocial) . '-' . ($compra->tipoDocumento ?? 'FT') . '-' .
            ($compra->serie ?? '') . '-' . ($compra->numero ?? '');
        $tipoCambio = $compra->tipoCambio ?? 0;
        $tipoConversion = 'V';
        $flagConversion = 'S';
        $fechaTipoCambio = $compra->tipoCambio > 0 ? $fechaComprobante : '';
        $ruc = $proveedor->numeroDocumentoIdentidad ?? '';

        // CORRECCIÓN: Usamos el campo 'tipoDocumento' que existe en la tabla compras
        $tipoDoc = self::TIPO_DOC_MAP[$compra->tipoDocumento ?? '01'] ?? 'FT';

        $numeroDoc = ($compra->serie ?? '') . '-' . ($compra->numero ?? '');
        $fechaDoc = Carbon::parse($compra->fechaEmision)->format('d/m/Y');
        $fechaVencimiento = Carbon::parse($compra->fechaVencimiento ?? $compra->fechaEmision)->format('d/m/Y');
        $glosaDetalle = $glosa;

        // Determinar el tipo de asiento según los montos
        $tieneIgv = $compra->igv > 0;
        $tieneDetraccion = ($compra->montoDetraccion ?? 0) > 0;

        // Para Recibos por Honorarios, usualmente el tipoDocumento es '03' (Boleta) o un código específico.
        // Si en tu sistema usas un campo extra o el nombre del proveedor para identificar RH, ajústalo aquí.
        // Por ahora, asumimos que si el tipo de documento es '03' y no tiene IGV, o si el usuario lo marcó así.
        // (Puedes ajustar esta lógica si tienes un campo específico para RH en compras).
        $esReciboHonorarios = false; // Ajusta esta condición si tienes un identificador claro de RH

        if ($esReciboHonorarios) {
            $this->addReciboHonorarios(
                $subdiario->codigo,
                $numeroComprobante,
                $fechaComprobante,
                $moneda,
                $glosa,
                $tipoCambio,
                $tipoConversion,
                $flagConversion,
                $fechaTipoCambio,
                $cuentaGasto->cuenta,
                $ruc,
                $tipoDoc,
                $numeroDoc,
                $fechaDoc,
                $fechaVencimiento,
                $glosaDetalle,
                $compra->total
            );
        } elseif ($tieneIgv && $tieneDetraccion) {
            $this->addFacturaConDetraccion(
                $subdiario->codigo,
                $numeroComprobante,
                $fechaComprobante,
                $moneda,
                $glosa,
                $tipoCambio,
                $tipoConversion,
                $flagConversion,
                $fechaTipoCambio,
                $cuentaGasto->cuenta,
                $ruc,
                $tipoDoc,
                $numeroDoc,
                $fechaDoc,
                $fechaVencimiento,
                $glosaDetalle,
                $compra->subTotal,
                $compra->igv,
                $compra->total,
                $compra->montoDetraccion
            );
        } elseif ($tieneIgv) {
            $this->addFacturaSimple(
                $subdiario->codigo,
                $numeroComprobante,
                $fechaComprobante,
                $moneda,
                $glosa,
                $tipoCambio,
                $tipoConversion,
                $flagConversion,
                $fechaTipoCambio,
                $cuentaGasto->cuenta,
                $ruc,
                $tipoDoc,
                $numeroDoc,
                $fechaDoc,
                $fechaVencimiento,
                $glosaDetalle,
                $compra->subTotal,
                $compra->igv,
                $compra->total
            );
        } else {
            $this->addFacturaSinIgv(
                $subdiario->codigo,
                $numeroComprobante,
                $fechaComprobante,
                $moneda,
                $glosa,
                $tipoCambio,
                $tipoConversion,
                $flagConversion,
                $fechaTipoCambio,
                $cuentaGasto->cuenta,
                $ruc,
                $tipoDoc,
                $numeroDoc,
                $fechaDoc,
                $fechaVencimiento,
                $glosaDetalle,
                $compra->total
            );
        }
    }

    private function addFacturaSimple(
        $subDiario,
        $numComp,
        $fechaComp,
        $moneda,
        $glosa,
        $tipoCambio,
        $tipoConv,
        $flagConv,
        $fechaTC,
        $cuentaGasto,
        $ruc,
        $tipoDoc,
        $numDoc,
        $fechaDoc,
        $fechaVenc,
        $glosaDetalle,
        $subtotal,
        $igv,
        $total
    ): void {
        // Línea 1: Gasto en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            $cuentaGasto,
            $ruc,
            '100',
            'D',
            $subtotal,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 2: IGV en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_IGV,
            '',
            '0',
            'D',
            $igv,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 3: Cuenta por Pagar en el HABER
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_POR_PAGAR,
            $ruc,
            '0',
            'H',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            $fechaVenc,
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
    }

    private function addFacturaConDetraccion(
        $subDiario,
        $numComp,
        $fechaComp,
        $moneda,
        $glosa,
        $tipoCambio,
        $tipoConv,
        $flagConv,
        $fechaTC,
        $cuentaGasto,
        $ruc,
        $tipoDoc,
        $numDoc,
        $fechaDoc,
        $fechaVenc,
        $glosaDetalle,
        $subtotal,
        $igv,
        $total,
        $detraccion
    ): void {
        // Línea 1: Gasto en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            $cuentaGasto,
            $ruc,
            '100',
            'D',
            $subtotal,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 2: IGV en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_IGV,
            '',
            '0',
            'D',
            $igv,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 3: Cuenta por Pagar en el HABER (total)
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_POR_PAGAR,
            $ruc,
            '0',
            'H',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            $fechaVenc,
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 4: Ajuste de Detracción en el DEBE (disminuye cuenta por pagar)
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_POR_PAGAR,
            $ruc,
            '0',
            'D',
            $detraccion,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 5: Cuenta de Detracción por Pagar en el HABER
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_DETRACCION,
            $ruc,
            '0',
            'H',
            $detraccion,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            $fechaVenc,
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
    }

    private function addFacturaSinIgv(
        $subDiario,
        $numComp,
        $fechaComp,
        $moneda,
        $glosa,
        $tipoCambio,
        $tipoConv,
        $flagConv,
        $fechaTC,
        $cuentaGasto,
        $ruc,
        $tipoDoc,
        $numDoc,
        $fechaDoc,
        $fechaVenc,
        $glosaDetalle,
        $total
    ): void {
        // Línea 1: Gasto en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            $cuentaGasto,
            $ruc,
            '100',
            'D',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 2: Cuenta por Pagar en el HABER
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_POR_PAGAR,
            $ruc,
            '0',
            'H',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            $fechaVenc,
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
    }

    private function addReciboHonorarios(
        $subDiario,
        $numComp,
        $fechaComp,
        $moneda,
        $glosa,
        $tipoCambio,
        $tipoConv,
        $flagConv,
        $fechaTC,
        $cuentaGasto,
        $ruc,
        $tipoDoc,
        $numDoc,
        $fechaDoc,
        $fechaVenc,
        $glosaDetalle,
        $total
    ): void {
        // Línea 1: Gasto en el DEBE
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            $cuentaGasto,
            $ruc,
            '100',
            'D',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        // Línea 2: Honorarios por Pagar en el HABER
        $this->addRow([
            $subDiario,
            $numComp,
            $fechaComp,
            $moneda,
            $glosa,
            $tipoCambio,
            $tipoConv,
            $flagConv,
            $fechaTC,
            self::CUENTA_HONORARIOS,
            $ruc,
            '0',
            'H',
            $total,
            0,
            0,
            $tipoDoc,
            $numDoc,
            $fechaDoc,
            '',
            '',
            $glosaDetalle,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);
    }

    private function addRow(array $data): void
    {
        $col = 'A';
        foreach ($data as $value) {
            $this->sheet->setCellValue($col . $this->row, $value);
            $col++;
        }

        // Estilo de fila
        $this->sheet->getStyle('A' . $this->row . ':AL' . $this->row)->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alinear montos a la derecha
        $this->sheet->getStyle('N' . $this->row . ':P' . $this->row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->sheet->getStyle('N' . $this->row . ':P' . $this->row)->getNumberFormat()->setFormatCode('#,##0.00');

        $this->row++;
    }

    private function adjustColumnWidths(): void
    {
        $widths = [
            'A' => 12,
            'B' => 15,
            'C' => 12,
            'D' => 8,
            'E' => 40,
            'F' => 12,
            'G' => 8,
            'H' => 8,
            'I' => 12,
            'J' => 15,
            'K' => 18,
            'L' => 8,
            'M' => 8,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 8,
            'R' => 20,
            'S' => 12,
            'T' => 12,
            'U' => 8,
            'V' => 40,
            'W' => 18,
            'X' => 8,
            'Y' => 8,
            'Z' => 20,
            'AA' => 12,
            'AB' => 8,
            'AC' => 15,
            'AD' => 12,
            'AE' => 8,
            'AF' => 15,
            'AG' => 15,
            'AH' => 15,
            'AI' => 12,
        ];

        foreach ($widths as $col => $width) {
            $this->sheet->getColumnDimension($col)->setWidth($width);
        }
    }
}
