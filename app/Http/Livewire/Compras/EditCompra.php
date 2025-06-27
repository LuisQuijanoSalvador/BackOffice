<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\TipoDocumento;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Estado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditCompra extends Component
{
    // Propiedad para la compra que se va a editar
    // public Compra $compra;

    // Propiedades para los campos de la tabla 'compras' (los mismos que CreateCompra)
    public $tipoDocumento;
    public $serie;
    public $numero;
    public $idProveedor;
    public $idCliente;
    public $numeroFile;
    public $formaPago;
    public $fechaEmision;
    public $moneda;
    public $subTotal = 0;
    public $inafecto = 0;
    public $igv = 0;
    public $total = 0;
    public $totalLetras;
    public $observacion;

    // Propiedad para los detalles de la compra (lista de detalles ya agregados)
    public $detalles = [];
    public $tasaIGV;

    // Propiedades temporales para el detalle que se está agregando/editando en el modal
    public $currentDetalle = [
        'id'            => null, // Para saber si es un detalle existente o nuevo
        'cantidad'      => 1,
        'unidadMedida'  => '',
        'descripcion'   => '',
        'valorUnitario' => 0,
        'afectoIgv'     => true,
        'estado'        => 1, // Puedes predefinir el ID del estado 'Activo'
    ];
    public $editingDetalleIndex = null; // Para saber qué índice de detalle se está editando

    // Para controlar la visibilidad del modal
    public $showDetalleModal = false;

    // Propiedades para los selects (las mismas que CreateCompra)
    public $tiposDocumento;
    public $proveedores;
    public $clientes;
    public $estados;
    public $formasPago = [
        'Efectivo' => 'Efectivo',
        'Transferencia' => 'Transferencia',
        'Crédito' => 'Crédito',
        'Débito' => 'Débito',
    ];
    public $monedas = [
        'PEN' => 'Soles (PEN)',
        'USD' => 'Dólares (USD)',
    ];

    // Livewire 2.x listeners (mismos que CreateCompra)
    protected $listeners = [
        'detalle-updated' => 'updateDetalleFromChild',
        'show-detalle-modal', // Necesario para el push/popstate si se quiere interacción con el historial
        'hide-detalle-modal'
    ];

    // Reglas de validación para el formulario principal de compra (las mismas que CreateCompra)
    protected $rules = [
        'tipoDocumento'    => 'required|exists:tipo_documentos,id',
        'serie'            => 'required|string|max:10',
        'numero'           => 'required|string|max:20',
        'idProveedor'      => 'required|exists:proveedors,id',
        'idCliente'        => 'nullable|exists:clientes,id',
        'numeroFile'       => 'nullable|string|max:50',
        'formaPago'        => 'required|string|in:Efectivo,Transferencia,Crédito,Débito',
        'fechaEmision'     => 'required|date',
        'moneda'           => 'required|string|in:PEN,USD',
        'observacion'      => 'nullable|string|max:500',
    ];

    // Reglas de validación para el detalle en el modal (las mismas que CreateCompra)
    protected $currentDetalleRules = [
        'currentDetalle.cantidad'      => 'required|numeric|min:1',
        'currentDetalle.unidadMedida'  => 'required|string|max:50',
        'currentDetalle.descripcion'   => 'required|string|max:255',
        'currentDetalle.valorUnitario' => 'required|numeric|min:0',
    ];

    // Mensajes de validación personalizados (los mismos que CreateCompra)
    protected $messages = [
        'tipoDocumento.required' => 'El tipo de documento es obligatorio.',
        'idProveedor.required'   => 'El proveedor es obligatorio.',
        'currentDetalle.cantidad.required' => 'La cantidad del detalle es obligatoria.',
        'currentDetalle.unidadMedida.required' => 'La unidad de medida es obligatoria.',
        'currentDetalle.descripcion.required' => 'La descripción del detalle es obligatoria.',
        'currentDetalle.valorUnitario.required' => 'El valor unitario es obligatorio.',
    ];

    // EL MÉTODO MOUNT ES CLAVE AQUÍ PARA CARGAR LA COMPRA
    public function mount($compra)
    {
        $this->tasaIGV = config('taxes.igv_rate');

        $compra = Compra::findOrFail($compra);
        $this->compra = $compra; // Inyecta la compra directamente

        // Cargar datos de la compra en las propiedades del componente
        $this->tipoDocumento = $compra->tipoDocumento;
        $this->serie = $compra->serie;
        $this->numero = $compra->numero;
        $this->idProveedor = $compra->idProveedor;
        $this->idCliente = $compra->idCliente;
        $this->numeroFile = $compra->numeroFile;
        $this->formaPago = $compra->formaPago;
        $this->fechaEmision = $compra->fechaEmision->format('Y-m-d'); // Formatear para el input date
        $this->moneda = $compra->moneda;
        $this->subTotal = $compra->subTotal;
        $this->inafecto = $compra->inafecto;
        $this->igv = $compra->igv;
        $this->total = $compra->total;
        $this->totalLetras = $compra->totalLetras;
        $this->observacion = $compra->observacion;

        // Cargar los detalles de la compra
        // Asegurarse de que los detalles se carguen como arrays asociativos para la edición
        $this->detalles = $compra->detalles->map(function($detalle) {
            return $detalle->toArray();
        })->toArray();


        // Cargar datos para los selects (igual que CreateCompra)
        $this->tiposDocumento = TipoDocumento::all();
        $this->proveedores = Proveedor::all();
        $this->clientes = Cliente::orderBy('razonSocial')->get();
        $this->estados = Estado::all();

        // Calcular totales al cargar (aunque ya vienen de la DB)
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subTotal = 0;
        $this->igv = 0;
        $this->inafecto = 0; // Monto de ítems inafectos
        $this->total = 0;
        // foreach ($this->detalles as $detalle) {
        //     $this->subTotal += ($detalle['cantidad'] * $detalle['valorUnitario']);
        // }
        
        foreach ($this->detalles as $index => $detalle) {
            // Asegurarse de que los valores sean numéricos
            $cantidad = floatval($detalle['cantidad'] ?? 0);
            $valorUnitario = floatval($detalle['valorUnitario'] ?? 0);
            $afectoIGV = (bool)($detalle['afectoIgv'] ?? true); // Por defecto true si no está definido
            
            $subtotalLinea = $cantidad * $valorUnitario;

            if ($afectoIGV) {
                // Si la línea está afecta al IGV
                $this->subTotal += $subtotalLinea; // Suma a la base gravada
                $this->igv += $subtotalLinea * $this->tasaIGV; // Calcula el IGV de esta línea
            } else {
                // Si la línea es inafecta al IGV
                $this->inafecto += $subtotalLinea; // Suma al total inafecto
            }
        }

        // El total general es la suma de la base gravada + inafecto + IGV
        $this->total = $this->subTotal + $this->inafecto + $this->igv;

        // Redondear a 2 decimales para evitar problemas de precisión de flotantes
        $this->subTotal = round($this->subTotal, 2);
        $this->igv = round($this->igv, 2);
        $this->inafecto = round($this->inafecto, 2);
        $this->total = round($this->total, 2);
        $this->totalLetras = "Son " . number_format($this->total, 2) . " " . ($this->monedas[$this->moneda] ?? '') . " y 00/100";
    }

    // --- Métodos para el Modal de Detalle ---

    public function openAddDetalleModal()
    {
        // Inicializar el detalle para el modal con valores por defecto y sin ID
        $this->currentDetalle = [
            'id'            => null,
            'cantidad'      => 1,
            'unidadMedida'  => '',
            'descripcion'   => '',
            'valorUnitario' => 0,
            'afectoIgv'     => true,
            'estado'        => 1,
        ];
        $this->editingDetalleIndex = null; // No estamos editando un detalle existente
        $this->resetErrorBag('currentDetalle.*');
        $this->showDetalleModal = true;
        $this->emit('show-detalle-modal');
    }

    public function openEditDetalleModal($index)
    {
        // Cargar el detalle seleccionado en currentDetalle para edición
        $this->currentDetalle = $this->detalles[$index];
        $this->editingDetalleIndex = $index; // Guardar el índice del detalle que se está editando
        $this->resetErrorBag('currentDetalle.*');
        $this->showDetalleModal = true;
        $this->emit('show-detalle-modal');
    }


    public function closeDetalleModal()
    {
        $this->showDetalleModal = false;
        $this->reset(['currentDetalle', 'editingDetalleIndex']); // Resetear currentDetalle y su índice
        $this->emit('hide-detalle-modal');
    }

    public function saveOrUpdateDetalle()
    {
        $this->validate($this->currentDetalleRules);

        if ($this->editingDetalleIndex !== null) {
            // Actualizar un detalle existente
            $this->detalles[$this->editingDetalleIndex] = $this->currentDetalle;
        } else {
            // Añadir un nuevo detalle
            $this->detalles[] = $this->currentDetalle;
        }

        $this->calculateTotals();
        $this->closeDetalleModal(); // Cerrar el modal después de guardar/actualizar
    }

    public function removeDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles); // Reindexar el array
        $this->calculateTotals();
    }

    // Este método ya no es estrictamente necesario si la edición se hace vía modal
    // pero lo mantenemos por si hay algún otro tipo de actualización
    public function updateDetalleFromChild($updatedItem, $index)
    {
        if (isset($this->detalles[$index])) {
            $this->detalles[$index] = $updatedItem;
            $this->calculateTotals();
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, array_keys($this->rules))) {
            $this->validateOnly($propertyName);
        }

        if ($propertyName === 'moneda') {
            $this->calculateTotals();
        }

        if (str_starts_with($propertyName, 'currentDetalle.')) {
            $this->validateOnly($propertyName, $this->currentDetalleRules);
        }
    }

    // --- Método para actualizar la compra completa ---

    public function updateCompra()
    {
        if (empty($this->detalles)) {
            session()->flash('error', 'Debe agregar al menos un detalle de compra para poder guardar.');
            $this->emit('no-detalles-error');
            return;
        }

        $this->validate();

        DB::beginTransaction();

        try {
            // Actualizar la compra principal
            $this->compra->update([
                'tipoDocumento'      => $this->tipoDocumento,
                'serie'              => $this->serie,
                'numero'             => $this->numero,
                'idProveedor'        => $this->idProveedor,
                'idCliente'          => $this->idCliente,
                'numeroFile'         => $this->numeroFile,
                'formaPago'          => $this->formaPago,
                'fechaEmision'       => $this->fechaEmision,
                'moneda'             => $this->moneda,
                'subTotal'           => $this->subTotal,
                'inafecto'           => $this->inafecto,
                'igv'                => $this->igv,
                'total'              => $this->total,
                'totalLetras'        => $this->totalLetras,
                'observacion'        => $this->observacion,
                // Mantener el estado actual a menos que haya una lógica específica para cambiarlo
                // 'estado'             => $this->compra->estado,
                'usuarioModificacion' => Auth::id(),
            ]);

            // Sincronizar los detalles de la compra
            // Esto es más complejo: necesitamos ver qué se eliminó, qué se añadió, qué se modificó.
            // Una estrategia común es eliminar todos los detalles existentes y recrearlos,
            // o identificar los cambios. Para simplificar, haremos un "borrar y recrear" si no hay muchos detalles.
            // Si hay muchos detalles y la performance es crítica, se necesitaría una lógica de diff más avanzada.

            // 1. Obtener los IDs de los detalles actuales en la base de datos
            $existingDetailIds = $this->compra->detalles->pluck('id')->toArray();
            // 2. Obtener los IDs de los detalles que vienen del formulario (los que tienen ID)
            $submittedDetailIds = collect($this->detalles)->whereNotNull('id')->pluck('id')->toArray();

            // 3. Detalles a eliminar (los que estaban en DB pero no en el formulario)
            $detailsToDelete = array_diff($existingDetailIds, $submittedDetailIds);
            if (!empty($detailsToDelete)) {
                CompraDetalle::whereIn('id', $detailsToDelete)->delete();
            }

            // 4. Procesar los detalles del formulario
            foreach ($this->detalles as $detalleData) {
                if (isset($detalleData['id'])) {
                    // Actualizar detalle existente
                    $detalle = CompraDetalle::find($detalleData['id']);
                    if ($detalle) {
                        $detalle->update([
                            'cantidad'      => $detalleData['cantidad'],
                            'unidadMedida'  => $detalleData['unidadMedida'],
                            'descripcion'   => $detalleData['descripcion'],
                            'valorUnitario' => $detalleData['valorUnitario'],
                            'afectoIgv'     => $detalleData['afectoIgv'],
                            'estado'        => $detalleData['estado'] ?? 1, // Mantener estado o predefinir
                        ]);
                    }
                } else {
                    // Crear nuevo detalle
                    $this->compra->detalles()->create([
                        'cantidad'      => $detalleData['cantidad'],
                        'unidadMedida'  => $detalleData['unidadMedida'],
                        'descripcion'   => $detalleData['descripcion'],
                        'valorUnitario' => $detalleData['valorUnitario'],
                        'afectoIgv'     => $detalleData['afectoIgv'],
                        'estado'        => $detalleData['estado'] ?? 1, // Predefinir estado
                    ]);
                }
            }


            DB::commit();

            session()->flash('message', '¡Compra actualizada exitosamente!');
            // Redirigir al listado de compras después de editar
            return redirect()->route('listaCompras');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Hubo un error al actualizar la compra: ' . $e->getMessage());
            \Log::error('Error al actualizar compra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.compras.edit-compra', [
            'tiposDocumento' => $this->tiposDocumento,
            'proveedores'    => $this->proveedores,
            'clientes'       => $this->clientes,
            'estados'        => $this->estados,
        ]);
    }
}