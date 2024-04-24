<?php

namespace App\Http\Livewire\Gestion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Aerolinea;
use App\Models\Area;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Counter;
use App\Models\Estado;
use App\Models\Gds;
use App\Models\moneda;
use App\Models\Proveedor;
use App\Models\Solicitante;
use App\Models\TipoDocumento;
use App\Models\TipoFacturacion;
use App\Models\TipoPasajero;
use App\Models\TipoServicio;
use App\Models\Vendedor;
use App\Clases\Funciones;
use App\Exports\BoletoExport;
use App\Models\MedioPago;
use App\Models\TipoCambio;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BoletoRuta;
use App\Models\BoletoPago;
use App\Models\TarjetaCredito;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\ServicioPago;
use App\Models\Documento;

class Servicios extends Component
{
    use WithPagination;
    public $search = "";
    public $sort= 'id';
    public $direction = 'desc';
    public $clientes;
    public $solicitantes;
    public $selectedCliente = NULL;
    public $selectedSolicitante = NULL;
    public $filtroCliente;

    public $idRegistro=0,$numeroServicio,$numeroFile,$fechaEmision,$idCounter,
            $idTipoFacturacion,$idTipoDocumento=1,$idArea,$idVendedor,$idProveedor=0,
            $fechaReserva,$fechaIn,$fechaOut,$idTipoServicio=1,$tipoRuta="NACIONAL",$tipoTarifa="NORMAL",
            $origen="BSP",$pasajero,$idDocumento,$tipoCambio,$idMoneda=1,$tarifaNeta=0,$inafecto=0,$igv=0,
            $otrosImpuestos=0,$xm=0,$total=0,$totalOrigen=0,$porcentajeComision,$montoComision=0,
            $descuentoCorporativo,$codigoDescCorp,$tarifaNormal,$tarifaAlta,$tarifaBaja,$destino,
            $centroCosto,$cod1,$cod2,$cod3,$cod4,$observaciones,$estado=1,$idTipoPagoConsolidador=6,
            $usuarioCreacion,$fechaCreacion,$usuarioModificacion,$fechaModificacion,$numDoc;

    public $idMedioPago=6,$idTarjetaCredito=1,$numeroTarjeta,$monto,$fechaVencimientoTC,$servicioPagos,$servPag;

    public function limpiarControles(){
        $this->idRegistro = 0;
        $this->numeroServicio = '';
        $this->selectedCliente = NULL;
        $this->selectedSolicitante = NULL;
        $this->numeroFile = NULL;
        $this->fechaEmision = '';
        $this->idCounter = 1;
        $this->idTipoFacturacion = 1;
        $this->idTipoDocumento = 1;
        $this->idArea = 1;
        $this->idVendedor = 1;
        $this->idProveedor = 1;
        $this->fechaReserva = '';
        $this->idTipoServicio = 1;
        $this->tipoRuta ="NACIONAL";
        $this->tipoTarifa = "NORMAL";
        $this->origen = '';
        $this->pasajero = '';
        $this->idDocumento = NULL;
        $this->tipoCambio = 0;
        $this->idMoneda = 1;
        $this->tarifaNeta = 0;
        $this->inafecto = 0;
        $this->igv = 0;
        $this->otrosImpuestos = 0;
        $this->xm = 0;
        $this->total = 0;
        $this->totalOrigen = 0;
        $this->porcentajeComision = 0;
        $this->montoComision = 0;
        $this->descuentoCorporativo = 0;
        $this->codigoDescCorp = '';
        $this->tarifaNormal = 0;
        $this->tarifaAlta = 0;
        $this->tarifaBaja = 0;
        $this->centroCosto = '';
        $this->cod1 = '';
        $this->cod2 = '';
        $this->cod3 = '';
        $this->cod4 = '';
        $this->observaciones = '';
        $this->estado = 1;
        $this->usuarioCreacion = NULL;
        $this->fechaCreacion = '';
        $this->usuarioModificacion = NULL;
        $this->fechaModificacion = '';
        $this->idMedioPago = 6;
        $this->idTarjetaCredito = 1;
        $this->numeroTarjeta = '';
        $this->monto = '';
        $this->fechaVencimientoTC = '';
        $this->servicioPagos = new Collection();
    }

