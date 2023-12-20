<?php

namespace App\Http\Livewire\Gestion;

use App\Models\Aerolinea;
use App\Models\Area;
use App\Models\Boleto;
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
use App\Models\TipoTicket;
use App\Models\Vendedor;
use Livewire\Component;
use Livewire\WithPagination;
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
use App\Models\Servicio;

class Boletos extends Component
{
    use WithPagination;
    public $search = "";
    public $sort= 'numeroBoleto';
    public $direction = 'asc';
    public $clientes;
    public $solicitantes;
    public $selectedCliente = NULL;
    public $selectedSolicitante = 0;

    public $idRegistro,$numeroBoleto,$numeroFile,$fechaEmision,$idCounter,
            $idTipoFacturacion,$idTipoDocumento=6,$idArea,$idVendedor,$idConsolidador=2,$codigoReserva,
            $fechaReserva,$idGds=2,$idTipoTicket=1,$tipoRuta="NACIONAL",$tipoTarifa="NORMAL",$idAerolinea=7,
            $origen="BSP",$pasajero,
            $idTipoPasajero=1,$ruta,$destino,$idDocumento,$tipoCambio,$idMoneda=2,$tarifaNeta=0,$igv=0,
            $otrosImpuestos=0,$xm=0,$total=0,$totalOrigen=0,$porcentajeComision,$montoComision=0,
            $descuentoCorporativo,$codigoDescCorp,$tarifaNormal,$tarifaAlta,$tarifaBaja,
            $idTipoPagoConsolidador,$centroCosto,$cod1,$cod2,$cod3,$cod4,$observaciones,$estado=1,
            $usuarioCreacion,$fechaCreacion,$usuarioModificacion,$fechaModificacion;
    
    public $ciudadSalida,$ciudadLlegada,$idAerolineaRuta,$vuelo,$clase,$fechaSalida,$horaSalida,$fechaLlegada,
            $horaLlegada,$farebasis;
    Public $boletoRutas;

    public $idMedioPago,$idTarjetaCredito,$numeroTarjeta,$monto,$fechaVencimientoTC,$boletoPagos;

    Public $tarifaFee=0,$tipoDocFee;
    
    public function rules(){
        return[
            'numeroBoleto' => 'required|digits:10',
            'selectedCliente' => 'required',
            'fechaEmision' => 'required',
            'idCounter' => 'required',
            'idTipoFacturacion' => 'required',
            'idTipoDocumento' => 'required',
            'idArea' => 'required',
            'idVendedor' => 'required',
            'idConsolidador' => 'required',
            'codigoReserva' => 'required',
            'fechaReserva' => 'required',
            'idGds' => 'required',
            'idTipoTicket' => 'required',
            'tipoRuta' => 'required',
            'tipoTarifa' => 'required',
            'idAerolinea' => 'required',
            'origen' => 'required',
            'pasajero' => 'required',
            'idTipoPasajero' => 'required',
            'tipoCambio' => 'required',
            'idMoneda' => 'required',
            'tarifaNeta' => 'required',
            'igv' => 'required',
            'otrosImpuestos' => 'required',
            'total' => 'required',
            'totalOrigen' => 'required',
            'idTipoPagoConsolidador' => 'required',
            'estado' => 'required',

            'ciudadSalida' => 'required',
            'ciudadLlegada' => 'required',
            'idAerolineaRuta' => 'required',
            'vuelo' => 'required',
            'clase' => 'required',
            'fechaSalida' => 'required',
            'horaSalida' => 'required',
            'fechaLlegada' => 'required',
            'horaLlegada' => 'required',

            'idMedioPago' => 'required',
            'monto' => 'required',
        ];
    }

