<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\TipoDocumento;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Estado;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateCompra extends Component
{
    // Propiedades para los campos de la tabla 'compras'
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
    public $igv = 0;
    public $total = 0;
    public $totalLetras;
    public $observacion;

    // Propiedad para los detalles de la compra (lista de detalles ya agregados)
    public $detalles = [];

    // Propiedades temporales para el detalle que se está agregando/editando en el modal
    public $currentDetalle = [
        'cantidad'      => 1,
        'unidadMedida'  => '',
        'descripcion'   => '',
        'valorUnitario' => 0,
        'estado'        => 1, // Puedes predefinir el ID del estado 'Activo' o 'Pendiente'
    ];

    // Para controlar la visibilidad del modal
    public $showDetalleModal = false;

    // Propiedades para los selects
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

    // Livewire 2.x listeners
    protected $listeners = ['detalle-updated' => 'updateDetalleFromChild']; // Renombramos para claridad

    // Reglas de validación para el formulario principal de compra
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
        // No validamos detalles aquí directamente, sino el currentDetalle en el modal
    ];

    // Reglas de validación para el detalle en el modal
    protected $currentDetalleRules = [
        'currentDetalle.cantidad'      => 'required|numeric|min:1',
        'currentDetalle.unidadMedida'  => 'required|string|max:50',
        'currentDetalle.descripcion'   => 'required|string|max:255',
        'currentDetalle.valorUnitario' => 'required|numeric|min:0',
    ];

    // Mensajes de validación personalizados
    protected $messages = [
        'tipoDocumento.required' => 'El tipo de documento es obligatorio.',
        'idProveedor.required'   => 'El proveedor es obligatorio.',
        'currentDetalle.cantidad.required' => 'La cantidad del detalle es obligatoria.',
        'currentDetalle.unidadMedida.required' => 'La unidad de medida es obligatoria.',
        'currentDetalle.descripcion.required' => 'La descripción del detalle es obligatoria.',
        'currentDetalle.valorUnitario.required' => 'El valor unitario es obligatorio.',
    ];

    public function mount()
    {
        $this->tiposDocumento = TipoDocumento::all();
        $this->proveedores    = Proveedor::all();
        $this->clientes       = Cliente::orderBy('razonSocial')->get();
        $this->estados        = Estado::all();
        $this->fechaEmision   = now()->format('Y-m-d');

        // NO INICIALIZAMOS currentDetalle en mount aquí, se hará al abrir el modal
    }

    public function calculateTotals()
    {
        $this->subTotal = 0;
        foreach ($this->detalles as $detalle) {
            $this->subTotal += ($detalle['cantidad'] * $detalle['valorUnitario']);
        }

        $this->igv = $this->subTotal * 0.18; // Ajusta el IGV si es necesario
        $this->total = $this->subTotal + $this->igv;
        $this->totalLetras = "Son " . number_format($this->total, 2) . " " . ($this->monedas[$this->moneda] ?? '') . " y 00/100";
    }

    // --- Métodos para el Modal de Detalle ---

    public function openAddDetalleModal()
    {
        // Inicializar el detalle para el modal con valores por defecto
        $this->currentDetalle = [
            'cantidad'      => 1,
            'unidadMedida'  => '',
            'descripcion'   => '',
            'valorUnitario' => 0,
            'estado'        => 1,
        ];
        $this->resetErrorBag('currentDetalle.*'); // Limpiar errores de validación previos del modal
        $this->showDetalleModal = true;
        // Emitir un evento JavaScript para mostrar el modal de Bootstrap
        $this->emit('show-detalle-modal');
    }

    public function closeDetalleModal()
    {
        $this->showDetalleModal = false;
        // Emitir un evento JavaScript para ocultar el modal de Bootstrap
        $this->emit('hide-detalle-modal');
    }

    public function addDetalleToCompra()
    {
        // Validar los campos del detalle actual
        $this->validate($this->currentDetalleRules);

        // Clonar el currentDetalle para evitar referencias
        $this->detalles[] = $this->currentDetalle;

        $this->calculateTotals();
        $this->closeDetalleModal(); // Cerrar el modal después de agregar
    }

    public function removeDetalle($index)
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles); // Reindexar el array
        $this->calculateTotals();
    }

    // Escucha el evento 'detalle-updated' del componente CompraDetalleItem hijo
    // Esto es para cuando edites un detalle directamente en la lista (si lo permites)
    public function updateDetalleFromChild($updatedItem, $index)
    {
        if (isset($this->detalles[$index])) {
            $this->detalles[$index] = $updatedItem;
            $this->calculateTotals();
        }
    }

    // Método que se ejecuta cada vez que una propiedad del componente principal es actualizada
    public function updated($propertyName)
    {
        // Validar solo la propiedad que ha cambiado del formulario principal
        if (in_array($propertyName, array_keys($this->rules))) {
            $this->validateOnly($propertyName);
        }

        // Recalcular totales si se cambia la moneda (puede afectar totalLetras)
        if ($propertyName === 'moneda') {
            $this->calculateTotals();
        }

        // Opcional: Validar campos del modal en tiempo real mientras se escribe
        if (str_starts_with($propertyName, 'currentDetalle.')) {
            $this->validateOnly($propertyName, $this->currentDetalleRules);
        }
    }


    // --- Método para guardar la compra completa ---

    public function saveCompra()
    {
        // 1. **VERIFICAR QUE HAYA AL MENOS UN DETALLE ANTES DE VALIDAR EL RESTO**
        if (empty($this->detalles)) {
            session()->flash('error', 'Debe agregar al menos un detalle de compra para poder guardar.');
            // Emitir un evento para que el frontend pueda, por ejemplo, hacer un scroll a la sección de detalles o resaltar el botón de "Agregar Detalle".
            $this->emit('no-detalles-error');
            return;
        }

        // 2. Ejecutar la validación de los campos principales de la compra
        $this->validate();

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            $compra = Compra::create([
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
                'igv'                => $this->igv,
                'total'              => $this->total,
                'totalLetras'        => $this->totalLetras,
                'observacion'        => $this->observacion,
                'estado'             => Estado::where('descripcion', 'Pendiente')->first()->id ?? 1,
                'usuarioCreacion'    => Auth::id(),
                'usuarioModificacion' => Auth::id(),
            ]);

            foreach ($this->detalles as $detalleData) {
                $compra->detalles()->create([
                    'cantidad'      => $detalleData['cantidad'],
                    'unidadMedida'  => $detalleData['unidadMedida'],
                    'descripcion'   => $detalleData['descripcion'],
                    'valorUnitario' => $detalleData['valorUnitario'],
                    'estado'        => Estado::where('descripcion', 'Activo')->first()->id ?? 1,
                ]);
            }

            DB::commit();

            session()->flash('message', '¡Compra registrada exitosamente!');
            $this->reset(); // Limpiar el formulario después de guardar
            $this->detalles = []; // Asegurarse de que los detalles también se reseteen
            $this->calculateTotals(); // Recalcular a 0
            return redirect()->route('listaCompras');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Hubo un error al registrar la compra: ' . $e->getMessage());
            \Log::error('Error al registrar compra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.compras.create-compra', [
            'tiposDocumento' => $this->tiposDocumento,
            'proveedores'    => $this->proveedores,
            'clientes'       => $this->clientes,
            'estados'        => $this->estados,
        ]);
    }
}