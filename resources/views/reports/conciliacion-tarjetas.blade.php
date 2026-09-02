<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Conciliación de Tarjeta de Crédito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .info-box {
            margin-bottom: 15px;
        }

        .info-box strong {
            display: inline-block;
            width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .coincidencia {
            background-color: #d4edda;
        }

        .sin-coincidencia {
            background-color: #f8d7da;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Reporte de Conciliación de Tarjeta de Crédito</h2>
        <p>Generado: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info-box">
        <p><strong>Últimos 4 dígitos:</strong> {{ $lastFourDigits }}</p>
        <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al
            {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>

    <div class="summary">
        <p><strong>Total de Movimientos:</strong> {{ $totalMovimientos }}</p>
        <p><strong>Coincidencias Encontradas:</strong> {{ $totalCoincidencias }}</p>
        <p><strong>Sin Coincidencia:</strong> {{ $totalSinCoincidencia }}</p>
        <p><strong>Monto Total:</strong> S/ {{ number_format($montoTotal, 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha Transacción</th>
                <th>Descripción (TC)</th>
                <th class="text-right">Monto</th>
                <th>Moneda</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>N° Boleto</th>
                <th>Documento</th>
                <th>Tipo Servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $result)
                <tr class="{{ $result['coincidencia'] ? 'coincidencia' : 'sin-coincidencia' }}">
                    <td>{{ \Carbon\Carbon::parse($result['fecha_transaccion'])->format('d/m/Y') }}</td>
                    <td>{{ $result['descripcion_tarjeta'] }}</td>
                    <td class="text-right">{{ number_format($result['monto_tarjeta'], 2) }}</td>
                    <td>{{ $result['moneda_tarjeta'] ?? 'PEN' }}</td>
                    <td>{{ $result['tipo'] ?? '-' }}</td>
                    <td>{{ $result['cliente'] ?? '-' }}</td>
                    <td>{{ $result['numero_boleto'] ?? '-' }}</td>
                    <td>{{ $result['serie_documento'] ?? '-' }}-{{ $result['numero_documento'] ?? '-' }}</td>
                    <td>{{ $result['tipo_servicio'] ?? '-' }}</td>
                    <td>{{ $result['coincidencia'] ? '✓ Match' : '✗ Sin match' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