    protected $messages = [
        'numeroBoleto.required' => 'Este campo es requerido',
        'numeroBoleto.digits' => 'Máximo 10 caracteres',
        'idCliente.required' => 'Este campo es requerido',
        'fechaEmision.required' => 'Este campo es requerido',
        'idCounter.required' => 'Este campo es requerido',
        'idTipoFacturacion.required' => 'Este campo es requerido',
        'idTipoDocumento.required' => 'Este campo es requerido',
        'idArea.required' => 'Este campo es requerido',
        'idVendedor.required' => 'Este campo es requerido',
        'idConsolidador.required' => 'Este campo es requerido',
        'codigoReserva.required' => 'Este campo es requerido',
        'fechaReserva.required' => 'Este campo es requerido',
        'idGds.required' => 'Este campo es requerido',
        'idTipoTicket.required' => 'Este campo es requerido',
        'tipoRuta.required' => 'Este campo es requerido',
        'tipoTarifa.required' => 'Este campo es requerido',
        'idAerolinea.required' => 'Este campo es requerido',
        'origen.required' => 'Este campo es requerido',
        'pasajero.required' => 'Este campo es requerido',
        'idTipoPasajero.required' => 'Este campo es requerido',
        'tipoCambio.required' => 'Este campo es requerido',
        'idMoneda.required' => 'Este campo es requerido',
        'tarifaNeta.required' => 'Este campo es requerido',
        'igv.required' => 'Este campo es requerido',
        'otrosImpuestos.required' => 'Este campo es requerido',
        'total.required' => 'Este campo es requerido',
        'totalOrigen.required' => 'Este campo es requerido',
        'idTipoPagoConsolidador.required' => 'Este campo es requerido',
        'estado.required' => 'Este campo es requerido',

        'ciudadSalida.required' => 'Requerido',
        'ciudadLlegada.required' => 'Requerido',
        'idAerolineaRuta.required' => 'Requerido',
        'vuelo.required' => 'Requerido',
        'clase.required' => 'Requerido',
        'fechaSalida.required' => 'Requerido',
        'horaSalida.required' => 'Requerido',
        'fechaLlegada.required' => 'Requerido',
        'horaLlegada.required' => 'Requerido',

        'idMedioPago.required' => 'Requerido',
        'monto.required' => 'Requerido',
    ];

    public function mount(){
        $this->clientes = Cliente::all()->sortBy('razonSocial');
        $this->solicitantes = collect();
        $this->boletoRutas = new Collection();
        $this->boletoPagos = new Collection();
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
    }

