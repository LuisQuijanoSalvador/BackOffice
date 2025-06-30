<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\Proveedor; // Necesitamos el modelo Proveedor
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComprasProveedorExport; // Clase de exportación que crearemos luego

class ReporteComprasPorProveedor extends Component
{
    public $proveedores; // Para poblar el select de proveedores
    public $selectedProveedorId;
    public $compras = [];
    public $mostrarResultados = false;

    protected $rules = [
        'selectedProveedorId' => 'required|exists:proveedors,id',
    ];

    protected $messages = [
        'selectedProveedorId.required' => 'Debe seleccionar un proveedor.',
        'selectedProveedorId.exists'   => 'El proveedor seleccionado no es válido.',
    ];

    public function mount()
    {
        $this->proveedores = Proveedor::orderBy('razonSocial')->get();
        $this->compras = [];
        $this->mostrarResultados = false;
    }

    public function generarReporte()
    {
        $this->validate();

        try {
            $this->compras = Compra::with('proveedor', 'tipoDocumentoR', 'estadoR') // Usar tipoDocumentoR
                                    ->where('idProveedor', $this->selectedProveedorId)
                                    ->orderBy('fechaEmision', 'desc') // O el orden que prefieras
                                    ->get();

            $this->mostrarResultados = true;

            if ($this->compras->isEmpty()) {
                session()->flash('info', 'No se encontraron compras para el proveedor seleccionado.');
            } else {
                session()->flash('success', 'Reporte generado exitosamente.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al generar el reporte: ' . $e->getMessage());
            Log::error('Error en ReporteComprasPorProveedor: ' . $e->getMessage());
        }
    }

    public function exportarExcel()
    {
        $this->validate(); // Valida el proveedor antes de exportar

        // Asegurarse de que los datos estén cargados si no se llamó a generarReporte antes
        if (empty($this->compras) || $this->compras->first()->idProveedor != $this->selectedProveedorId) {
             // Si las compras están vacías o no corresponden al proveedor actual, se generan.
             // La segunda condición es útil si el usuario cambia el select sin generar el reporte de nuevo.
            $this->generarReporte();
        }

        if ($this->compras->isEmpty()) {
            session()->flash('info', 'No hay datos para exportar a Excel. Genere el reporte primero.');
            return;
        }

        $proveedor = Proveedor::find($this->selectedProveedorId);
        $fileName = 'Reporte_Compras_Proveedor_' . ($proveedor->razonSocial ?? 'Desconocido') . '.xlsx';

        // Usa la clase de exportación que crearemos en el siguiente paso
        return Excel::download(new ComprasProveedorExport($this->compras), $fileName);
    }

    public function render()
    {
        return view('livewire.compras.reporte-compras-por-proveedor');
    }
}