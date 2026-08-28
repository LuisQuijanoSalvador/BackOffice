<?php
namespace App\Http\Livewire\CuentasPorPagar;
use Livewire\Component;
use App\Models\AbonoPago;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbonoDetail extends Component
{
    public $abonoId;
    public $abono;
    public $message = '';
    public $error = '';

    public function mount($abonoId)
    {
        $this->abonoId = $abonoId;
        $this->abono = AbonoPago::with(['cargo.proveedor', 'egreso', 'banco', 'estado'])->findOrFail($abonoId);
    }

    public function anular()
    {
        DB::beginTransaction();
        try {
            $estadoAnuladoId = Estado::where('descripcion', 'Anulado')->value('id');
            if (!$estadoAnuladoId) throw new \Exception('Estado "Anulado" no encontrado.');

            // 1. Anular Abono
            $this->abono->idEstado = $estadoAnuladoId;
            $this->abono->idUsuarioModificacion = auth()->id();
            $this->abono->save();

            // 2. Anular Egreso asociado
            if ($this->abono->egreso) {
                $this->abono->egreso->idEstado = $estadoAnuladoId;
                $this->abono->egreso->idUsuarioModificacion = auth()->id();
                $this->abono->egreso->save();
            }

            // 3. Devolver el saldo al Cargo por Pagar
            $cargo = $this->abono->cargo;
            $cargo->saldo += $this->abono->monto;
            
            // Si estaba "Pagado", volver a "Pendiente"
            $estadoPendienteId = Estado::where('descripcion', 'Pendiente')->value('id');
            if ($estadoPendienteId && $cargo->saldo > 0) {
                $cargo->idEstado = $estadoPendienteId;
            }
            $cargo->usuarioModificacion = auth()->id();
            $cargo->save();

            DB::commit();
            $this->message = 'Abono anulado exitosamente. El saldo ha sido restaurado.';
            $this->abono->refresh(); // Refrescar datos en vista
            Log::info("Abono ID {$this->abonoId} anulado por usuario " . auth()->id());

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Error al anular: ' . $e->getMessage();
        }
    }

    public function eliminar()
    {
        DB::beginTransaction();
        try {
            // Primero anulamos para mantener la integridad contable antes de borrar
            $this->anular(); 
            
            // Si la anulación fue exitosa, procedemos a eliminar físicamente
            if (empty($this->error)) {
                $abonoId = $this->abono->id;
                $egresoId = $this->abono->idEgreso;
                
                $this->abono->delete();
                
                // Opcional: Eliminar también el egreso si no se usa para nada más
                \App\Models\Egreso::find($egresoId)?->delete();

                $this->message = 'Abono eliminado permanentemente del sistema.';
                Log::warning("Abono ID {$abonoId} eliminado físicamente por usuario " . auth()->id());
                
                // Redirigir al listado después de eliminar
                return redirect()->route('abonos.index')->with('message', 'Abono eliminado.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error = 'Error al eliminar: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.cuentas-por-pagar.abono-detail');
    }
}