    public function render()
    {
        $boletos = Boleto::where('numeroBoleto', 'like', "%$this->search%")
                            ->orderBy($this->sort, $this->direction)
                            ->paginate(6);
        $counters = Counter::all()->sortBy('nombre');
        $tipoFacturacions = TipoFacturacion::all()->sortBy('descripcion');
        $tipoDocumentos = TipoDocumento::all()->sortBy('descripcion');
        $areas = Area::all()->sortBy('descripcion');
        $vendedors = Vendedor::all()->sortBy('nombre');
        $consolidadors = Proveedor::where('esConsolidador',1)->get();
        $gdss = Gds::all()->sortBy('descripcion');
        $tipoTickets = TipoTicket::all()->sortBy('descripcion');
        $aerolineas = Aerolinea::all()->sortBy('razonSocial');
        $tipoPasajeros = TipoPasajero::all()->sortBy('descripcion');
        //ToDo: Agregar Documentos
        $monedas = moneda::all()->sortBy('codigo');
        $estados = Estado::all()->sortBy('descripcion');
        $usuarios = User::all()->sortBy('name');
        $medioPagos = MedioPago::all()->sortBy('descripcion');
        $tarjetaCreditos = TarjetaCredito::all()->sortBy('descripcion');
        return view('livewire.gestion.boletos', compact('boletos','counters'
                                                        ,'tipoFacturacions','tipoDocumentos','areas','medioPagos',
                                                    'vendedors','consolidadors','gdss','tipoTickets',
                                                    'aerolineas','tipoPasajeros','monedas','estados','usuarios',
                                                    'tarjetaCreditos'));
    
        
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
        $boleto = new Boleto();
        $funciones = new Funciones();
        $boleto->numeroBoleto = $this->numeroBoleto;

        $file = $funciones->generaFile('FILES');
        $boleto->numeroFile = $area->codigo . str_pad($file,7,"0",STR_PAD_LEFT);
        $boleto->idCliente = $this->selectedCliente;
        $boleto->idSolicitante = $this->selectedSolicitante;
        $boleto->fechaEmision = $this->fechaEmision;
        $boleto->idCounter = $this->idCounter;
        $boleto->idTipoFacturacion = $this->idTipoFacturacion;
        $boleto->idTipoDocumento = $this->idTipoDocumento;
        $boleto->idArea = $this->idArea;
        $boleto->idVendedor = $this->idVendedor;
        $boleto->idConsolidador = $this->idConsolidador;
        $boleto->codigoReserva = $this->codigoReserva;
        $boleto->fechaReserva = $this->fechaReserva;
        $boleto->idGds = $this->idGds;
        $boleto->idTipoTicket = $this->idTipoTicket;
        $boleto->tipoRuta = $this->tipoRuta;
        $boleto->tipoTarifa = $this->tipoTarifa;
        $boleto->idAerolinea = $this->idAerolinea;
        $boleto->origen = $this->origen;
        $boleto->pasajero = $this->pasajero;
        $boleto->idTipoPasajero = $this->idTipoPasajero;
        $boleto->ruta = $this->ruta;
        $boleto->destino = $this->destino;
        $boleto->tipoCambio = $this->tipoCambio;
        $boleto->idMoneda = $this->idMoneda;
        $boleto->tarifaNeta = $this->tarifaNeta;
        $boleto->igv = $this->igv;
        $boleto->otrosImpuestos = $this->otrosImpuestos;
        $boleto->xm = $this->xm;
        $boleto->total = $this->total;
        $boleto->totalOrigen = $this->totalOrigen;
        $boleto->porcentajeComision = $this->porcentajeComision;
        $boleto->montoComision = $this->montoComision;
        $boleto->descuentoCorporativo = $this->descuentoCorporativo;
        $boleto->codigoDescCorp = $this->codigoDescCorp;
        $boleto->tarifaNormal = $this->tarifaNormal;
        $boleto->tarifaAlta = $this->tarifaAlta;
        $boleto->tarifaBaja = $this->tarifaBaja;
        $boleto->idTipoPagoConsolidador = $this->idTipoPagoConsolidador;
        $boleto->centroCosto = $this->centroCosto;
        $boleto->cod1 = $this->cod1;
        $boleto->cod2 = $this->cod2;
        $boleto->cod3 = $this->cod3;
        $boleto->cod4 = $this->cod4;
        $boleto->observaciones = $this->observaciones;
        $boleto->estado = $this->estado;
        $boleto->usuarioCreacion = auth()->user()->id;
        try {
            $boleto->save();
            $this->grabarRutas($boleto->id);
            $this->grabarPagos($boleto->id);
        } catch (\Throwable $th) {
            session()->flash('error', 'Ocurrió un error intentando grabar.');
        }
         
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han guardado exitosamente.');
    }

    public function grabarRutas($idBoleto){
        $boletoRuta = new BoletoRuta();
        $boletoRuta->idBoleto = $idBoleto;
        $boletoRuta->idAerolinea = $this->idAerolineaRuta;
        $boletoRuta->ciudadSalida = $this->ciudadSalida;
        $boletoRuta->ciudadLlegada = $this->ciudadLlegada;
        $boletoRuta->vuelo = $this->vuelo;
        $boletoRuta->clase = $this->clase;
        $boletoRuta->fechaSalida = $this->fechaSalida;
        $boletoRuta->horaSalida = $this->horaSalida;
        $boletoRuta->fechaLlegada = $this->fechaLlegada;
        $boletoRuta->horaLlegada = $this->horaLlegada;
        $boletoRuta->farebasis = $this->farebasis;
        $boletoRuta->idEstado = 1;
        $boletoRuta->usuarioCreacion = auth()->user()->id;
        $boletoRuta->save();
    }

