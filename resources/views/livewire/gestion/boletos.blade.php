<div>
    {{-- Be like water. --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="div-filtro">
        <input type="text" class="txtFiltro rounded" id="txtFiltro" wire:model="search" placeholder="Filtrar por boleto">
        <div>
            <select name="selectedCliente" style="width: 100%; display:block;font-size: 0.9em; height:31px;" class="rounded" id="cboCliente" wire:model="filtroCliente">
                <option value="">-- Filtrar por Cliente --</option>
                @foreach ($clientes as $cliente)
                    <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="button" class="btn btn-success" wire:click='exportar'>Exportar</button>
            <button type="button" class="btn btn-primary rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal">Nuevo</button>
        </div>
        
    </div>
    <div class="contenedorTabla">
        <table class="tabla-listado">
            <thead class="thead-listado">
                <tr>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('id')">
                        ID 
                        @if ($sort == 'id')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('numeroBoleto')">
                        Boleto 
                        @if ($sort == 'numeroBoleto')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('numeroFile')">
                        File 
                        @if ($sort == 'numeroFile')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('idCliente')">
                        Cliente
                        @if ($sort == 'idCliente')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                        F. Emisión 
                        @if ($sort == 'fechaEmision')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('pasajero')">
                        Pasajero 
                        @if ($sort == 'pasajero')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('idDocumento')">
                        Documento 
                        @if ($sort == 'idDocumento')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('tarifaNeta')">
                        Afecto 
                        @if ($sort == 'tarifaNeta')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('inafecto')">
                        Inafecto 
                        @if ($sort == 'inafecto')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('igv')">
                        IGV 
                        @if ($sort == 'igv')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('otrosImpuestos')">
                        Otros Imp. 
                        @if ($sort == 'otrosImpuestos')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('total')">
                        Total 
                        @if ($sort == 'total')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('estado')">
                        Estado 
                        @if ($sort == 'estado')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        Acción
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($boletos as $boleto)
    
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1">{{$boleto->id}}</td>
                    <td class="py-1">{{$boleto->numeroBoleto}}</td>
                    <td class="py-1">{{$boleto->numeroFile}}</td>
                    <td class="py-1">{{$boleto->tcliente->razonSocial}}</td>
                    <td class="py-1">{{$boleto->fechaEmision}}</td>
                    <td class="py-1">{{$boleto->pasajero}}</td>
                    <td class="py-1">@if($boleto->tDocumento){{$boleto->tDocumento->serie . '-' . str_pad($boleto->tDocumento->numero,8,"0",STR_PAD_LEFT)}}@else - @endif</td>
                    <td class="py-1 text-right">{{$boleto->tarifaNeta}}</td>
                    <td class="py-1 text-right">{{$boleto->inafecto}}</td>
                    <td class="py-1 text-right">{{$boleto->igv}}</td>
                    <td class="py-1 text-right">{{$boleto->otrosImpuestos}}</td>
                    <td class="py-1 text-right">{{$boleto->total}}</td>
                    <td class="py-1">{{$boleto->tEstado->descripcion}}</td>
                    <td class="py-1">
                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                            <button type="button" class="btn btn-outline-primary mr-2 rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal" wire:click='editar("{{$boleto->id}}")'>Editar</button>
                            <button type="button" class="btn btn-danger rounded" data-bs-toggle="modal" data-bs-target="#ModalEliminacion" wire:click='encontrar("{{$boleto->id}}")'>Eliminar</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{$boletos->links()}}

    {{-- Modal para Insertar y Actualizar --}}
    @include('components.modalheaderxl')
    @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    <form>
        <div class="row">
            @if ($idRegistro!=0 and !$idFee)
                <div class="col-md-1">
                    <label for="txtTarifaFee" class="">Neto: </label>
                    <input type="number" class="uTextBox" id="txtTarifaFee" wire:model.lazy.defer="tarifaFee">
                    @error('tarifaFee')
                        <span class="error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="cboTipoDocumento" class="form-label">Tipo Documento:</label>
                    <select name="idTipoDocumento" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoDocumento" wire:model="tipoDocFee">
                        @foreach ($tipoDocumentos as $tipoDocumento)
                            <option value={{$tipoDocumento->id}}>{{$tipoDocumento->descripcion}}</option>
                        @endforeach
                    </select>
                    @error('tipoDocFee')
                        <span class="error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <br>
                    <button type="button" class="btn btn-success" wire:click='generarFee'>Generar Fee</button>
                </div>  
            @endif
            @if ($idRegistro!=0)
                <div class="col-md-1">
                    <br>
                    <button type="button" class="btn btn-success" wire:click='clonarBoleto'>Clonar</button>
                </div>
            @endif
        </div>
    </form>
    <hr width="100%"> 
    <div class="contenedor">
        <form>
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                          Datos del Boleto
                        </button>
                      </h2>
                      <div id="flush-collapseOne" class="accordion-collapse">
                        <div class="seccion1">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="txtBoleto" class="">Boleto:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" maxlength="10" id="txtBoleto" wire:model.lazy="numeroBoleto" onkeypress="return valideKey(event);">
                                    @error('numeroBoleto')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-1">
                                            <input @if($idDocumento) disabled @endif type="checkbox" class=" mt-16" name="chkFile" id="chkFile" wire:model="checkFile">
                                        </div>
                                        <div class="col-md-11">
                                            <label for="txtFile" class="form-label">File:</label>
                                            <input type="text" class="uTextBox" maxlength="10" id="txtFile" wire:model.lazy="numeroFile" @if (!$checkFile) disabled @endif style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cboCliente" class="form-label">Cliente:</label>
                                    <select @if($idDocumento) disabled @endif name="selectedCliente" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboCliente" wire:model="selectedCliente">
                                        <option value="">-- Seleccione una opción --</option>
                                        @foreach ($clientes as $cliente)
                                            <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCliente')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                               
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="cboSolicitante" class="form-label">Solicitante:</label>
                                    <select @if($idDocumento) disabled @endif name="selectedSolicitante" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboSolicitante" wire:model="selectedSolicitante">
                                        <option value="0">--</option>
                                        @foreach ($solicitantes as $solicitante)
                                            <option value={{$solicitante->id}}>{{$solicitante->nombres}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedSolicitante')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="cboCounter" class="form-label">Counter:</label>
                                    <select @if($idDocumento) disabled @endif name="idCounter" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboCounter" wire:model="idCounter">
                                        @foreach ($counters as $counter)
                                            <option value={{$counter->id}}>{{$counter->nombre}}</option>
                                        @endforeach
                                    </select>
                                    @error('idCounter')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="cboVendedor" class="form-label">Vendedor:</label>
                                    <select @if($idDocumento) disabled @endif name="idVendedor" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboVendedor" wire:model="idVendedor">
                                        @foreach ($vendedors as $vendedor)
                                            <option value={{$vendedor->id}}>{{$vendedor->nombre}}</option>
                                        @endforeach
                                    </select>
                                    @error('idVendedor')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="cboArea" class="form-label">Área:</label>
                                    <select @if($idDocumento) disabled @endif name="idArea" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboArea" wire:model="idArea">
                                        @foreach ($areas as $area)
                                            <option value={{$area->id}}>{{$area->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idArea')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="cboTipoFacturacion" class="form-label">Tipo Fact:</label>
                                    <select @if($idDocumento) disabled @endif name="idTipoFacturacion" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoFacturacion" wire:model="idTipoFacturacion">
                                        @foreach ($tipoFacturacions as $tipoFacturacion)
                                            <option value={{$tipoFacturacion->id}}>{{$tipoFacturacion->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idTipoFacturacion')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="txtFechaEmision" class="form-label">F. Emisión:</label>
                                    <input @if($idDocumento) disabled @endif type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaEmision" wire:model="fechaEmision">
                                    @error('fechaEmision')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="cboConsolidador" class="form-label">Consolidador:</label>
                                    <select @if($idDocumento) disabled @endif name="idConsolidador" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboConsolidador" wire:model="idConsolidador">
                                        @foreach ($consolidadors as $consolidador)
                                            <option value={{$consolidador->id}}>{{$consolidador->razonSocial}}</option>
                                        @endforeach
                                    </select>
                                    @error('idConsolidador')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="txtCentroCosto" class="form-label">Centro Costo:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" id="txtCentroCosto" wire:model.lazy="centroCosto" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('centroCosto')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtCod1" class="form-label">COD 1:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" id="txtCod1" wire:model.lazy="cod1" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('cod1')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtCod2" class="form-label">COD 2:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" id="txtCod2" wire:model.lazy="cod2" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('cod2')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtCod3" class="form-label">COD 3:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" id="txtCod3" wire:model.lazy="cod3" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('cod3')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtCod4" class="form-label">COD 4:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" id="txtCod4" wire:model.lazy="cod4" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('cod4')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                            
                        <hr class="">
                        <div class="seccion2">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="txtCodigoReserva" class="form-label">Cod. Reserva:</label>
                                    <input @if($idDocumento) disabled @endif type="text" maxlength="6" class="uTextBox" style="text-transform:uppercase;" id="txtCodigoReserva" wire:model="codigoReserva" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('codigoReserva')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtFechaReserva" class="form-label">F. Reserva:</label>
                                    <input @if($idDocumento) disabled @endif type="date" class="" style="width: 100%; display:block;font-size: 0.8em;" id="txtFechaReserva" wire:model="fechaReserva">
                                    @error('fechaReserva')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboGds" class="form-label">GDS:</label>
                                    <select @if($idDocumento) disabled @endif name="idGds" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboGds" wire:model="idGds">
                                        @foreach ($gdss as $gds)
                                            <option value={{$gds->id}}>{{$gds->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idGds')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboTipoTicket" class="form-label">Tipo:</label>
                                    <select @if($idDocumento) disabled @endif name="idTipoTicket" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoTicket" wire:model="idTipoTicket">
                                        @foreach ($tipoTickets as $tipoTicket)
                                            <option value={{$tipoTicket->id}}>{{$tipoTicket->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idTipoTicket')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboTipoRuta" class="form-label">Nac./Int.:</label>
                                    <select @if($idDocumento) disabled @endif name="tipoRuta" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoRuta" wire:model="tipoRuta">
                                        <option value="INTERNACIONAL">INTERNACIONAL</option>
                                        <option value="NACIONAL">NACIONAL</option>
                                    </select>
                                    @error('tipoRuta')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboTipoTarifa" class="form-label">Tipo Tarifa:</label>
                                    <select @if($idDocumento) disabled @endif name="tipoTarifa" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoTarifa" wire:model="tipoTarifa">
                                        <option value="BULK">BULK</option>
                                        <option value="NORMAL">NORMAL</option>
                                    </select>
                                    @error('tipoTarifa')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="cboAerolinea" class="form-label">Aerolinea:</label>
                                    <select @if($idDocumento) disabled @endif name="idAerolinea" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboAerolinea" wire:model="idAerolinea">
                                        @foreach ($aerolineas as $aerolinea)
                                            <option value={{$aerolinea->id}}>{{$aerolinea->razonSocial}}</option>
                                        @endforeach
                                    </select>
                                    @error('idAerolinea')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboOrigen" class="form-label">Origen:</label>
                                    <select @if($idDocumento) disabled @endif name="origen" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboOrigen" wire:model="origen">
                                        <option value="BSP">BSP</option>
                                        <option value="LA">LA</option>
                                    </select>
                                    @error('origen')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtPasajero" class="form-label">Pasajero:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" style="text-transform:uppercase;" id="txtPasajero" wire:model="pasajero" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('pasajero')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboTipoPasajero" class="form-label">Tipo Pax:</label>
                                    <select @if($idDocumento) disabled @endif name="idTipoPasajero" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoPasajero" wire:model="idTipoPasajero">
                                        @foreach ($tipoPasajeros as $tipoPasajero)
                                            <option value={{$tipoPasajero->id}}>{{$tipoPasajero->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idTipoPasajero')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtRuta" class="form-label">Ruta:</label>
                                    <input type="text" class="uTextBox" style="text-transform:uppercase;" disabled id="txtRuta" wire:model="ruta" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('ruta')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="txtDestino" class="form-label">Destino:</label>
                                    <input type="text" class="uTextBox" style="text-transform:uppercase;" disabled id="txtDestino" wire:model="destino" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('destino')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                            
                        <hr class="">
                        <div class="seccion1">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="txtDocumento" class="form-label">Documento:</label>
                                    <input type="text" class="uTextBox" id="txtDocumento" wire:model.lazy.defer="numDoc" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label for="cboTipoDocumento" class="form-label">Tipo Documento:</label>
                                    <select @if($idDocumento) disabled @endif name="idTipoDocumento" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoDocumento" wire:model="idTipoDocumento">
                                        @foreach ($tipoDocumentos as $tipoDocumento)
                                            <option value={{$tipoDocumento->id}}>{{$tipoDocumento->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('idTipoDocumento')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label for="cboEstado" class="form-label">Estado:</label>
                                    <select @if($idDocumento) disabled @endif name="estado" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboEstado" wire:model="estado">
                                        @foreach ($estados as $estado)
                                            <option value={{$estado->id}}>{{$estado->descripcion}}</option>
                                        @endforeach
                                    </select>
                                    @error('estado')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="txtObservaciones" class="form-label">Observaciones:</label>
                                    <input @if($idDocumento) disabled @endif type="text" class="uTextBox" style="text-transform:uppercase;" id="txtObservaciones" wire:model="observaciones" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                    @error('observaciones')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <hr class="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="cboUsuarioCreacion" class="form-label">Creado Por:</label>
                                    <select name="usuarioCreacion" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboUsuarioCreacion" wire:model="usuarioCreacion" disabled>
                                        <option value="">--  --</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value={{$usuario->id}}>{{$usuario->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="txtFechaCreacion" class="form-label">Fecha:</label>
                                    <input type="date" class="" style="width: 100%; display:block;font-size: 0.8em;" id="txtFechaCreacion" wire:model="fechaCreacion" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label for="cboUsuarioModificacion" class="form-label">Modificado Por:</label>
                                    <select name="usuarioModificacion" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboUsuarioModificacion" wire:model="usuarioModificacion" disabled>
                                        <option value="">--  --</option>
                                        @foreach ($usuarios as $usuario)
                                            <option value={{$usuario->id}}>{{$usuario->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="txtFechaModificacion" class="form-label">Fecha:</label>
                                    <input type="date" class="" style="width: 100%; display:block;font-size: 0.8em;" id="txtFechaModificacion" wire:model="fechaModificacion" disabled>
                                </div>
                            </div>
                        </div>
                      </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseOne">
                            Tarifas
                        </button>
                    </h2>
                    <div id="flush-collapseTwo" class="accordion-collapse">
                        <div class="seccion1">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="cboMoneda">Moneda:</label>
                                    <select @if($idDocumento) disabled @endif name="moneda" style="width: 50%;font-size: 0.8em; display:inline;" id="cboMoneda" wire:model.lazy.defer="idMoneda">
                                        <option>==Seleccione una opción==</option>
                                        @foreach ($monedas as $moneda)
                                            <option value={{$moneda->id}}>{{$moneda->codigo}}</option>
                                        @endforeach
                                    </select>
                                    @error('idMoneda')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="txtTipoCambio" class="">Tipo Cambio:</label>
                                    <input type="number" class="uTextBoxInLine" id="txtTipoCambio" wire:model.lazy.defer="tipoCambio" disabled>
                                    @error('tipoCambio')
                                        <span class="error">{{$message}}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <table>
                                        <tr>
                                            <td>
                                                <label for="txtTarifaNeta" class="">Tarifa Neta:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine" id="txtTarifaNeta" wire:model="tarifaNeta">
                                                @error('tarifaNeta')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                            
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtInafecto" class="">Inafecto:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine" id="txtInafecto" wire:model="inafecto">
                                                @error('inafecto')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtIgv" class="">IGV:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine" id="txtIgv" wire:model="igv" disabled>
                                                @error('igv')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtOtrosImpuestos" class="">Otros Imp.:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine" id="txtOtrosImpuestos" wire:model="otrosImpuestos">
                                                @error('otrosImpuestos')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>
                                                <label for="txtTotal" class="">Total venta:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine" id="txtTotal" wire:model.lazy.defer="total">
                                                @error('total')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        
                                    </table> 
                                </div>
                                <div class="col-md-4">
                                    <table>
                                        <tr>
                                            <td>
                                                <label for="txtXm" class="">XM:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine2" id="txtXm" wire:model="xm">
                                                @error('xm')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtTotalOrigen" class="">Total Pagado:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine2" id="txtTotalOrigen" wire:model.lazy.defer="totalOrigen">
                                                @error('totalOrigen')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtFormaPago" class="">Forma Pago:</label>
                                            </td>
                                            <td>
                                                <select @if($idDocumento) disabled @endif name="idTipoPagoConsolidador" style="width: 60%;font-size: 0.8em; display:inline;" id="cboFPago" wire:model.lazy.defer="idTipoPagoConsolidador">
                                                    <option>==Seleccione una opción==</option>
                                                    @foreach ($medioPagos as $medioPago)
                                                        <option value={{$medioPago->id}}>{{$medioPago->descripcion}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label for="txtComision" class="">Comisión:</label>
                                            </td>
                                            <td>
                                                <input @if($idDocumento) disabled @endif type="number" class="uTextBoxInLine2" id="txtComision" wire:model.lazy.defer="montoComision">
                                                @error('montoComision')
                                                    <span class="error">{{$message}}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($idRegistro==0)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseOne">
                                Rutas
                            </button>
                        </h2>

                        <div id="flush-collapseThree" class="accordion-collapse">
                            <div class="seccion1">
                                <div class="row">
                                    
                                    <div class="col-md-1">
                                        <label for="txtCiudadSalida" class="">Salida:</label>
                                        <input type="text" class="uTextBox" id="txtCiudadSalida" wire:model.lazy.defer="ciudadSalida" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('ciudadSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtCiudadLlegada" class="">Llegada:</label>
                                        <input required type="text" class="uTextBox" id="txtCiudadLlegada" wire:model.lazy.defer="ciudadLlegada" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('ciudadLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="cboAerolineaRuta" class="form-label">Aerolinea:</label>
                                        <select required name="idAerolineaRuta" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboAerolineaRuta" wire:model.defer="idAerolineaRuta">
                                            <option>--</option>
                                            @foreach ($aerolineas as $aerolinea)
                                                <option value="{{$aerolinea->id}}">{{$aerolinea->razonSocial}}</option>
                                            @endforeach
                                        </select>
                                        @error('idAerolineaRuta')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtVuelo" class="">Vuelo:</label>
                                        <input required type="text" class="uTextBox" id="txtVuelo" wire:model.lazy.defer="vuelo" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('vuelo')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtClase" class="">Clase:</label>
                                        <input required type="text" class="uTextBox" id="txtClase" wire:model.lazy.defer="clase" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('clase')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFechaSalida" class="form-label">F. Salida:</label>
                                        <input required type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaSalida" wire:model.lazy.defer="fechaSalida">
                                        @error('fechaSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtHoraSalida" class="">Hora:</label>
                                        <input required type="text" class="uTextBox" maxlength="4" id="txtHoraSalida" wire:model.lazy.defer="horaSalida" onkeypress="return valideKey(event);">
                                        @error('horaSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFechaLlegada" class="form-label">F. Llegada:</label>
                                        <input required type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaLlegada" wire:model.lazy.defer="fechaLlegada">
                                        @error('fechaLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtHoraLlegada" class="">Hora:</label>
                                        <input required type="text" class="uTextBox" maxlength="4" id="txtHoraLlegada" wire:model.lazy.defer="horaLlegada" onkeypress="return valideKey(event);">
                                        @error('horaLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFarebasis" class="">Farebasis:</label>
                                        <input type="text" class="uTextBox" id="txtFarebasis" wire:model.lazy.defer="farebasis" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" style="margin-top: 20px" wire:click="addRuta('1')">
                                            <img src="{{ asset('img/add.png')}}" width="30px" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="seccion2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="tabla-listado">
                                            <thead class="thead-listado">
                                                <tr>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Ciudad Salida 
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Ciudad Llegada
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Aerolinea
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer" >
                                                        Vuelo
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Clase
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Salida
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Hora
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Llegada
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Hora
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Farebasis
                                                    </th>
                                                    <th scope="col" class="py-1 thAccion">
                                                        Acción
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($boletoRutas)
                                                    @foreach ($boletoRutas as $boletoRuta)
                                        
                                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                        <td class="py-1">{{$boletoRuta['ciudadSalida']}}</td>
                                                        <td class="py-1">{{$boletoRuta['ciudadLlegada']}}</td>
                                                        <td class="py-1">{{$boletoRuta['aerolinea']}}</td>
                                                        <td class="py-1">{{$boletoRuta['vuelo']}}</td>
                                                        <td class="py-1">{{$boletoRuta['clase']}}</td>
                                                        <td class="py-1">{{$boletoRuta['fechaSalida']}}</td>
                                                        <td class="py-1">{{$boletoRuta['horaSalida']}}</td>
                                                        <td class="py-1">{{$boletoRuta['fechaLlegada']}}</td>
                                                        <td class="py-1">{{$boletoRuta['horaLlegada']}}</td>
                                                        <td class="py-1">{{$boletoRuta['farebasis']}}</td>

                                                        <td class="py-1">
                                                            <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                                                                <button type="button" style="margin-top: 0px" wire:click="quitarRuta({{$loop->index}})">
                                                                    <img src="{{ asset('img/delete.png')}}" width="20px" style="margin-bottom: 3px">
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                @else
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseOne">
                                Rutas
                            </button>
                        </h2>

                        <div id="flush-collapseThree" class="accordion-collapse">
                            <div class="seccion1">
                                <div class="row">
                                    
                                    <div class="col-md-1">
                                        <label for="txtCiudadSalida" class="">Salida:</label>
                                        <input type="text" class="uTextBox" id="txtCiudadSalida" wire:model.lazy.defer="ciudadSalida" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('ciudadSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtCiudadLlegada" class="">Llegada:</label>
                                        <input required type="text" class="uTextBox" id="txtCiudadLlegada" wire:model.lazy.defer="ciudadLlegada" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('ciudadLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="cboAerolineaRuta" class="form-label">Aerolinea:</label>
                                        <select required name="idAerolineaRuta" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboAerolineaRuta" wire:model.defer="idAerolineaRuta">
                                            <option>--</option>
                                            @foreach ($aerolineas as $aerolinea)
                                                <option value="{{$aerolinea->id}}">{{$aerolinea->razonSocial}}</option>
                                            @endforeach
                                        </select>
                                        @error('idAerolineaRuta')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtVuelo" class="">Vuelo:</label>
                                        <input required type="text" class="uTextBox" id="txtVuelo" wire:model.lazy.defer="vuelo" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('vuelo')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtClase" class="">Clase:</label>
                                        <input required type="text" class="uTextBox" id="txtClase" wire:model.lazy.defer="clase" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('clase')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFechaSalida" class="form-label">F. Salida:</label>
                                        <input required type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaSalida" wire:model.lazy.defer="fechaSalida">
                                        @error('fechaSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtHoraSalida" class="">Hora:</label>
                                        <input required type="text" class="uTextBox" maxlength="4" id="txtHoraSalida" wire:model.lazy.defer="horaSalida" onkeypress="return valideKey(event);">
                                        @error('horaSalida')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFechaLlegada" class="form-label">F. Llegada:</label>
                                        <input required type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaLlegada" wire:model.lazy.defer="fechaLlegada">
                                        @error('fechaLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtHoraLlegada" class="">Hora:</label>
                                        <input required type="text" class="uTextBox" maxlength="4" id="txtHoraLlegada" wire:model.lazy.defer="horaLlegada" onkeypress="return valideKey(event);">
                                        @error('horaLlegada')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <label for="txtFarebasis" class="">Farebasis:</label>
                                        <input type="text" class="uTextBox" id="txtFarebasis" wire:model.lazy.defer="farebasis" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" style="margin-top: 20px" wire:click="addRutaEdit">
                                            <img src="{{ asset('img/add.png')}}" width="30px" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="seccion2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="tabla-listado">
                                            <thead class="thead-listado">
                                                <tr>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Ciudad Salida 
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Ciudad Llegada
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Aerolinea
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer" >
                                                        Vuelo
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Clase
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Salida
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Hora
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Llegada
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Hora
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Farebasis
                                                    </th>
                                                    <th scope="col" class="py-1 thAccion">
                                                        Acción
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($this->boletoRutasEdit as $boletoRuta)
                                    
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="py-1">{{$boletoRuta->ciudadSalida}}</td>
                                                    <td class="py-1">{{$boletoRuta->ciudadLlegada}}</td>
                                                    <td class="py-1">{{$boletoRuta->tAerolinea->razonSocial}}</td>
                                                    <td class="py-1">{{$boletoRuta->vuelo}}</td>
                                                    <td class="py-1">{{$boletoRuta->clase}}</td>
                                                    <td class="py-1">{{$boletoRuta->fechaSalida}}</td>
                                                    <td class="py-1">{{$boletoRuta->horaSalida}}</td>
                                                    <td class="py-1">{{$boletoRuta->fechaLlegada}}</td>
                                                    <td class="py-1">{{$boletoRuta->horaLlegada}}</td>
                                                    <td class="py-1">{{$boletoRuta->farebasis}}</td>

                                                    <td class="py-1">
                                                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                                                            <button type="button" style="margin-top: 0px" wire:click='quitarRutaEdit("{{$boletoRuta->id}}")'>
                                                                <img src="{{ asset('img/delete.png')}}" width="20px" style="margin-bottom: 3px">
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                @endif

                @if($idRegistro==0)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseOne">
                                Pagos
                            </button>
                        </h2>
                        <div id="flush-collapseFour" class="accordion-collapse">
                            <div class="seccion1">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="cboTipoPago" class="form-label">Medio de Pago:</label>
                                        <select required name="idMedioPago" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoPago" wire:model.defer="idMedioPago">
                                            <option value=''>--</option>
                                            @foreach ($medioPagos as $medioPago)
                                                <option value="{{$medioPago->id}}">{{$medioPago->descripcion}}</option>
                                            @endforeach
                                        </select>
                                        @error('idMedioPago')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cboTarjeta" class="form-label">Tarjeta:</label>
                                        <select required name="idTarjetaCredito" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTarjeta" wire:model.defer="idTarjetaCredito">
                                            <option value=''>--</option>
                                            @foreach ($tarjetaCreditos as $tarjetaCredito)
                                                <option value="{{$tarjetaCredito->id}}">{{$tarjetaCredito->descripcion}}</option>
                                            @endforeach
                                        </select>
                                        @error('idTarjetaCredito')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtNumeroTarjeta" class="">Número Tarjeta:</label>
                                        <input type="text" class="uTextBox" maxlength="19"  id="txtNumeroTarjeta" wire:model.lazy.defer="numeroTarjeta" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('numeroTarjeta')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtMonto" class="">Monto:</label>
                                        <input required type="number" step="0.01" class="uTextBox"  id="txtMonto" wire:model.lazy.defer="monto">
                                        @error('monto')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtFechaVencimiento" class="">F. Vencimiento:</label>
                                        <input type="text" class="uTextBox" maxlength="5"  id="txtFechaVencimiento" wire:model.lazy.defer="fechaVencimientoTC" pattern="^(0[1-9]|1[0-2])\/\d{2}$" placeholder="MM/AA" onkeypress="return valideKey(event);">
                                        @error('fechaVencimientoTC')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" style="margin-top: 20px" wire:click="addPago()">
                                            <img src="{{ asset('img/add.png')}}" width="30px" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="seccion2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="tabla-listado">
                                            <thead class="thead-listado">
                                                <tr>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Medio Pago 
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Tipo Tarjeta
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Numero Tarjeta
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer" >
                                                        Monto
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Vencimiento
                                                    </th>
                                                    <th scope="col" class="py-1 thAccion">
                                                        Acción
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($boletoPagos)
                                                @foreach ($boletoPagos as $boletoPago)
                                    
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="py-1">{{$boletoPago['medioPago']}}</td>
                                                    <td class="py-1">{{$boletoPago['tarjetaCredito']}}</td>
                                                    <td class="py-1">{{$boletoPago['numeroTarjeta']}}</td>
                                                    <td class="py-1">{{$boletoPago['monto']}}</td>
                                                    <td class="py-1">{{$boletoPago['fechaVencimientoTC']}}</td>

                                                    <td class="py-1">
                                                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                                                            <button type="button" style="margin-top: 0px" wire:click="quitarPago({{$loop->index}})">
                                                                <img src="{{ asset('img/delete.png')}}" width="20px" style="margin-bottom: 3px">
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFour" aria-expanded="false" aria-controls="flush-collapseOne">
                                Pagos
                            </button>
                        </h2>
                        <div id="flush-collapseFour" class="accordion-collapse">
                            <div class="seccion1">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="cboTipoPago" class="form-label">Medio de Pago:</label>
                                        <select required name="idMedioPago" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoPago" wire:model.defer="idMedioPago">
                                            <option value=''>--</option>
                                            @foreach ($medioPagos as $medioPago)
                                                <option value="{{$medioPago->id}}">{{$medioPago->descripcion}}</option>
                                            @endforeach
                                        </select>
                                        @error('idMedioPago')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cboTarjeta" class="form-label">Tarjeta:</label>
                                        <select required name="idTarjetaCredito" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTarjeta" wire:model.defer="idTarjetaCredito">
                                            <option value=''>--</option>
                                            @foreach ($tarjetaCreditos as $tarjetaCredito)
                                                <option value="{{$tarjetaCredito->id}}">{{$tarjetaCredito->descripcion}}</option>
                                            @endforeach
                                        </select>
                                        @error('idTarjetaCredito')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtNumeroTarjeta" class="">Número Tarjeta:</label>
                                        <input type="text" class="uTextBox" maxlength="19"  id="txtNumeroTarjeta" wire:model.lazy.defer="numeroTarjeta" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                                        @error('numeroTarjeta')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtMonto" class="">Monto:</label>
                                        <input required type="number" step="0.01" class="uTextBox"  id="txtMonto" wire:model.lazy.defer="monto">
                                        @error('monto')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="txtFechaVencimiento" class="">F. Vencimiento:</label>
                                        <input type="text" class="uTextBox" maxlength="5"  id="txtFechaVencimiento" wire:model.lazy.defer="fechaVencimientoTC" pattern="^(0[1-9]|1[0-2])\/\d{2}$" placeholder="MM/AA" onkeypress="return valideKey(event);">
                                        @error('fechaVencimientoTC')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" style="margin-top: 20px" wire:click="addPagoEdit">
                                            <img src="{{ asset('img/add.png')}}" width="30px" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="seccion2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="tabla-listado">
                                            <thead class="thead-listado">
                                                <tr>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Medio Pago 
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Tipo Tarjeta
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        Numero Tarjeta
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer" >
                                                        Monto
                                                    </th>
                                                    <th scope="col" class="py-1 cursor-pointer">
                                                        F. Vencimiento
                                                    </th>
                                                    <th scope="col" class="py-1 thAccion">
                                                        Acción
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($this->boletoPagosEdit as $boletoPago)
                                    
                                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                    <td class="py-1">{{$boletoPago->tMedioPago->descripcion}}</td>
                                                    <td class="py-1">{{$boletoPago->tTarjetaCredito->descripcion}}</td>
                                                    <td class="py-1">{{$boletoPago->numeroTarjeta}}</td>
                                                    <td class="py-1">{{$boletoPago->monto}}</td>
                                                    <td class="py-1">{{$boletoPago->fechaVencimientoTC}}</td>

                                                    <td class="py-1">
                                                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                                                            <button type="button" style="margin-top: 0px" wire:click='quitarPagoEdit("{{$boletoPago->id}}")'>
                                                                <img src="{{ asset('img/delete.png')}}" width="20px" style="margin-bottom: 3px">
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
   
        </form>
    </div>

    @include('components.modalfooter')
    
    {{-- Modal para Eliminar --}}
    @include('components.modaldelete')


</div>