    public function rules(){
        return[
            'selectedCliente' => 'required',
            'fechaEmision' => 'required',
            'idCounter' => 'required',
            'idTipoFacturacion' => 'required',
            'idTipoDocumento' => 'required',
            'idArea' => 'required',
            'idVendedor' => 'required',
            'idProveedor' => 'required',
            'fechaReserva' => 'required',
            'idTipoServicio' => 'required',
            'pasajero' => 'required',
            'tipoCambio' => 'required',
            'idMoneda' => 'required',
            'tarifaNeta' => 'required',
            'igv' => 'required',
            'total' => 'required',
            'totalOrigen' => 'required',
            'estado' => 'required',

            // 'idMedioPago' => 'required',
            // 'monto' => 'required',
        ];
    }

    protected $messages = [
        'numeroServicio.required' => 'Este campo es requerido',
        'selectedCliente.required' => 'Este campo es requerido',
        'selectedSolicitante.required' => 'Este campo es requerido',
        'fechaEmision.required' => 'Este campo es requerido',
        'idCounter.required' => 'Este campo es requerido',
        'idTipoFacturacion.required' => 'Este campo es requerido',
        'idTipoDocumento.required' => 'Este campo es requerido',
        'idArea.required' => 'Este campo es requerido',
        'idVendedor.required' => 'Este campo es requerido',
        'idProveedor.required' => 'Este campo es requerido',
        'fechaReserva.required' => 'Este campo es requerido',
        'idTipoServicio.required' => 'Este campo es requerido',
        'pasajero.required' => 'Este campo es requerido',
        'tipoCambio.required' => 'Este campo es requerido',
        'idMoneda.required' => 'Este campo es requerido',
        'tarifaNeta.required' => 'Este campo es requerido',
        'igv.required' => 'Este campo es requerido',
        'total.required' => 'Este campo es requerido',
        'totalOrigen.required' => 'Este campo es requerido',
        'estado.required' => 'Este campo es requerido',

        'idMedioPago.required' => 'Requerido',
        'monto.required' => 'Debe ingresar datos del Pago',
    ];

    public function mount(){
        $this->clientes = Cliente::all()->sortBy('razonSocial');
        $this->solicitantes = collect();
        $this->servicioPagos = new Collection();
    }

    public function updatedselectedCliente($cliente_id){
        $this->solicitantes = Solicitante::where('cliente', $cliente_id)->get();
        $cliente = Cliente::find($cliente_id);
        $this->idCounter = $cliente->counter;
        $this->idVendedor = $cliente->vendedor;
        $this->idArea = $cliente->area;
        $this->idTipoFacturacion = $cliente->tipoFacturacion;
    }

    public function updatedfechaEmision($fechaEmision){
        // dd($fechaCambio);
        $tipoCambio = TipoCambio::where('fechaCambio',$fechaEmision)->first();
        if($tipoCambio){
            $this->tipoCambio = $tipoCambio->montoCambio;
        }else{
            $this->tipoCambio = 0.00;
        }
        $this->fechaReserva = $fechaEmision;
        $this->fechaIn = $fechaEmision;
        $this->fechaOut = $fechaEmision;
    }

    public function updatedtarifaNeta($tarifaNeta){
        if($this->tarifaNeta >= 0){
            $this->igv = round($this->tarifaNeta * 0.18,2);
            $this->total = round(($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto),2);
            $this->totalOrigen = round($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto - $this->xm,2);
        }
    }

    public function updatedotrosImpuestos($otrosImpuestos){
        if($this->otrosImpuestos >= 0){
            $this->total = round($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto,2);
            $this->totalOrigen = round($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto - $this->xm,2);
        } 
    }