    public function grabarPagos($idBoleto){
        $boletoPago = new BoletoPago();
        $boletoPago->idBoleto = $idBoleto;
        $boletoPago->idMedioPago = $this->idMedioPago;
        $boletoPago->idTarjetaCredito = $this->idTarjetaCredito;
        $boletoPago->numeroTarjeta = $this->numeroTarjeta;
        $boletoPago->monto = $this->monto;
        $boletoPago->fechaVencimientoTC = $this->fechaVencimientoTC;
        $boletoPago->idEstado = 1;
        $boletoPago->usuarioCreacion = auth()->user()->id;
        $boletoPago->save();
    }
    
    public function limpiarControles(){
        $this->idRegistro = 0;
        $this->numeroBoleto = '';
        $this->numeroFile = '';
        $this->selectedCliente = '';
        $this->selectedSolicitante = '';
        $this->fechaEmision = '';
        $this->idCounter = '';
        $this->idTipoFacturacion = '';
        $this->idTipoDocumento = '';
        $this->idArea = '';
        $this->idVendedor = '';
        $this->idConsolidador = '';
        $this->codigoReserva = '';
        $this->fechaReserva = '';
        $this->idGds = '';
        $this->idTipoTicket = '';
        $this->tipoRuta = '';
        $this->tipoTarifa = '';
        $this->idAerolinea = '';
        $this->origen = '';
        $this->pasajero = '';
        $this->idTipoPasajero = '';
        $this->ruta = '';
        $this->destino = '';
        $this->idDocumento = '';
        $this->tipoCambio = '';
        $this->idMoneda = '';
        $this->tarifaNeta = '';
        $this->igv = '';
        $this->otrosImpuestos = '';
        $this->xm = '';
        $this->total = '';
        $this->totalOrigen = '';
        $this->porcentajeComision = '';
        $this->montoComision = '';
        $this->descuentoCorporativo = '';
        $this->codigoDescCorp = '';
        $this->tarifaNormal = '';
        $this->tarifaAlta = '';
        $this->tarifaBaja = '';
        $this->idTipoPagoConsolidador = '';
        $this->centroCosto = '';
        $this->cod1 = '';
        $this->cod2 = '';
        $this->cod3 = '';
        $this->cod4 = '';
        $this->observaciones = '';
        $this->estado = '';
        $this->usuarioCreacion = '';
        $this->usuarioModificacion = '';
    }

    public function editar($id){
        $boleto = Boleto::find($id);
        $this->limpiarControles();
        $this->idRegistro = $boleto->id;
        $this->numeroBoleto = $boleto->numeroBoleto;
        $this->numeroFile = $boleto->numeroFile;
        $this->selectedCliente = $boleto->idCliente;
        $this->selectedSolicitante = $boleto->idSolicitante;
        $this->fechaEmision = $boleto->fechaEmision;
        $this->idCounter = $boleto->idCounter;
        $this->idTipoFacturacion = $boleto->idTipoFacturacion;
        $this->idTipoDocumento = $boleto->idTipoDocumento;
        $this->idArea = $boleto->idArea;
        $this->idVendedor = $boleto->idVendedor;
        $this->idConsolidador = $boleto->idConsolidador;
        $this->codigoReserva = $boleto->codigoReserva;
        $this->fechaReserva = $boleto->fechaReserva;
        $this->idGds = $boleto->idGds;
        $this->idTipoTicket = $boleto->idTipoTicket;
        $this->tipoRuta = $boleto->tipoRuta;
        $this->tipoTarifa = $boleto->tipoTarifa;
        $this->idAerolinea = $boleto->idAerolinea;
        $this->origen = $boleto->origen;
        $this->pasajero = $boleto->pasajero;
        $this->idTipoPasajero = $boleto->idTipoPasajero;
        $this->ruta = $boleto->ruta;
        $this->destino = $boleto->destino;
        $this->idDocumento = $boleto->idDocumento;
        $this->tipoCambio = $boleto->tipoCambio;
        $this->idMoneda = $boleto->idMoneda;
        $this->tarifaNeta = $boleto->tarifaNeta;
        $this->igv = $boleto->igv;
        $this->otrosImpuestos = $boleto->otrosImpuestos;
        $this->xm = $boleto->xm;
        $this->total = $boleto->total;
        $this->totalOrigen = $boleto->totalOrigen;
        $this->porcentajeComision = $boleto->porcentajeComision;
        $this->montoComision = $boleto->montoComision;
        $this->descuentoCorporativo = $boleto->descuentoCorporativo;
        $this->codigoDescCorp = $boleto->codigoDescCorp;
        $this->tarifaNormal = $boleto->tarifaNormal;
        $this->tarifaAlta = $boleto->tarifaAlta;
        $this->tarifaBaja = $boleto->tarifaBaja;
        $this->idTipoPagoConsolidador = $boleto->idTipoPagoConsolidador;
        $this->centroCosto = $boleto->centroCosto;
        $this->cod1 = $boleto->cod1;
        $this->cod2 = $boleto->cod2;
        $this->cod3 = $boleto->cod3;
        $this->cod4 = $boleto->cod4;
        $this->observaciones = $boleto->observaciones;
        $this->estado = $boleto->estado;
        $this->usuarioCreacion = $boleto->usuarioCreacion;
        $this->fechaCreacion = Carbon::parse($boleto->created_at)->format("Y-m-d");
        $this->usuarioModificacion = $boleto->usuarioModificacion;
        $this->fechaModificacion = Carbon::parse($boleto->updated_at)->format("Y-m-d");
    }

