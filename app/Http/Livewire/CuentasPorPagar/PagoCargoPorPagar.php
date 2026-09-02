<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use App\Models\CargoPorPagar;
use App\Models\Egreso;
use App\Models\AbonoPago;
use App\Models\Banco;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;

class PagoCargoPorPagar extends Component
{
    public $cargosIds = [];
    public $cargos;

    public $totalSeleccionado = 0;
    public $aplicaDetraccion = false;
    public $montoDetraccion = 0;
    public $montoAPagar = 0;

    public $bancoId = '';
    public $bancoDetraccionId = '';
    public $fechaPago;
    public $observacion = '';

    public $bancos = [];
    public $message = '';
    public $error = '';

    public function mount($cargos)
    {
        $this->cargosIds = array_filter(explode(',', $cargos));

        $this->cargos = CargoPorPagar::with('proveedor')
            ->whereIn('id', $this->cargosIds)
            ->where('saldo', '>', 0)
            ->get();

        if ($this->cargos->isEmpty()) {
            $this->error = 'No se encontraron cargos pendientes para los IDs seleccionados.';
            return;
        }

        $this->totalSeleccionado = $this->cargos->sum('saldo');
        $this->montoAPagar = $this->totalSeleccionado;
        $this->fechaPago = now()->format('Y-m-d');

        $this->bancos = Banco::orderBy('nombre')->get();
    }

    public function updatedAplicaDetraccion($value)
    {
        if (!$value) $this->montoDetraccion = 0;
        $this->calcularMontos();
    }

    public function updatedMontoDetraccion()
    {
        $this->calcularMontos();
    }

    private function calcularMontos()
    {
        $this->montoDetraccion = max(0, (float)$this->montoDetraccion);
        $this->montoAPagar = max(0, $this->totalSeleccionado - $this->montoDetraccion);
    }

    public function registrarPago()
    {
        $this->reset(['message', 'error']);

        // Calculamos el monto sugerido para usarlo en validaciones
        $montoSugerido = $this->totalSeleccionado - $this->montoDetraccion;

        $rules = [
            'montoAPagar' => 'required|numeric|min:0.01|max:' . $this->totalSeleccionado,
            'montoDetraccion' => $this->aplicaDetraccion ? 'required|numeric|min:0.01|max:' . $this->totalSeleccionado : 'nullable',
            'bancoId' => 'required|exists:bancos,id',
            'fechaPago' => 'required|date',
        ];

        // Si seleccionó varios documentos, obligamos a que el pago sea el total exacto
        if (count($this->cargos) > 1) {
            $rules['montoAPagar'] .= '|in:' . $montoSugerido;
        }

        if ($this->aplicaDetraccion) {
            $rules['bancoDetraccionId'] = 'required|exists:bancos,id';
        }

        $this->validate($rules, [
            'montoAPagar.in' => 'Al seleccionar múltiples documentos, el monto a pagar debe ser el total exacto (' . number_format($montoSugerido, 2) . ').',
            'montoAPagar.max' => 'El monto a pagar no puede ser mayor al total de la deuda.',
        ]);

        DB::beginTransaction();
        try {
            $estadoActivoId = Estado::where('descripcion', 'ACTIVO')->value('id');
            $estadoPagadoId = Estado::where('descripcion', 'PAGADO')->value('id');
            $userId = auth()->id();
            $moneda = $this->cargos->first()->moneda;
            $idDocRef = $this->cargos->first()->idDocumento;
            $observacionBase = (count($this->cargos) > 1 ? 'Pago múltiple. ' : 'Abono parcial/total. ') . ($this->aplicaDetraccion ? 'Incluye detracción. ' : '') . $this->observacion;

            // 1. Generar EGRESO 1: Pago al Proveedor
            $egresoProveedor = Egreso::create([
                'idBanco' => $this->bancoId,
                'moneda' => $moneda,
                'monto' => $this->montoAPagar,
                'idDocumento' => $idDocRef,
                'idEstado' => $estadoActivoId,
                'idUsuarioCreacion' => $userId,
                'idUsuarioModificacion' => $userId,
            ]);

            // 2. Generar EGRESO 2: Pago de Detracción
            $egresoDetraccion = null;
            if ($this->aplicaDetraccion && $this->montoDetraccion > 0) {
                $egresoDetraccion = Egreso::create([
                    'idBanco' => $this->bancoDetraccionId,
                    'moneda' => $moneda,
                    'monto' => $this->montoDetraccion,
                    'idDocumento' => $idDocRef,
                    'idEstado' => $estadoActivoId,
                    'idUsuarioCreacion' => $userId,
                    'idUsuarioModificacion' => $userId,
                ]);
            }

            // 3. Generar los ABONOS
            $primerCargoId = $this->cargos->first()->id;

            AbonoPago::create([
                'idCargoPorPagar' => $primerCargoId,
                'idEgreso' => $egresoProveedor->id,
                'moneda' => $moneda,
                'monto' => $this->montoAPagar,
                'fechaPago' => $this->fechaPago,
                'idBanco' => $this->bancoId,
                'observacion' => 'Abono al proveedor. ' . $observacionBase,
                'idEstado' => $estadoActivoId,
                'idUsuarioCreacion' => $userId,
                'idUsuarioModificacion' => $userId,
            ]);

            if ($egresoDetraccion) {
                AbonoPago::create([
                    'idCargoPorPagar' => $primerCargoId,
                    'idEgreso' => $egresoDetraccion->id,
                    'moneda' => $moneda,
                    'monto' => $this->montoDetraccion,
                    'fechaPago' => $this->fechaPago,
                    'idBanco' => $this->bancoDetraccionId,
                    'observacion' => 'Abono por Detracción SUNAT. ' . $observacionBase,
                    'idEstado' => $estadoActivoId,
                    'idUsuarioCreacion' => $userId,
                    'idUsuarioModificacion' => $userId,
                ]);
            }

            // 4. Actualizar SALDOS (Lógica para Pagos Parciales y Totales)
            $totalAbonado = $this->montoAPagar + $this->montoDetraccion;

            if (count($this->cargos) === 1) {
                // Si es un solo documento, permitimos el pago parcial
                $cargo = $this->cargos->first();
                $cargo->saldo = max(0, $cargo->saldo - $totalAbonado);

                // Si el saldo llega a 0, cambiamos el estado a Pagado
                if ($cargo->saldo <= 0) {
                    $cargo->idEstado = $estadoPagadoId;
                    $cargo->saldo = 0;
                }
                $cargo->usuarioModificacion = $userId;
                $cargo->save();
            } else {
                // Si son múltiples documentos, asumimos pago total de todos (validado arriba)
                foreach ($this->cargos as $cargo) {
                    $cargo->saldo = 0;
                    $cargo->idEstado = $estadoPagadoId;
                    $cargo->usuarioModificacion = $userId;
                    $cargo->save();
                }
            }

            DB::commit();
            $this->message = 'Pago registrado exitosamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Error al registrar el pago: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.cuentas-por-pagar.pago-cargo-por-pagar');
    }
}
