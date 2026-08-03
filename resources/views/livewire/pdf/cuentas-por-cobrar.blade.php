<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cuentas por Cobrar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .subtitulo {
            text-align: center;
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #343a40;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }

        .text-success {
            color: #28a745;
            font-weight: bold;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .border-bottom {
            border-bottom: 2px solid #343a40;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <h1>CUENTAS POR COBRAR {{ $moneda === 'USD' ? 'DÓLARES' : 'SOLES' }}</h1>
    <p class="subtitulo">
        Período: {{ $fechaDesde }} al {{ $fechaHasta }} |
        Total Clientes: {{ $resumen['totalClientes'] }} |
        Total Cargos: {{ $resumen['totalCargos'] }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">CLIENTE</th>
                <th class="text-right" style="width: 20%;">MONTO</th>
                <th class="text-right" style="width: 20%;">DEUDA VENCIDA</th>
                <th class="text-center" style="width: 10%;">DIAS ATRASO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargosAgrupados as $cargo)
                <tr>
                    <td>
                        <div class="font-bold">{{ $cargo->razonSocial }}</div>
                        <div style="font-size: 10px; color: #666;">{{ $cargo->numeroDocumentoIdentidad }}</div>
                    </td>
                    <td class="text-right font-bold">
                        {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($cargo->monto_total, 2) }}
                    </td>
                    <td class="text-right {{ $cargo->deuda_vencida > 0 ? 'text-danger' : '' }}">
                        {{ $cargo->deuda_vencida > 0 ? number_format($cargo->deuda_vencida, 2) : '0.00' }}
                    </td>
                    <td class="text-center">
                        {{ $cargo->max_dias_atraso > 0 ? $cargo->max_dias_atraso . ' días' : 'Al día' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-light border-bottom">
                <td class="font-bold">TOTAL</td>
                <td class="text-right text-success font-bold">
                    {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalMonto'], 2) }}
                </td>
                <td class="text-right text-danger font-bold">
                    {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalDeudaVencida'], 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Reporte generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema de Gestión de Agencia de Viajes
    </div>

</body>

</html>