    public function actualizar($id){
        $boleto = Boleto::find($id);
        $boleto->numeroBoleto = $this->numeroBoleto;
        $boleto->numeroFile = $this->numeroFile;
        $boleto->idCliente = $this->selectedCliente;
        $boleto->idSolicitante = $this->selectedSolicitante;
        $boleto->fechaEmision = $this->fechaEmision;
        $boleto->idCounter = $this->idCounter;
        $boleto->idTipoFacturacion = $this->idTipoFacturacion;
        $boleto->idTipoDocumento = $this->idTipoDocumento;
        $boleto->idArea = $this->idArea;
        $boleto->idVendedor = $this->idVendedor;
        $boleto->idConsolidador = $this->idConsolidador;
        $boleto->codigoReserva = $this->codigoReserva;
        $boleto->fechaReserva = $this->fechaReserva;
        $boleto->idGds = $this->idGds;
        $boleto->idTipoTicket = $this->idTipoTicket;
        $boleto->tipoRuta = $this->tipoRuta;
        $boleto->tipoTarifa = $this->tipoTarifa;
        $boleto->idAerolinea = $this->idAerolinea;
        $boleto->origen = $this->origen;
        $boleto->pasajero = $this->pasajero;
        $boleto->idTipoPasajero = $this->idTipoPasajero;
        $boleto->ruta = $this->ruta;
        $boleto->destino = $this->destino;
        $boleto->idDocumento = $this->idDocumento;
        $boleto->tipoCambio = $this->tipoCambio;
        $boleto->idMoneda = $this->idMoneda;
        $boleto->tarifaNeta = $this->tarifaNeta;
        $boleto->igv = $this->igv;
        $boleto->otrosImpuestos = $this->otrosImpuestos;
        $boleto->xm = $this->xm;
        $boleto->total = $this->total;
        $boleto->totalOrigen = $this->totalOrigen;
        $boleto->porcentajeComision = $this->porcentajeComision;
        $boleto->montoComision = $this->montoComision;
        $boleto->descuentoCorporativo = $this->descuentoCorporativo;
        $boleto->codigoDescCorp = $this->codigoDescCorp;
        $boleto->tarifaNormal = $this->tarifaNormal;
        $boleto->tarifaAlta = $this->tarifaAlta;
        $boleto->tarifaBaja = $this->tarifaBaja;
        $boleto->idTipoPagoConsolidador = $this->idTipoPagoConsolidador;
        $boleto->centroCosto = $this->centroCosto;
        $boleto->cod1 = $this->cod1;
        $boleto->cod2 = $this->cod2;
        $boleto->cod3 = $this->cod3;
        $boleto->cod4 = $this->cod4;
        $boleto->observaciones = $this->observaciones;
        $boleto->estado = $this->estado;
        $boleto->usuarioModificacion = auth()->user()->id;
        $boleto->save();
        $this->limpiarControles();
        session()->flash('success', 'Los datos se han actualizado exitosamente.');
    }

