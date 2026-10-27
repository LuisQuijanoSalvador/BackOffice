<?php

namespace App\Http\Livewire\Contabilidad;

use Livewire\Component;
use App\Models\Compra;
use App\Services\ConcarExportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GenerarAsientoCompras extends Component
{
    public $fechaInicio;
    public $fechaFin;
    public $generating = false;
    public $progress = 0;
    public $totalCompras = 0;
    public $processedCompras = 0;
    public $generatedFile = '';
    public $message = '';
    public $error = '';

    public function mount()
    {
        // Por defecto, mes anterior
        $this->fechaInicio = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
    }

    public function generar()
    {
        $this->reset(['message', 'error', 'generatedFile']);
        
        $this->validate([
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        // Obtener compras del período
        $compras = Compra::whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->whereNotNull('idCuentaContable')
            ->whereNotNull('idSubdiario')
            ->get();

        if ($compras->isEmpty()) {
            $this->error = 'No se encontraron compras en el período seleccionado con cuenta contable y subdiario asignados.';
            return;
        }

        $this->totalCompras = $compras->count();
        $this->generating = true;
        $this->progress = 0;
        $this->processedCompras = 0;

        // Simular progreso (en un entorno real, esto sería más dinámico)
        $this->dispatchBrowserEvent('start-progress', ['total' => $this->totalCompras]);

        try {
            $service = new ConcarExportService();
            $filename = $service->generate($compras->pluck('id')->toArray(), $this->fechaInicio, $this->fechaFin);
            
            $this->generatedFile = $filename;
            $this->progress = 100;
            $this->processedCompras = $this->totalCompras;
            $this->message = 'Archivo generado exitosamente con ' . $this->totalCompras . ' compras procesadas.';
            
            $this->dispatchBrowserEvent('complete-progress');
            
        } catch (\Exception $e) {
            $this->error = 'Error al generar el archivo: ' . $e->getMessage();
            $this->dispatchBrowserEvent('error-progress');
        } finally {
            $this->generating = false;
        }
    }

    public function descargar()
    {
        if ($this->generatedFile && Storage::disk('public')->exists($this->generatedFile)) {
            return response()->download(storage_path('app/public/' . $this->generatedFile));
        }
        
        $this->error = 'El archivo no existe o ya fue eliminado.';
    }

    public function render()
    {
        // Contar compras disponibles para el período
        $comprasCount = Compra::whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin])
            ->whereNotNull('idCuentaContable')
            ->whereNotNull('idSubdiario')
            ->count();

        return view('livewire.contabilidad.generar-asiento-compras', compact('comprasCount'));
    }
}