<?php

namespace App\Http\Livewire\ApiBoletos;

use Livewire\Component;
use App\Services\ContinentalTravelApiService; // Importa tu servicio API
use Illuminate\Support\Facades\Log; // Para depuración
use Carbon\Carbon; 
use App\Models\Cliente;
use App\Clases\Funciones;
use App\Models\Boleto;
use App\Models\Area;
use App\Models\TipoCambio;
use App\Models\Aerolinea;
use App\Models\TarjetaCredito;
use App\Models\MedioPago;
use App\Models\File;
use App\Models\FileDetalle;
use App\Models\Counter;
use App\Models\BoletoRuta;
use App\Models\BoletoPago;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator; 

class BuscarBoleto extends Component
{
    use WithFileUploads;

    public $ticketNumber,$idCounter=1, $source = 'api', $jsonFile;
    public $ticketData = null; // Para almacenar los datos del boleto
    public $boletoInfo = null;
    public $segments = []; 
    public $taxes = [];
    public $errorMessage = null; // Para mensajes de error
    public $isLoading = false; // Para mostrar un spinner o mensaje de carga
    public $numeroFile,$fechaEmision,$idCliente,$idTipoFacturacion,$idTipoDocumento=6,$idArea=1,$idVendedor,
    $idConsolidador=2,$codigoReserva,$fechaReserva,$idGds=2,$idTipoTicket=1,$tipoRuta="NACIONAL",$tipoTarifa="NORMAL",
    $idAerolinea=7,$origen="BSP",$pasajero,$bookingAgent,$idTipoPasajero=1,$ruta,$destino,$idDocumento,$tipoCambio,
    $idMoneda=2,$tarifaNeta=0,$igv=0,$otrosImpuestos=0,$yr=0,$hw=0,$xm=0,$total=0,$totalOrigen=0,$porcentajeComision,
    $montoComision=0,$descuentoCorporativo,$codigoDescCorp,$tarifaNormal,$tarifaAlta,$tarifaBaja,
    $idTipoPagoConsolidador,$centroCosto,$cod1,$cod2,$cod3,$cod4,$observaciones,$estado=1,
    $usuarioCreacion,$fechaCreacion,$usuarioModificacion,$fechaModificacion,$checkFile, $detalleRutas;

    public $ciudadSalida,$ciudadLlegada,$idAerolineaRuta,$vuelo,$clase,$fechaSalida,$horaSalida,$fechaLlegada,
            $horaLlegada,$farebasis, $boletoRutas;

    public $idMedioPago,$idTarjetaCredito,$numeroTarjeta,$monto,$fechaVencimientoTC,$boletoPagos;

    protected $rules = [
        'source' => 'required|in:api,file',
        'ticketNumber' => 'required_if:source,api|string|min:10', // Ajusta la longitud mínima si sabes el formato exacto
        'jsonFile' => 'required_if:source,file',
    ];

    protected $messages = [
        'ticketNumber.required_if' => 'Por favor, ingrese el número de boleto.',
        'ticketNumber.string' => 'El número de boleto debe ser texto.',
        'ticketNumber.min' => 'El número de boleto debe tener al menos :min caracteres.',
        'jsonFile.required_if' => 'Por favor, seleccione un archivo JSON.',
        'jsonFile.file' => 'El archivo seleccionado no es válido.',
        'jsonFile.mimes' => 'El archivo debe ser de tipo JSON.',
        'jsonFile.max' => 'El archivo JSON no debe exceder los 2MB.',
    ];

    public function updatedSource()
    {
        // Limpiar campos y resultados anteriores cuando cambia la fuente
        $this->reset([
            'ticketData',
            'boletoInfo',
            'segments',
            'taxes',
            'errorMessage',
        ]);
        // Quitar errores de validación anteriores
        $this->resetValidation();
        
        if ($this->source === 'api') {
            // Si cambiamos a API, aseguramos que jsonFile esté completamente limpio
            if ($this->jsonFile instanceof UploadedFile) { // Solo si hay un archivo cargado
                $this->jsonFile->delete(); // Eliminar el archivo temporal del disco
            }
            $this->jsonFile = null; // Establecer la propiedad en null
        } elseif ($this->source === 'file') {
            // Si cambiamos a Archivo, aseguramos que ticketNumber esté limpio
            $this->ticketNumber = '';
        }
    }