    public function encontrar($id){
        $boleto = Boleto::find($id);
        $this->idRegistro = $boleto->id;
        $this->numeroBoleto = $boleto->numeroBoleto;
    }

    public function eliminar($id){
        $boleto = Boleto::find($id);
        $boleto->delete();
        $this->limpiarControles();
    }

    public function exportar(){
        return Excel::download(new BoletoExport,'Boletos.xlsx');
    }

    public function addRuta($idBoleto){
        // $this->validate();
        if ($this->ciudadSalida !== null && $this->ciudadLlegada  !== null && $this->idAerolineaRuta  !== null 
            && $this->vuelo  !== null && $this->clase  !== null && $this->fechaSalida  !== null 
            && $this->horaSalida  !== null && $this->fechaLlegada  !== null && $this->horaLlegada  !== null) {
            
            $aer = Aerolinea::find($this->idAerolineaRuta);
            $this->boletoRutas->add(array(
                'ciudadSalida' =>  $this->ciudadSalida,
                'ciudadLlegada' =>  $this->ciudadLlegada,
                'idAerolinea' =>  $this->idAerolineaRuta,
                'aerolinea' => $aer->razonSocial,
                'vuelo' =>  $this->vuelo,
                'clase' =>  $this->clase,
                'fechaSalida' =>  $this->fechaSalida,
                'horaSalida' =>  $this->horaSalida,
                'fechaLlegada' =>  $this->fechaLlegada,
                'horaLlegada' =>  $this->horaLlegada,
                'farebasis' =>  $this->farebasis
            ));
            $this->getRutaDestino($this->boletoRutas);
            $this->resetRutas();
        }  
    }
    public function getRutaDestino($ruta){
        $cRuta = "";
        $contador = 0;
        foreach ($ruta as $indice => $columna) {
            $contador = $contador + 1;
            if ($contador == 1) {
                foreach ($columna as $campo => $valor) {
                    if ($campo == "ciudadSalida") {
                        $cRuta = $cRuta . $valor ."/";
                    }
                    if ($campo == "ciudadLlegada") {
                        $cRuta = $cRuta . $valor ."/";
                    }
                }
            }else{
                foreach ($columna as $campo => $valor) {
                    if ($campo == "ciudadLlegada") {
                        $cRuta = $cRuta . $valor ."/";
                    }
                }
            }
            
        }
        $this->ruta = substr($cRuta,0,strlen($cRuta)-1);
        $cCadena = str_replace("/","",$this->ruta);
        $incioCadena = round(((strlen($cCadena) / 3) / 2),0,PHP_ROUND_HALF_DOWN) * 3;
        // dd($incioCadena);
        $this->destino =  substr($cCadena, $incioCadena, 3);
    }
    public function quitarRuta($indice){
        unset($this->boletoRutas[$indice]);
    }
    public function resetRutas(){
        $this->ciudadSalida = '';
        $this->ciudadLlegada = '';
        $this->idAerolineaRuta = '';
        $this->vuelo = '';
        $this->clase = '';
        $this->fechaSalida = '';
        $this->horaSalida = '';
        $this->fechaLlegada = '';
        $this->horaLlegada = '';
        $this->farebasis = '';

    }

    public function addPago(){
        if ($this->idMedioPago !== null && $this->idTarjetaCredito  !== null && $this->monto  !== null) {
            $mp = MedioPago::find($this->idMedioPago);
            $tc = TarjetaCredito::find($this->idTarjetaCredito);
            $this->boletoPagos->add(array(
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
        unset($this->boletoPagos[$indice]);
    }

    public function vaciarRutas($idBoleto){
        $this->boletoRutas->NULL;
    }

    public function generarFee(){

    }

    
}