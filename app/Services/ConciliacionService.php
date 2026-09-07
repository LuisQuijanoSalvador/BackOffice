<?php

namespace App\Services;

use App\Models\Boleto;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\TipoServicio;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConciliacionService
{
    /**
     * Busca coincidencias entre movimientos de tarjeta y boletos/servicios
     */
    public function conciliar(array $movements, string $lastFourDigits, string $fechaInicio, string $fechaFin): Collection
    {
        $results = collect();

        foreach ($movements as $movement) {
            $result = [
                'fecha_transaccion' => $movement['fecha'],
                'descripcion_tarjeta' => $movement['descripcion'],
                'monto_tarjeta' => $movement['monto'],
                'moneda_tarjeta' => $movement['moneda'] ?? 'PEN',
                'banco' => $movement['banco'],
                'coincidencia' => false,
                'tipo' => null,
                'cliente' => null,
                'numero_boleto' => null,
                'serie_documento' => null,
                'numero_documento' => null,
                'tipo_servicio' => null,
            ];

            // Buscar en boletos comparando con totalOrigen (Costo real sin margen)
            $boleto = Boleto::where('cod4', $lastFourDigits)
                ->whereBetween('fechaEmision', [$fechaInicio, $fechaFin])
                ->where('totalOrigen', $movement['monto'])
                ->first();

            if ($boleto) {
                $cliente = Cliente::find($boleto->idCliente);
                $documento = Documento::find($boleto->idDocumento);

                $result['coincidencia'] = true;
                $result['tipo'] = 'Boleto';
                $result['cliente'] = $cliente ? $cliente->razonSocial : 'N/A';
                $result['numero_boleto'] = $boleto->numeroBoleto;
                $result['serie_documento'] = $documento ? $documento->serie : 'N/A';
                $result['numero_documento'] = $documento ? $documento->numero : 'N/A';
            } else {
                // Buscar en servicios comparando con totalOrigen (Costo real sin margen)
                $servicio = Servicio::where('cod4', $lastFourDigits)
                    ->whereBetween('fechaEmision', [$fechaInicio, $fechaFin])
                    ->where('totalOrigen', $movement['monto'])
                    ->first();

                if ($servicio) {
                    $cliente = Cliente::find($servicio->idCliente);
                    $documento = Documento::find($servicio->idDocumento);
                    $tipoServicio = TipoServicio::find($servicio->idTipoServicio);

                    $result['coincidencia'] = true;
                    $result['tipo'] = 'Servicio';
                    $result['cliente'] = $cliente ? $cliente->razonSocial : 'N/A';
                    $result['serie_documento'] = $documento ? $documento->serie : 'N/A';
                    $result['numero_documento'] = $documento ? $documento->numero : 'N/A';
                    $result['tipo_servicio'] = $tipoServicio ? $tipoServicio->descripcion : 'N/A';
                }
            }

            $results->push($result);
        }

        return $results;
    }
}
