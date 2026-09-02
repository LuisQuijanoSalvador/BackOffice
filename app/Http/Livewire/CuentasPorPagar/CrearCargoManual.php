<?php

namespace App\Http\Livewire\CuentasPorPagar;

use Livewire\Component;
use App\Models\CargoPorPagar;
use App\Models\Proveedor;
use App\Models\Estado;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrearCargoManual extends Component
{
    public $proveedorId = '';
    public $tipoDocumento = 'Documento de Cobranza';
    public $serieDocumento = '';
    public $numeroDocumento = '';
    
    public $fechaEmision;
    public $diasCredito = 30;
    public $fechaVencimiento;
    
    public $moneda = 'PEN';
    public $tipoCambio = 1.00;
    
    public $tarifaNeta = 0;
    public $inafecto = 0;
    public $igv = 0;
    public $otrosImpuestos = 0;
    public $total = 0;
    
    public $observacion = '';
    
    public $proveedores = [];
    public $message = '';
    public $error = '';

    public function mount()
    {
        $this->fechaEmision = now()->format('Y-m-d');
        $this->calcularVencimiento();
        $this->calcularTotal();
        $this->proveedores = Proveedor::orderBy('razonSocial')->get();
    }

    // Recalcular fechas automáticamente
    public function updatedDiasCredito() { $this->calcularVencimiento(); }
    public function updatedFechaEmision() { $this->calcularVencimiento(); }
    
    // Recalcular totales automáticamente
    public function updatedTarifaNeta() { $this->calcularTotal(); }
    public function updatedInafecto() { $this->calcularTotal(); }
    public function updatedIgv() { $this->calcularTotal(); }
    public function updatedOtrosImpuestos() { $this->calcularTotal(); }

    private function calcularVencimiento()
    {
        if ($this->fechaEmision) {
            $this->fechaVencimiento = Carbon::parse($this->fechaEmision)->addDays((int)$this->diasCredito)->format('Y-m-d');
        }
    }

    private function calcularTotal()
    {
        $this->total = (float)$this->tarifaNeta + (float)$this->inafecto + (float)$this->igv + (float)$this->otrosImpuestos;
    }

    public function guardar()
    {
        $this->reset(['message', 'error']);

        $this->validate([
            'proveedorId' => 'required|exists:proveedors,id',
            'serieDocumento' => 'required|string|max:50',
            'numeroDocumento' => 'required|string|max:255',
            'fechaEmision' => 'required|date',
            'fechaVencimiento' => 'required|date|after_or_equal:fechaEmision',
            'total' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Buscamos el estado "Pendiente"
            $estadoPendienteId = Estado::where('descripcion', 'PENDIENTE')->value('id');

            CargoPorPagar::create([
                'idDocumento' => null, // Al ser manual, no hay factura de compra asociada
                'idProveedor' => $this->proveedorId,
                'montoCredito' => $this->total,
                'diasCredito' => $this->diasCredito,
                'fechaEmision' => $this->fechaEmision,
                'fechaVencimiento' => $this->fechaVencimiento,
                'moneda' => $this->moneda,
                'tarifaNeta' => $this->tarifaNeta,
                'inafecto' => $this->inafecto,
                'igv' => $this->igv,
                'otrosImpuestos' => $this->otrosImpuestos,
                'total' => $this->total,
                'tipoDocumento' => $this->tipoDocumento,
                'serieDocumento' => $this->serieDocumento,
                'numeroDocumento' => $this->numeroDocumento,
                'montoCargo' => $this->total,
                'tipoCambio' => $this->tipoCambio,
                'saldo' => $this->total,
                'idEstado' => $estadoPendienteId,
                'usuarioCreacion' => auth()->id(),
                'usuarioModificacion' => auth()->id(),
            ]);

            DB::commit();
            $this->message = 'Cargo manual registrado exitosamente.';
            
            // Limpiar formulario
            $this->reset(['serieDocumento', 'numeroDocumento', 'tarifaNeta', 'inafecto', 'igv', 'otrosImpuestos', 'observacion']);
            $this->total = 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Error al guardar: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.cuentas-por-pagar.crear-cargo-manual');
    }
}