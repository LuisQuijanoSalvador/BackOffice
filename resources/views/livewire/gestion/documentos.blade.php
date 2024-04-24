<div>
    {{-- Do your work, then step back. --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <div class="div-filtro">
        <div class="row">
            <div class="col-md-8">
                <input type="text" class="txtFiltro" id="txtFiltro" wire:model.lazy.defer="search" placeholder="Buscar por documento">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-secondary txtFiltro" style="padding-top: 0;" type="button" id="button-addon2"  wire:click='buscarDoc'>Buscar</button>
            </div>
        </div>
        <div>
            <select name="selectedCliente" style="width: 100%; display:block;font-size: 0.9em; height:31px;" class="rounded" id="cboTipoDoc" wire:model.lazy.defer="selectedTipoDocumento">
                <option value="">-- Filtrar por tipo Documento --</option>
                @foreach ($tipoDocumentos as $tipoDocumento)
                    <option value="{{$tipoDocumento->id}}">{{$tipoDocumento->descripcion}}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="selectedCliente" style="width: 60%; display:block;font-size: 0.9em; height:31px;" class="rounded" id="cboCliente" wire:model.lazy.defer="selectedIdCliente">
                <option value="">-- Filtrar por Cliente --</option>
                @foreach ($clientes as $cliente)
                    <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <button type="button" class="btn btn-success" wire:click='exportar'>Exportar</button>
            {{-- <button type="button" class="btn btn-primary rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal">Nuevo</button> --}}
        </div>
        
    </div>
    <div class="row">
        <div class="col-md-1">
            <p style="text-align:right">F. Inicio:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="startDate" id="startDate">
        </div>
        <div class="col-md-1">
            <p style="text-align:right">F. Fin:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="endDate" id="endDate">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div>
    <table class="tabla-listado">
        <thead class="thead-listado">
            <tr>
                <th scope="col" class="py-1 ">
                    Acción
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('id')">
                    ID 
                    @if ($sort == 'id')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('numero')">
                    Documento 
                    @if ($sort == 'numero')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('idTipoDocumento')">
                    Tipo 
                    @if ($sort == 'idTipoDocumento')
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
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                    C. Costo 
                    @if ($sort == 'fechaEmision')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                    COD1
                    @if ($sort == 'fechaEmision')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                    COD2 
                    @if ($sort == 'fechaEmision')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                    COD3
                    @if ($sort == 'fechaEmision')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('fechaEmision')">
                    COD4
                    @if ($sort == 'fechaEmision')
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
            </tr>
        </thead>
        <tbody>
            @if($this->documentos)
                @foreach ($this->documentos as $documento)

                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1">
                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                            <button type="button" class="btn btn-outline-primary mr-2 rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal" wire:click='ver("{{$documento->id}}")'>Ver</button>
                        </div>
                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                            <button @if($documento->idEstado == 2) disabled @endif type="button" class="btn btn-danger mr-2 rounded" data-bs-toggle="modal" data-bs-target="#ModalAnulacion" wire:click='encontrar("{{$documento->id}}")'>Anular</button>
                        </div>
                    </td>
                    <td class="py-1">{{$documento->id}}</td>
                    <td class="py-1">{{$documento->numero}}</td>
                    <td class="py-1">{{$documento->tTipoDocumento->descripcion}}</td>
                    <td class="py-1">{{$documento->tcliente->razonSocial}}</td>
                    <td class="py-1">{{$documento->fechaEmision}}</td>
                    <td class="py-1">@if($documento->tBoleto){{$documento->tBoleto->centroCosto}}@else - @endif</td>
                    <td class="py-1">@if($documento->tBoleto){{$documento->tBoleto->cod1}}@else - @endif</td>
                    <td class="py-1">@if($documento->tBoleto){{$documento->tBoleto->cod2}}@else - @endif</td>
                    <td class="py-1">@if($documento->tBoleto){{$documento->tBoleto->cod3}}@else - @endif</td>
                    <td class="py-1">@if($documento->tBoleto){{$documento->tBoleto->cod4}}@else - @endif</td>
                    <td class="py-1">{{$documento->total}}</td>
                    <td class="py-1">{{$documento->tEstado->descripcion}}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    @if($this->documentos)
        {{$this->documentos->links()}}
    @endif

    <div class="modal fade" id="FormularioModal" wire:ignore.self tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" id="modalxl1">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Documento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click='limpiarControles' aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-2">
                            <label for="cboTipoDocumento" class="form-label">Tipo Documento:</label>
                            <select disabled name="idTipoDocumento" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoDocumento" wire:model="idTipoDocumento">
                                @foreach ($tipoDocumentos as $tipoDocumento)
                                    <option value={{$tipoDocumento->id}}>{{$tipoDocumento->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="txtSerie" class="">Serie: </label>
                            <input disabled type="text" class="uTextBox" id="txtSerie" wire:model.lazy.defer="serie">
                        </div>
                        <div class="col-md-2">
                            <label for="txtNumero" class="">Número: </label>
                            <input disabled type="text" class="uTextBox" id="txtNumero" wire:model.lazy.defer="numero">
                        </div>
                        <div class="col-md-2">
                            <label for="txtFechaEmision" class="form-label">F. Emisión:</label>
                            <input disabled type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaEmision" wire:model="fechaEmision">
                        </div>
                        <div class="col-md-2">
                            <label for="txtFechaVencimiento" class="form-label">F. Vencimiento:</label>
                            <input disabled type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaVencimiento" wire:model="fechaVencimiento">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label for="txtDocRef" class="">Doc. Rereferncia: </label>
                            <input disabled type="text" class="uTextBox" id="txtDocRef" wire:model.lazy.defer="documentoReferencia">
                        </div>
                        <div class="col-md-2">
                            <label for="cboMedioPago" class="form-label">Medio Pago:</label>
                            <select disabled name="idMedioPago" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboMedioPago" wire:model="idMedioPago">
                                @foreach ($medioPagos as $medioPago)
                                    <option value={{$medioPago->id}}>{{$medioPago->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-2"></div>
                        <div class="col-md-2">
                            <label for="cboEstado" class="form-label">Estado:</label>
                            <select disabled name="estado" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboEstado" wire:model="idEstado">
                                @foreach ($estados as $estado)
                                    <option value={{$estado->id}}>{{$estado->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="cboCliente" class="form-label">Cliente:</label>
                            <select disabled name="selectedCliente" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboCliente" wire:model="idCliente">
                                @foreach ($clientes as $cliente)
                                    <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="txtDireccion" class="">Dirección: </label>
                            <input disabled type="text" class="uTextBox" id="txtDireccion" wire:model.lazy.defer="direccionFiscal">
                        </div>
                        <div class="col-md-2">
                            <label for="txtDocIdentidad" class="">Documento Identidad: </label>
                            <input disabled type="text" class="uTextBox" id="txtDocIdentidad" wire:model.lazy.defer="numeroDocumentoIdentidad">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p style="font-weight: bold">Glosa:</p>
                            <textarea disabled name="txtGlosa" id="txtGlosa" cols="50" rows="6" wire:model.lazy.defer="glosa"></textarea>
                        </div>
                        <div class="col-md-6">
                            <table>
                                <tr>
                                    <td>
                                        <label for="txtAfecto" class="">Afecto:</label>
                                    </td>
                                    <td>
                                        <input disabled type="number" class="uTextBoxInLine" id="txtAfecto" wire:model="afecto">
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
                                        <input disabled type="number" class="uTextBoxInLine" id="txtInafecto" wire:model="inafecto">
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
                                        <input disabled type="number" class="uTextBoxInLine" id="txtIgv" wire:model="igv" disabled>
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
                                        <input disabled type="number" class="uTextBoxInLine" id="txtOtrosImpuestos" wire:model="otrosImpuestos">
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
                                        <input disabled type="number" class="uTextBoxInLine" id="txtTotal" wire:model.lazy.defer="total">
                                        @error('total')
                                            <span class="error">{{$message}}</span>
                                        @enderror
                                    </td>
                                </tr>
                                
                            </table> 
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click='limpiarControles'>Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalAnulacion" wire:ignore.self tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Confirmación de Anulacion</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="divConfirmacion">
                        <div class="divDeleteText">
                            <p class="textoConfirmacion">
                                Se va anular el comprobante: {{$comprobante . ' ' . $serie . '-' . $numeroCompleto}}
                            </p>
                            <p class="textoConfirmacion">
                                ¿Está seguro?
                            </p>
                        </div>
                        <div class="divIconWarning">
                            <img src="{{asset('img/warning.png')}}" alt="">
                        </div>
                    </div>
                    <div>
                        <label for="txtMotivo">Motivo de baja:</label>
                        <input type="text" name="txtMotivo" id="txtMotivo" wire:model.laze.defer="motivoBaja" style="text-transform:uppercase; width:100%;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                    </div>
                    @if(session('ErrorAnulacion'))
                        <div class="alert alert-danger">
                            {{ session('ErrorAnulacion') }}
                        </div>
                    @endif
    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click='limpiarControles'>Cancelar</button>
                    {{-- <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @if($idUsuario==0) wire:click='grabar' @else wire:click='actualizar("{{$idUsuario}}")' @endif>Aceptar</button> --}}
                    <button @if ($this->errorAnulacion) disabled @endif type="button" class="btn btn-danger" data-bs-dismiss="modal"  wire:click='anular("{{$idRegistro}}")'>Aceptar</button>
                </div>
            </div>
        </div>
    </div>
</div>