    /**
     * Busca los datos del boleto usando el servicio API.
     */
    public function buscarBoleto(ContinentalTravelApiService $apiService)
    {
        $this->reset(['ticketData', 'boletoInfo', 'segments', 'taxes', 'errorMessage']); // Limpia resultados anteriores
        $this->isLoading = true; // Inicia el estado de carga
        
        try {
            $this->validate();

            $result = null;
            
            if ($this->source === 'api'){
                $result = $apiService->getTicketData($this->ticketNumber);

            }elseif ($this->source === 'file'){
                $validator = Validator::make(
                    ['jsonFile' => $this->jsonFile], // Datos a validar
                    ['jsonFile' => 'file|mimes:json|max:2048'], // Reglas específicas de archivo
                    [
                        'jsonFile.file' => $this->messages['jsonFile.file'],
                        'jsonFile.mimes' => $this->messages['jsonFile.mimes'],
                        'jsonFile.max' => $this->messages['jsonFile.max'],
                    ]
                ); 

                if ($validator->fails()) {
                    // Si falla la validación específica del archivo, lanza la excepción
                    throw new \Illuminate\Validation\ValidationException($validator);
                }
                // Si la validación pasa, jsonFile es un UploadedFile válido y cumple con tipo/tamaño

                $jsonContent = $this->jsonFile->get(); // Obtener el contenido del archivo
                $decodedJson = json_decode($jsonContent, true); // Decodificar a un array asociativo

                if (json_last_error() === JSON_ERROR_NONE) {
                    // Si la decodificación fue exitosa
                    $result = $decodedJson;
                } else {
                    // Error al decodificar JSON
                    throw new \Exception('El archivo JSON no es válido o está mal formado.');
                }
                
            }
            

            if ($result && !isset($result['error'])) {
                // Si la respuesta no es nula y no contiene un error, significa éxito
                $this->ticketData = $result;
                $this->boletoInfo = [
                    'name' => $result['name'] ?? 'N/A',
                    'foid' => $result['foid'] ?? 'N/A',
                    'ticketnumber' => $result['ticketnumber'] ?? 'N/A',
                    'issuingAirline' => $result['issuingAirline'] ?? 'N/A',
                    'issuingAirlineCode' => $result['issuingAirlineCode'] ?? 'N/A',
                    'issuingAgent' => $result['issuingAgent'] ?? 'N/A',
                    'issueDate' => $result['issueDate'] ?? 'N/A',
                    'iata' => $result['iata'] ?? 'N/A',
                    'bookingReference' => $result['bookingReference'] ?? 'N/A',
                    'customerNBR' => $result['customerNBR'] ?? 'N/A',
                    'nameRef' => $result['nameRef'] ?? 'N/A',
                    'destination' => $result['destination'] ?? 'N/A',
                    'route' => $result['route'] ?? 'N/A',
                    'endorsements' => $result['endorsements'] ?? 'N/A',
                    'farecalculation' => $result['farecalculation'] ?? 'N/A',
                    'formOfPayment' => $result['formOfPayment'] ?? 'N/A',
                    'currency' => $result['currency'] ?? 'N/A',
                    'fare' => $result['fare'] ?? 0,
                    'totalTax' => $result['totalTax'] ?? 0,
                    'total' => $result['total'] ?? 0,
                    'commission' => $result['commission'] ?? 0,
                    'commissionPercentage' => $result['commissionPercentage'] ?? 0,
                ];
                // Obtener Número Boleto
                $this->ticketNumber = $this->boletoInfo['ticketnumber'];

                // Validar Si boleto existe
                $oBoleto = Boleto::where('numeroBoleto',$this->ticketNumber)->first();
                if($oBoleto){
                    session()->flash('error', 'El boleto ya está integrado.');
                    return;
                }

                // Obtener Cliente
                $ruc = $this->boletoInfo['nameRef'];
                $oCliente = Cliente::where('numeroDocumentoIdentidad',$ruc)->first();
                if ($oCliente) {
                    $this->idCliente = $oCliente->id;
                    $this->idTipoFacturacion = $oCliente->tipoFacturacion;
                    $this->idVendedor = $oCliente->vendedor;
                }else{
                    session()->flash('error', 'No se encuentra el cliente');
                    return;
                }

                // Obtener Fecha Emision y tipo de cambio
                $fechaOriginal = $this->boletoInfo['issueDate'];
                $fechaFormat = Carbon::createFromFormat('dMy',$fechaOriginal);
                $this->fechaEmision = $fechaFormat->format('Y-m-d');
                
                $tc = TipoCambio::where('fechaCambio',$this->fechaEmision)->first();
                if($tc){
                    $this->tipoCambio = $tc->montoCambio;
                }else{
                    $this->tipoCambio = 0.00;
                }

                // Obtener Codigo de reserva
                $this->codigoReserva = $this->boletoInfo['bookingReference'];

                //Obtener Ruta
                $this->ruta = $this->boletoInfo['route'];

                // Obtener Aerolinea
                $codigoAerolinea = $this->boletoInfo['issuingAirlineCode'];
                $oAerolinea = Aerolinea::where('codigoIata',$codigoAerolinea)->first();
                $this->idAerolinea = $oAerolinea->id;

                // Obtener Pasajero
                $this->pasajero = $this->boletoInfo['name'];
                $this->pasajero = str_replace("/"," ",$this->pasajero);

                // Obtener destino
                $this->destino =  $this->boletoInfo['destination'];

                // Obtener Tarifa Neta
                $this->tarifaNeta = $this->boletoInfo['fare'];

                // Obtener Comision
                $this->porcentajeComision = $this->boletoInfo['commissionPercentage'];
                $this->montoComision = $this->boletoInfo['commission'];

                // Obtener Forma de pago
                if(str_contains($this->boletoInfo['formOfPayment'],"CA")){
                    $oTc = TarjetaCredito::where('codigo','XX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','009')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(str_contains($this->boletoInfo['formOfPayment'],"VISA")){
                    $oTc = TarjetaCredito::where('codigo','VI')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(str_contains($this->boletoInfo['formOfPayment'],"MASTERCARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(str_contains($this->boletoInfo['formOfPayment'],"MASTER CARD")){
                    $oTc = TarjetaCredito::where('codigo','MA')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(str_contains($this->boletoInfo['formOfPayment'],"DINERS CLUB")){
                    $oTc = TarjetaCredito::where('codigo','DC')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }
                if(str_contains($this->boletoInfo['formOfPayment'],"AMERICAN EXPRESS")){
                    $oTc = TarjetaCredito::where('codigo','AX')->first();
                    $this->idTarjetaCredito = $oTc->id;
                    $oMp = MedioPago::where('codigo','006')->first();
                    $this->idMedioPago = $oMp->id;
                }

                // Asignar arrays de segmentos y taxes
                $this->segments = $result['segments'] ?? [];
                $this->taxes = $result['taxes'] ?? [];

                // Obtener IGV
                foreach ($this->taxes as $tax) {
                    if (isset($tax['description']) && $tax['description'] === 'PE') {
                        $this->igv = $tax['amount'] ?? 0;
                        break; // Salimos del bucle una vez que encontramos la descripción "PE"
                    }
                }

                //Obtener YR
                foreach ($this->taxes as $tax) {
                    if (isset($tax['description']) && $tax['description'] === 'YR') {
                        $this->yr = $tax['amount'] ?? 0;
                        break; // Salimos del bucle una vez que encontramos la descripción "PE"
                    }
                }

                // Obtener otros Impuestos
                foreach ($this->taxes as $tax) {
                    if (isset($tax['amount'])) {
                        $this->otrosImpuestos += $tax['amount'];
                    }
                }
                $this->otrosImpuestos = $this->otrosImpuestos - $this->igv - $this->yr;

                // Actualizar Tarifa neta con YR
                $this->tarifaNeta = $this->tarifaNeta + $this->yr;

                $this->grabarBoleto();

                session()->flash('success', 'Datos del boleto obtenidos exitosamente.');

                return redirect()->route('listaBoletos');

            } elseif (isset($result['error'])) {
                // Si la respuesta contiene un mensaje de error específico de la API
                $this->errorMessage = $result['error'];
                session()->flash('error', $result['error']);
            } else {
                // Si la respuesta es nula (ej. problema con el token, o error genérico)
                $this->errorMessage = 'No se pudieron obtener los datos del boleto. Intente nuevamente más tarde.';
                session()->flash('error', $this->errorMessage);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejo de errores de validación, Livewire los muestra automáticamente
            dd($e);
            $this->errorMessage = 'Por favor, corrija los errores del formulario.';
            // Los errores ya serán visibles por las directivas @error
        } catch (\Exception $e) {
            // Captura cualquier otra excepción inesperada
            Log::error('Error en BuscarBoleto Livewire: ' . $e->getMessage(), ['exception' => $e]);
            $this->errorMessage = 'Ocurrió un error inesperado al buscar el boleto.';
            session()->flash('error', $this->errorMessage);
        } finally {
            $this->isLoading = false; // Finaliza el estado de carga
        }
    }

    public function grabarBoleto(){
        $area = Area::find($this->idArea);
        $boleto = new Boleto();
        $funciones = new Funciones();
        $boleto->numeroBoleto = $this->ticketNumber;
        if($this->checkFile){
            $boleto->numeroFile = $this->numeroFile;
        }else{
            $file = $funciones->generaFile('FILES');
            $boleto->numeroFile = $area->codigo . str_pad($file,7,"0",STR_PAD_LEFT);
        }
        
        $boleto->idCliente = $this->idCliente;
        $boleto->idSolicitante = 0;
        $boleto->fechaEmision = $this->fechaEmision;
        $boleto->idCounter = $this->idCounter;
        $boleto->idTipoFacturacion = $this->idTipoFacturacion;
        $boleto->idTipoDocumento = 6;
        $boleto->idArea = $this->idArea;
        $boleto->idVendedor = $this->idVendedor;
        $boleto->idConsolidador = 9;
        $boleto->codigoReserva = $this->codigoReserva;
        $boleto->fechaReserva = $this->fechaEmision;
        $boleto->idGds = 1;
        $boleto->idTipoTicket = 1;
        $boleto->tipoRuta = $this->tipoRuta;
        $boleto->tipoTarifa = 'NORMAL';
        $boleto->idAerolinea = $this->idAerolinea;
        $boleto->origen = 'BSP';
        $boleto->pasajero = $this->pasajero;
        $boleto->idTipoPasajero = 1;
        $boleto->ruta = $this->ruta;
        $boleto->destino = $this->destino;
        $boleto->tipoCambio = $this->tipoCambio;
        $boleto->idMoneda = 1;
        $boleto->tarifaNeta = $this->tarifaNeta;
        $boleto->inafecto = 0;
        $boleto->igv = $this->igv;
        $boleto->otrosImpuestos = $this->otrosImpuestos;
        $boleto->xm = 0;
        $boleto->total = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boleto->totalOrigen = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boleto->porcentajeComision = $this->porcentajeComision;
        $boleto->montoComision = $this->montoComision;
        $boleto->descuentoCorporativo = 0;
        $boleto->codigoDescCorp = ' ';
        $boleto->tarifaNormal = 0;
        $boleto->tarifaAlta = 0;
        $boleto->tarifaBaja = 0;
        $boleto->idTipoPagoConsolidador = 6;
        $boleto->centroCosto = ' ';
        $boleto->cod1 = ' ';
        $boleto->cod2 = ' ';
        $boleto->cod3 = ' ';
        $boleto->cod4 = ' ';
        $boleto->observaciones = ' ';
        $boleto->detalleRutas = $this->detalleRutas;
        if($this->bookingAgent){
            $boleto->bookingAgent = $this->bookingAgent;
        }else{
            $boleto->bookingAgent = 'AS TRAVEL PERU';
        }
        $boleto->estado = 1;
        $boleto->usuarioCreacion = auth()->user()->id;
        $boleto->save();
        $this->grabarPagosSabre($boleto->id);
        $this->grabarRutas($boleto->id, $this->boletoRutas);
        $this->crearFile($boleto);
    }

    public function grabarPagosSabre($idBoleto){
        $boletoPago = new BoletoPago();
        $boletoPago->idBoleto = $idBoleto;
        $boletoPago->idMedioPago = $this->idMedioPago;
        $boletoPago->idTarjetaCredito = $this->idTarjetaCredito;
        $boletoPago->numeroTarjeta = ' ';
        $boletoPago->monto = $this->tarifaNeta + $this->igv + $this->otrosImpuestos;
        $boletoPago->fechaVencimientoTC = ' ';
        $boletoPago->idEstado = 1;
        $boletoPago->usuarioCreacion = auth()->user()->id;
        $boletoPago->save();
        
    }

    public function grabarRutas($idBoleto){
        foreach ($this->taxes as $tax) {
            if (isset($tax['amount'])) {
                $this->otrosImpuestos += $tax['amount'];
            }
        }
        foreach($this->segments as $item){
            $fs = $item['departureDatetime'];
            $fsFormat = Carbon::parse($fs);
            $fechaSalida = $fsFormat->toDateString();
            $horaSalida = $fsFormat->toTimeString();

            $fl = $item['arrivalDatetime'];
            $flFormat = Carbon::parse($fl);
            $fechaLlegada = $flFormat->toDateString();
            $horaLlegada = $flFormat->toTimeString();

            BoletoRuta::create([
                'idBoleto' => $idBoleto,
                'ciudadSalida' => $item['departureCity'],
                'ciudadLlegada' => $item['arrivalCity'],
                'idAerolinea' => $this->idAerolinea,
                'vuelo' => $item['fligthNumber'],
                'clase' => $item['class'],
                'fechaSalida' => $fechaSalida,
                'horaSalida' => $horaSalida,
                'fechaLlegada' => $fechaLlegada,
                'horaLlegada' => $horaLlegada,
                'farebasis' => $item['farebasis'],
                'idEstado' => 1,
                'usuarioCreacion' => auth()->user()->id
            ]);
        }
    }

    public function crearFile($boleto){
        $fechaActual = Carbon::now();
        if(!$this->checkFile and !$this->numeroFile){
            $file = new File();
            $file->numeroFile = $boleto->numeroFile;
            $file->idArea = $boleto->idArea;
            $file->idCliente = $boleto->idCliente;
            $file->descripcion = $boleto->ruta;
            $file->totalPago = 0;
            $file->totalCobro = 0;
            $file->fechaFile = Carbon::parse($fechaActual)->format("Y-m-d");
            $file->idEstado = 1;
            $file->usuarioCreacion = auth()->user()->id;
            $file->save();

            $fileDetalle = new FileDetalle();
            $fileDetalle->idFile = $file->id;
            $fileDetalle->numeroFile = $file->numeroFile;
            $fileDetalle->idBoleto = $boleto->id;
            $fileDetalle->idEstado = 1;
            $fileDetalle->usuarioCreacion = auth()->user()->id;
            $fileDetalle->save();
        }else{
            $oFile = File::where("numeroFile",$this->numeroFile)->first();
            $fileDetalle = new FileDetalle();
            $fileDetalle->idFile = $oFile->id;
            $fileDetalle->numeroFile = $oFile->numeroFile;
            $fileDetalle->idBoleto = $boleto->id;
            $fileDetalle->idEstado = 1;
            $fileDetalle->usuarioCreacion = auth()->user()->id;
            $fileDetalle->save();
        }
        

    }

    public function render()
    {
        $counters = Counter::all()->sortBy('nombre');
        $areas = Area::all()->sortBy('descripcion');
        return view('livewire.api-boletos.buscar-boleto',compact('counters','areas'));
    }
}