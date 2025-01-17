<div>
    {{-- The best athlete wants his opponent at his best. --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
<hr>
<div class="row align-items-center">
    <div class="col-md-2 mt-2">
        <label for="cboMoneda">Moneda:</label>
        <select name="moneda" style="width: 50%;font-size: 0.8em; display:inline;" id="cboMoneda" wire:model.lazy.defer="idMoneda">
            @foreach ($monedas as $moneda)
                <option value={{$moneda->id}}>{{$moneda->codigo}}</option>
            @endforeach
        </select>
        @error('idMoneda')
            <span class="error">{{$message}}</span>
        @enderror
    </div>
    <div class="col-md-3 mt-2">
        <label for="txtTipoCambio" class="">Tipo Cambio:</label>
        <input type="number" class="uTextBoxInLine" id="txtTipoCambio" wire:model="tipoCambio" disabled>
        @error('tipoCambio')
            <span class="error">{{$message}}</span>
        @enderror
    </div>
    <div class="col-md-3 mt-2">
        <div class="row">
            <div class="col-md-5 ">
                <label for="txtFechaEmision" class="">F. Emisión:</label>
            </div>
            <div class="col-md-7">
                <input type="date" class="" style="width: 100%; display:flex;font-size: 0.8em;font-size: 0.8em;" id="txtFechaEmision" wire:model.lazy="fechaEmision">
                @error('fechaEmision')
                    <span class="error">{{$message}}</span>
                @enderror
            </div>
        </div>
    </div>
    <div class="col-md-2 mt-2">
        <input type="checkbox" id="detraccion" name="detraccion"  wire:model="detraccion">
        <label for="detraccion">Detracción</label>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalGlosa">Emitir CPE</button>
    </div>
    
</div>
<div class="row">
    <div class="col-md-3">
        <select @if(!$chkMedioPago) disabled @endif name="medioPago" style="width: 100%;font-size: 0.8em; display:inline;" id="cboMedioPago" wire:model.lazy.defer="idMedioPagoCambio">
            <option >Seleccione Medio de Pago</option>
            @foreach ($medioPagos as $medioPago)
                <option value={{$medioPago->id}}>{{$medioPago->descripcion}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <input type="checkbox" id="chkMedioPago" name="chkMedioPago"  wire:model="chkMedioPago">
        <label for="chkMedioPago">Cambiar Medio de Pago</label>
    </div>
</div>

<div class="modal" id="modalGlosa" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Glosa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <textarea id="w3review" name="w3review" rows="4" cols="50" wire:model.lazy.defer="glosa" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                
            </textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" wire:click="emitirComprobante" data-bs-dismiss="modal">Emitir</button>
        </div>
      </div>
    </div>
</div>
<hr>

<div class="row">
    <div class="div-filtro row">
        <div class="row">
            <div class="col-md-3">
                <select name="selectedCliente" style="width: 100%; display:block;font-size: 0.8em; height:30px;" class="" id="cboCliente" wire:model.lazy.defer="idCliente">
                    <option value="">-- Seleccione un cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-4">
                                <p style="text-align:right">F. inicio:</p>
                            </div>
                            <div class="col-md-8">
                                <input type="date" wire:model.lazy.defer="startDate" id="startDate">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-4">
                                <p style="text-align:right">F. Final:</p>
                            </div>
                            <div class="col-md-8">
                                <input type="date" wire:model.lazy.defer="endDate" id="endDate">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-success" wire:click="exportar" >Exportar</button>
                    </div>
                </div>
            </div>
        </div> 
    </div>
    <div class="contenedorTablaCC">
        <table class="tabla-listado">
            <thead class="thead-listado">
                <tr>
                    <th scope="col" class="py-1 cursor-pointer">
                        
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('id')">
                        ID 
                        @if ($sort == 'id')
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
                    <th scope="col" class="py-1">
                        Tipo Doc.
                    </th>
                    <th scope="col" class="py-1 cursor-pointer" wire:click="order('pasajero')">
                        Pasajero 
                        @if ($sort == 'pasajero')
                            <i class="fas fa-sort float-right py-1 px-1"></i>
                        @endif
                    </th>
                    <th scope="col" class="py-1">
                        Neto
                    </th>
                    <th scope="col" class="py-1">
                        Inafecto
                    </th>
                    <th scope="col" class="py-1">
                        IGV
                    </th>
                    <th scope="col" class="py-1">
                        Otros Imp.
                    </th>
                    <th scope="col" class="py-1">
                        Total
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
                @if($this->servicios)
                @foreach ($this->servicios as $servicio)
    
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1"><input type="checkbox" name="chkSelect" id="" wire:model.lazy.defer="selectedRows" value="{{ $servicio->id }}"></td>
                    <td class="py-1">{{$servicio->id}}</td>
                    <td class="py-1">{{$servicio->numeroFile}}</td>
                    <td class="py-1">{{$servicio->tcliente->razonSocial}}</td>
                    <td class="py-1">{{$servicio->fechaEmision}}</td>
                    <td class="py-1">{{$servicio->tTipoDocumento->descripcion}}</td>
                    <td class="py-1">{{$servicio->pasajero}}</td>
                    <td class="py-1">{{$servicio->tarifaNeta}}</td>
                    <td class="py-1">{{$servicio->inafecto}}</td>
                    <td class="py-1">{{$servicio->igv}}</td>
                    <td class="py-1">{{$servicio->otrosImpuestos}}</td>
                    <td class="py-1">{{$servicio->total}}</td>
                    <td class="py-1">{{$servicio->tEstado->descripcion}}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
    
</div>
</div>
