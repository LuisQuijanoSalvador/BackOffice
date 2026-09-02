<?php

namespace App\Http\Livewire\Conciliacion;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\PdfParserService;
use App\Services\ConciliacionService;
use App\Services\PdfReportService;
use Illuminate\Support\Facades\Storage;

class ConciliacionTarjetas extends Component
{
    use WithFileUploads;

    public $pdfFile;
    public $lastFourDigits = '';
    public $fechaInicio;
    public $fechaFin;
    
    public $results = [];
    public $pdfFilename = '';
    public $message = '';
    public $error = '';
    public $loading = false;

    public function mount()
    {
        $this->fechaInicio = now()->subMonth()->format('Y-m-d');
        $this->fechaFin = now()->format('Y-m-d');
    }

    public function procesar()
    {
        $this->reset(['message', 'error', 'results', 'pdfFilename']);
        $this->loading = true;

        $this->validate([
            'pdfFile' => 'required|file|mimes:pdf|max:10240',
            'lastFourDigits' => 'required|string|size:4',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        try {
            // Guardar PDF temporalmente
            $pdfPath = $this->pdfFile->store('temp', 'local');
            $fullPath = storage_path('app/' . $pdfPath);

            // Extraer movimientos del PDF
            $parserService = new PdfParserService();
            $movements = $parserService->extractMovements($fullPath, $this->lastFourDigits);

            if (empty($movements)) {
                $this->error = 'No se encontraron movimientos en el estado de cuenta.';
                $this->loading = false;
                return;
            }

            // Conciliar con boletos y servicios
            $conciliacionService = new ConciliacionService();
            $this->results = $conciliacionService->conciliar(
                $movements,
                $this->lastFourDigits,
                $this->fechaInicio,
                $this->fechaFin
            )->toArray();

            // Generar PDF de reporte
            $reportService = new PdfReportService();
            $this->pdfFilename = $reportService->generateReport(
                collect($this->results),
                $this->lastFourDigits,
                $this->fechaInicio,
                $this->fechaFin
            );

            $this->message = 'Conciliación completada. Se encontraron ' . 
                           collect($this->results)->where('coincidencia', true)->count() . 
                           ' coincidencias.';

            // Limpiar archivo temporal
            Storage::disk('local')->delete($pdfPath);

        } catch (\Exception $e) {
            $this->error = 'Error al procesar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function descargarPdf()
    {
        if ($this->pdfFilename) {
            return response()->download(storage_path('app/public/' . $this->pdfFilename));
        }
    }

    public function render()
    {
        return view('livewire.conciliacion.conciliacion-tarjetas');
    }
}