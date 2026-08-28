<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use App\Models\CargoPorPagar;
use App\Models\Egreso;
use App\Models\Banco;
use App\Models\Estado;
use App\Models\AbonoPago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PagoCargoPorPagar extends Component
{
    public $cargoId;
    public $cargo;

    public $monto = 0;
    public $bancoId = '';
    public $fechaPago;
    public $observacion = '';

    public $bancos = [];
    public $message = '';
    public $error = '';

    public function mount($cargoId)
    {
        $this->cargoId = $cargoId;
        $this->cargo = CargoPorPagar::with('proveedor')->findOrFail($cargoId);

        // Pre-llenar datos
        $this->monto = $this->cargo->saldo;
        $this->fechaPago = now()->format('Y-m-d');

        // Cargar bancos activos
        $this->bancos = Banco::where('idEstado', 1)->orderBy('nombre')->get();
        // Nota: Ajusta 'estado' => 1 si tu tabla bancos usa otro campo para activo.
    }

    public function registrarPago()
    {
        $this->reset(['message', 'error']);

        $this->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $this->cargo->saldo,
            'bancoId' => 'required|exists:bancos,id',
            'fechaPago' => 'required|date',
        ], [
            'monto.max' => 'El monto a pagar no puede ser mayor al saldo pendiente (' . number_format($this->cargo->saldo, 2) . ').',
            'bancoId.required' => 'Debe seleccionar el banco desde donde se realiza el pago.',
        ]);

        DB::beginTransaction();
        try {
            // 1. Generar el Egreso
            $egreso = Egreso::create([
                'idBanco'             => $this->bancoId,
                'moneda'              => $this->cargo->moneda,
                'monto'               => $this->monto,
                'idDocumento'         => $this->cargo->idDocumento,
                'idEstado'            => Estado::where('descripcion', 'Activo')->value('id'),
                'idUsuarioCreacion'   => auth()->id(),
                'idUsuarioModificacion' => auth()->id(),
            ]);

            // 2. Generar el Abono (¡NUEVO!)
            AbonoPago::create([
                'idCargoPorPagar'     => $this->cargo->id,
                'idEgreso'            => $egreso->id,
                'moneda'              => $this->cargo->moneda,
                'monto'               => $this->monto,
                'fechaPago'           => $this->fechaPago,
                'idBanco'             => $this->bancoId,
                'observacion'         => $this->observacion,
                'idEstado'            => Estado::where('descripcion', 'Activo')->value('id'),
                'idUsuarioCreacion'   => auth()->id(),
                'idUsuarioModificacion' => auth()->id(),
            ]);

            // 3. Actualizar el Saldo del Cargo por Pagar
            $nuevoSaldo = max(0, $this->cargo->saldo - $this->monto);
            $this->cargo->saldo = $nuevoSaldo;
            $this->cargo->usuarioModificacion = auth()->id();

            // Si el saldo llega a 0, cambiamos el estado a "Pagado" (si existe ese estado)
            if ($nuevoSaldo == 0) {
                $estadoPagadoId = Estado::where('descripcion', 'Pagado')->value('id');
                if ($estadoPagadoId) {
                    $this->cargo->idEstado = $estadoPagadoId;
                }
            }

            $this->cargo->save();

            DB::commit();

            $this->message = 'Pago registrado exitosamente. Saldo actualizado.';
            Log::info("Pago registrado para Cargo ID: {$this->cargoId}, Monto: {$this->monto}");

            // Limpiar formulario
            $this->reset(['monto', 'bancoId', 'observacion']);
            $this->cargo->refresh(); // Refrescar los datos del cargo en la vista

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Error al registrar el pago: ' . $e->getMessage();
            Log::error('Error al registrar pago de cargo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.cuentas-por-pagar.pago-cargo-por-pagar');
    }
}