    public function updatedinafecto($inafecto){
        if($this->inafecto >= 0){
            $this->total = round($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto,2);
            $this->totalOrigen = round($this->tarifaNeta + $this->igv + $this->otrosImpuestos + $this->inafecto - $this->xm,2);
        } 
    }

    public function updatedxm($xm){
        if($this->xm){
            $this->total = round($this->tarifaNeta + $this->inafecto + $this->igv + $this->otrosImpuestos,2);
            $this->totalOrigen = round($this->total - $this->xm,2);
        }
    }
    
    public function render()
    {
        $servicios = Servicio::query()
            ->when($this->filtroCliente, function($query){
                $query->where('idCliente', $this->filtroCliente);
            })
            ->when($this->search, function($query){
                $query->where(function ($subquery) {
                    $subquery->where('pasajero', 'like', '%' . $this->search . '%')
                        ->orWhere('numeroFile', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sort, $this->direction)
            ->paginate(6);

            

        // $servicios = Servicio::where('numeroFile', 'like', "%$this->search%")
        //                     ->orwhere('pasajero', 'like', "%$this->search%")
        //                     ->orderBy($this->sort, $this->direction)
        //                     ->paginate(6);

        // $servicios = Servicio::join('clientes', 'servicios.idCliente', '=', 'clientes.id')
        // ->join('documentos', 'servicios.idDocumento', '=', 'documentos.id')
        // ->where(function ($query) {
        //     $query->where('servicios.numeroFile', 'like', "%$this->search%")
        //         ->orWhere('servicios.pasajero', 'like', "%$this->search%")
        //         ->orWhere('clientes.razonSocial', 'like', "%$this->search%")
        //         ->orWhere('documentos.numero', 'like', "%$this->search%");
        // })
        // // ->orderBy($this->sort, $this->direction)
        // ->paginate(6);

        $counters = Counter::all()->sortBy('nombre');
        $tipoFacturacions = TipoFacturacion::all()->sortBy('descripcion');
        $tipoDocumentos = TipoDocumento::all()->sortBy('descripcion');
        $tipoServicios = TipoServicio::all()->sortBy('descripcion');
        $areas = Area::all()->sortBy('descripcion');
        $vendedors = Vendedor::all()->sortBy('nombre');
        $proveedors = Proveedor::all()->sortBy('razonSocial');
        $tipoPasajeros = TipoPasajero::all()->sortBy('descripcion');
        //ToDo: Agregar Documentos
        $monedas = moneda::all()->sortBy('codigo');
        $estados = Estado::all()->sortBy('descripcion');
        $usuarios = User::all()->sortBy('name');
        $medioPagos = MedioPago::all()->sortBy('descripcion');
        $tarjetaCreditos = TarjetaCredito::all()->sortBy('descripcion');
        return view('livewire.gestion.servicios', compact('servicios','counters','tipoFacturacions','tipoDocumentos',
                                                            'areas','medioPagos','vendedors','proveedors','tipoServicios',
                                                            'monedas','estados','usuarios','tarjetaCreditos','tipoPasajeros'));
    }

    public function order($sort){
        if ($this->sort == $sort) {
            if ($this->direction == 'desc') {
                $this->direction = 'asc';
            } else {
                $this->direction = 'desc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'desc';
        }
    }

    public function grabar(){
        $this->validate();
        $area = Area::find($this->idArea);
        $servicio = new Servicio();
        $funciones = new Funciones();

        // dd($this->servicioPagos);
        if(count($this->servicioPagos) == 0){
            session()->flash('ErrorPagos', 'Debe Ingresar los datos del pago.');
            return;
        }
        $numServ = $funciones->numeroServicio('SERVICIOS');
        $servicio->numeroServicio = $numServ;
        if($this->numeroFile){
            $servicio->numeroFile = $this->numeroFile;
        }else{
            $file = $funciones->generaFile('FILES');
            $servicio->numeroFile = $area->codigo . str_pad($file,7,"0",STR_PAD_LEFT);
        }
        $servicio->idCliente = $this->selectedCliente;
        $servicio->idSolicitante = $this->selectedSolicitante;
        $servicio->fechaEmision = $this->fechaEmision;
        $servicio->idCounter = $this->idCounter;
        $servicio->idTipoFacturacion = $this->idTipoFacturacion;
        $servicio->idTipoDocumento = $this->idTipoDocumento;
        $servicio->idArea = $this->idArea;
        $servicio->idVendedor = $this->idVendedor;
        $servicio->idProveedor = $this->idProveedor;
        $servicio->fechaReserva = $this->fechaReserva;
        $servicio->idTipoServicio = $this->idTipoServicio;
        $servicio->tipoRuta = $this->tipoRuta;
        $servicio->idGds = 1;
        $servicio->fechaIn = $this->fechaEmision;
        $servicio->fechaOut = $this->fechaEmision;
        $servicio->idTipoPasajero = 1;
        $servicio->codigoReserva = '';
        $servicio->inafecto = $this->inafecto;
        $servicio->idTipoPagoConsolidador = $this->idTipoPagoConsolidador;
        $servicio->tipoTarifa = $this->tipoTarifa;
        $servicio->origen = $this->origen;
        $servicio->pasajero = $this->pasajero;
        $servicio->tipoCambio = $this->tipoCambio;
        $servicio->idMoneda = $this->idMoneda;
        $servicio->tarifaNeta = $this->tarifaNeta;
        $servicio->igv = $this->igv;
        $servicio->otrosImpuestos = $this->otrosImpuestos;
        $servicio->xm = $this->xm;
        $servicio->total = $this->total;
        $servicio->totalOrigen = $this->totalOrigen;
        $servicio->porcentajeComision = $this->porcentajeComision;
        $servicio->montoComision = $this->montoComision;
        $servicio->descuentoCorporativo = $this->descuentoCorporativo;
        $servicio->codigoDescCorp = $this->codigoDescCorp;
        $servicio->tarifaNormal = $this->tarifaNormal;
        $servicio->tarifaAlta = $this->tarifaAlta;
        $servicio->tarifaBaja = $this->tarifaBaja;
        $servicio->centroCosto = $this->centroCosto;
        $servicio->cod1 = $this->cod1;
        $servicio->cod2 = $this->cod2;
        $servicio->cod3 = $this->cod3;
        $servicio->cod4 = $this->cod4;
        $servicio->observaciones = $this->observaciones;
        $servicio->estado = $this->estado;
        $servicio->usuarioCreacion = auth()->user()->id;
        $servicio->save();
        $this->grabarPagos($servicio->id);
        try {
            
        } catch (\Throwable $th) {
            session()->flash('error', 'Ocurrió un error intentando grabar.');
        }
         
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han guardado exitosamente.');
    }

    public function grabarPagos($idServicio){
        // dd($this->servicioPagos);
        $servicioPago = new ServicioPago();
        $servicioPago->idServicio = $idServicio;
        $servicioPago->idMedioPago = $this->servicioPagos[0]["idMedioPago"];
        $servicioPago->idTarjetaCredito = $this->servicioPagos[0]["idTarjetaCredito"];
        $servicioPago->numeroTarjeta = $this->servicioPagos[0]["numeroTarjeta"];
        $servicioPago->monto = $this->servicioPagos[0]["monto"];
        $servicioPago->fechaVencimientoTC = $this->servicioPagos[0]["fechaVencimientoTC"];
        $servicioPago->idEstado = 1;
        $servicioPago->usuarioCreacion = auth()->user()->id;
        // dd($servicioPago);
        $servicioPago->save();
    }

    public function editar($id){
        
        $servicio = Servicio::find($id);
        // dd($servicio);
        $this->limpiarControles();
        $this->idRegistro = $servicio->id;
        $this->numeroServicio = $servicio->numeroServicio;
        $this->numeroFile = $servicio->numeroFile;
        $this->selectedCliente = $servicio->idCliente;
        $this->updatedselectedCliente($servicio->idCliente);
        $this->selectedSolicitante = $servicio->idSolicitante;
        $this->fechaEmision = $servicio->fechaEmision;
        $this->idCounter = $servicio->idCounter;
        $this->idTipoFacturacion = $servicio->idTipoFacturacion;
        $this->idTipoDocumento = $servicio->idTipoDocumento;
        $this->idArea = $servicio->idArea;
        $this->idVendedor = $servicio->idVendedor;
        $this->idProveedor = $servicio->idProveedor;
        $this->fechaReserva = $servicio->fechaReserva;
        $this->fechaIn = $servicio->fechaIn;
        $this->fechaOut = $servicio->fechaOut;
        $this->idTipoServicio = $servicio->idTipoServicio;
        $this->tipoRuta = $servicio->tipoRuta;
        $this->tipoTarifa = $servicio->tipoTarifa;
        $this->origen = $servicio->origen;
        $this->pasajero = $servicio->pasajero;
        $this->idDocumento = $servicio->idDocumento;
        $this->tipoCambio = $servicio->tipoCambio;
        $this->idMoneda = $servicio->idMoneda;
        $this->tarifaNeta = $servicio->tarifaNeta;
        $this->inafecto = $servicio->inafecto;
        $this->igv = $servicio->igv;
        $this->otrosImpuestos = $servicio->otrosImpuestos;
        $this->xm = $servicio->xm;
        $this->total = $servicio->total;
        $this->totalOrigen = $servicio->totalOrigen;
        $this->porcentajeComision = $servicio->porcentajeComision;
        $this->montoComision = $servicio->montoComision;
        $this->descuentoCorporativo = $servicio->descuentoCorporativo;
        $this->codigoDescCorp = $servicio->codigoDescCorp;
        $this->tarifaNormal = $servicio->tarifaNormal;
        $this->tarifaAlta = $servicio->tarifaAlta;
        $this->tarifaBaja = $servicio->tarifaBaja;
        $this->centroCosto = $servicio->centroCosto;
        $this->cod1 = $servicio->cod1;
        $this->cod2 = $servicio->cod2;
        $this->cod3 = $servicio->cod3;
        $this->cod4 = $servicio->cod4;
        $this->observaciones = $servicio->observaciones;
        $this->estado = $servicio->estado;
        $this->usuarioCreacion = $servicio->usuarioCreacion;
        $this->fechaCreacion = Carbon::parse($servicio->created_at)->format("Y-m-d");
        $this->usuarioModificacion = $servicio->usuarioModificacion;
        $this->fechaModificacion = Carbon::parse($servicio->updated_at)->format("Y-m-d");

        if($this->idDocumento){
            $oDocumento = Documento::find($this->idDocumento);
            $this->numDoc = $oDocumento->serie . '-' . str_pad($oDocumento->numero,8,"0",STR_PAD_LEFT);
        }
    }

    public function actualizar($id){
        $servicio = Servicio::find($id);
        $servicio->numeroServicio = $this->numeroServicio;
        $servicio->numeroFile = $this->numeroFile;
        $servicio->idCliente = $this->selectedCliente;
        $servicio->idSolicitante = $this->selectedSolicitante;
        $servicio->fechaEmision = $this->fechaEmision;
        $servicio->idCounter = $this->idCounter;
        $servicio->idTipoFacturacion = $this->idTipoFacturacion;
        $servicio->idTipoDocumento = $this->idTipoDocumento;
        $servicio->idArea = $this->idArea;
        $servicio->idVendedor = $this->idVendedor;
        $servicio->idProveedor = $this->idProveedor;
        $servicio->fechaReserva = $this->fechaReserva;
        $servicio->idTipoServicio = $this->idTipoServicio;
        $servicio->tipoRuta = $this->tipoRuta;
        $servicio->tipoTarifa = $this->tipoTarifa;
        $servicio->origen = $this->origen;
        $servicio->pasajero = $this->pasajero;
        $servicio->idDocumento = $this->idDocumento;
        $servicio->tipoCambio = $this->tipoCambio;
        $servicio->idMoneda = $this->idMoneda;
        $servicio->tarifaNeta = $this->tarifaNeta;
        $servicio->inafecto = $this->inafecto;
        $servicio->igv = $this->igv;
        $servicio->otrosImpuestos = $this->otrosImpuestos;
        $servicio->xm = $this->xm;
        $servicio->total = $this->total;
        $servicio->totalOrigen = $this->totalOrigen;
        $servicio->porcentajeComision = $this->porcentajeComision;
        $servicio->montoComision = $this->montoComision;
        $servicio->descuentoCorporativo = $this->descuentoCorporativo;
        $servicio->codigoDescCorp = $this->codigoDescCorp;
        $servicio->tarifaNormal = $this->tarifaNormal;
        $servicio->tarifaAlta = $this->tarifaAlta;
        $servicio->tarifaBaja = $this->tarifaBaja;
        $servicio->centroCosto = $this->centroCosto;
        $servicio->cod1 = $this->cod1;
        $servicio->cod2 = $this->cod2;
        $servicio->cod3 = $this->cod3;
        $servicio->cod4 = $this->cod4;
        $servicio->observaciones = $this->observaciones;
        $servicio->estado = $this->estado;
        $servicio->usuarioModificacion = auth()->user()->id;
        $servicio->save();
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han actualizado exitosamente.');
    }

    public function encontrar($id){
        $servicio = Servicio::find($id);
        $this->idRegistro = $servicio->id;
        $this->numeroBoleto = $servicio->numeroServicio;
    }

    public function eliminar($id){
        $servicio = Servicio::find($id);
        $servicio->delete();
        $this->limpiarControles();
    }

    public function addPago(){
        if ($this->idMedioPago !== null && $this->idTarjetaCredito  !== null && $this->monto  !== null) {
            $mp = MedioPago::find($this->idMedioPago);
            $tc = TarjetaCredito::find($this->idTarjetaCredito);
            $this->servicioPagos->add(array(
                'idMedioPago' => $this->idMedioPago,
                'medioPago' => $mp->descripcion,
                'idTarjetaCredito' => $this->idTarjetaCredito,
                'tarjetaCredito' => $tc->descripcion,
                'numeroTarjeta' => $this->numeroTarjeta,
                'monto' => $this->monto,
                'fechaVencimientoTC' => $this->fechaVencimientoTC
            ));
            
            $this->resetPagos();
        }
    }

    public function resetPagos(){
        $this->idMedioPago = '';
        $this->idTarjetaCredito = '';
        $this->numeroTarjeta = '';
        $this->monto = '';
        $this->fechaVencimientoTC = '';
    }

    public function quitarPago($indice){
        unset($this->servicioPagos[$indice]);
    }

    public function clonarServicio(){
        $servicioOriginal = Servicio::find($this->idRegistro);
        $servicioPagoOriginal = ServicioPago::where('idServicio',$this->idRegistro)->first();
        $funciones = new Funciones();

        $numServ = $funciones->numeroServicio('SERVICIOS');
        
        $servicioClon = new Servicio();
        $servicioClon->numeroServicio = $numServ;

        $servicioClon->numeroFile = $servicioOriginal->numeroFile;
        $servicioClon->idCliente = $servicioOriginal->idCliente;
        $servicioClon->idSolicitante = $servicioOriginal->idSolicitante;
        $servicioClon->fechaEmision = $servicioOriginal->fechaEmision;
        $servicioClon->idCounter = $servicioOriginal->idCounter;
        $servicioClon->idTipoFacturacion = $servicioOriginal->idTipoFacturacion;
        $servicioClon->idTipoDocumento = $servicioOriginal->idTipoDocumento;
        $servicioClon->idArea = $servicioOriginal->idArea;
        $servicioClon->idVendedor = $servicioOriginal->idVendedor;
        $servicioClon->idProveedor = $servicioOriginal->idProveedor;
        $servicioClon->fechaReserva = $servicioOriginal->fechaReserva;
        $servicioClon->idTipoServicio = $servicioOriginal->idTipoServicio;
        $servicioClon->tipoRuta = $servicioOriginal->tipoRuta;
        $servicioClon->idGds = $servicioOriginal->idGds;
        $servicioClon->fechaIn = $servicioOriginal->fechaIn;
        $servicioClon->fechaOut = $servicioOriginal->fechaOut;
        $servicioClon->idTipoPasajero = $servicioOriginal->idTipoPasajero;
        $servicioClon->codigoReserva = $servicioOriginal->codigoReserva;
        $servicioClon->inafecto = $servicioOriginal->inafecto;
        $servicioClon->idTipoPagoConsolidador = $servicioOriginal->idTipoPagoConsolidador;
        $servicioClon->tipoTarifa = $servicioOriginal->tipoTarifa;
        $servicioClon->origen = $servicioOriginal->origen;
        $servicioClon->pasajero = $servicioOriginal->pasajero;
        $servicioClon->tipoCambio = $servicioOriginal->tipoCambio;
        $servicioClon->idMoneda = $servicioOriginal->idMoneda;
        $servicioClon->tarifaNeta = $servicioOriginal->tarifaNeta;
        $servicioClon->igv = $servicioOriginal->igv;
        $servicioClon->otrosImpuestos = $servicioOriginal->otrosImpuestos;
        $servicioClon->xm = $servicioOriginal->xm;
        $servicioClon->total = $servicioOriginal->total;
        $servicioClon->totalOrigen = $servicioOriginal->totalOrigen;
        $servicioClon->porcentajeComision = $servicioOriginal->porcentajeComision;
        $servicioClon->montoComision = $servicioOriginal->montoComision;
        $servicioClon->descuentoCorporativo = $servicioOriginal->descuentoCorporativo;
        $servicioClon->codigoDescCorp = $servicioOriginal->codigoDescCorp;
        $servicioClon->tarifaNormal = $servicioOriginal->tarifaNormal;
        $servicioClon->tarifaAlta = $servicioOriginal->tarifaAlta;
        $servicioClon->tarifaBaja = $servicioOriginal->tarifaBaja;
        $servicioClon->centroCosto = $servicioOriginal->centroCosto;
        $servicioClon->cod1 = $servicioOriginal->cod1;
        $servicioClon->cod2 = $servicioOriginal->cod2;
        $servicioClon->cod3 = $servicioOriginal->cod3;
        $servicioClon->cod4 = $servicioOriginal->cod4;
        $servicioClon->observaciones = $servicioOriginal->observaciones;
        $servicioClon->estado = $servicioOriginal->estado;
        $servicioClon->usuarioCreacion = $servicioOriginal->usuarioCreacion;
        $servicioClon->save();
        
        if($servicioPagoOriginal){
            $servicioPagoClon = new ServicioPago();
            $servicioPagoClon->idServicio = $servicioClon->id;
            $servicioPagoClon->idMedioPago = $servicioPagoOriginal->idMedioPago;
            $servicioPagoClon->idTarjetaCredito = $servicioPagoOriginal->idTarjetaCredito;
            $servicioPagoClon->numeroTarjeta = $servicioPagoOriginal->numeroTarjeta;
            $servicioPagoClon->monto = $servicioPagoOriginal->monto;
            $servicioPagoClon->fechaVencimientoTC = $servicioPagoOriginal->fechaVencimientoTC;
            $servicioPagoClon->idEstado = $servicioPagoOriginal->idEstado;
            $servicioPagoClon->usuarioCreacion = $servicioPagoOriginal->usuarioCreacion;
            $servicioPagoClon->save();
        }
        return redirect()->route('listaServicios');
    }
}
