<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-3">
            <label for="txtFechaEmision" class="form-label">F. Emisión:</label>
            <input type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaEmision" wire:model="fechaEmision">
        </div>
        <div class="col-md-3">
            <label for="txtTipoCambio" class="">Tipo Cambio:</label>
            <input type="number" class="uTextBox" id="txtTipoCambio" wire:model.lazy.defer="tipoCambio" disabled>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <label for="cboTipoDocumento" class="form-label">Tipo Documento:</label>
            <select name="idTipoDocumento" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTipoDocumento" wire:model.lazy.defer="idTipoDocumento">
                @foreach ($tipoDocumentos as $tipoDocumento)
                    <option value={{$tipoDocumento->id}}>{{$tipoDocumento->descripcion}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="txtNumeroDoc" class="form-label">Numero:</label>
            <input type="text" class="uTextBox"  id="txtNumeroDoc" wire:model.lazy.defer="numeroDocumento" onkeypress="return valideKey(event);">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-success mt-3" wire:click='buscar'>Buscar</button>
        </div>
    </div>
    <hr>
    <div class="row">
        <table class="tabla-listado">
            <thead class="thead-listado">
                <tr>
                    <th scope="col" class="py-1">
                        ID 
                    </th>
                    <th scope="col" class="py-1">
                        Documento
                    </th>
                    <th scope="col" class="py-1">
                        Tipo 
                    </th>
                    <th scope="col" class="py-1">
                        Cliente
                    </th>
                    <th scope="col" class="py-1">
                        F. Emisión 
                    </th>
                    <th scope="col" class="py-1">
                        Afecto
                    </th>
                    <th scope="col" class="py-1">
                        Inafecto
                    </th>
                    <th scope="col" class="py-1">
                        IGV
                    </th>
                    <th scope="col" class="py-1">
                        Total
                    </th>
                    <th scope="col" class="py-1">
                        Estado
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($this->documentos)
                    @foreach ($this->documentos as $documento)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{$documento->id}}</td>
                            <td class="py-1">{{$documento->numero}}</td>
                            <td class="py-1">{{$documento->tTipoDocumento->descripcion}}</td>
                            <td class="py-1">{{$documento->tcliente->razonSocial}}</td>
                            <td class="py-1">{{\Carbon\Carbon::parse($documento->fechaEmision)->format('d-m-Y')}}</td>
                            <td class="py-1">{{$documento->afecto}}</td>
                            <td class="py-1">{{$documento->inafecto}}</td>
                            <td class="py-1">{{$documento->igv}}</td>
                            <td class="py-1">{{$documento->total}}</td>
                            <td class="py-1">{{$documento->tEstado->descripcion}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <label for="cboMotivo" class="form-label">Motivo:</label>
            <select name="idMotivo" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboMotivo" wire:model.lazy.defer="motivo">
                @foreach ($motivos as $motivo)
                    <option value={{$motivo->id}}>{{$motivo->descripcion}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="txtMonto" class="form-label">Monto:</label>
            <input type="number" class="uTextBox"  id="txtMonto" wire:model.lazy.defer="monto">
        </div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <label for="txtGlosa" class="form-label">Glosa:</label>
            <textarea id="txtGlosa" name="txtGlosa" rows="4" cols="50" wire:model.lazy.defer="glosa" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                    
            </textarea>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <button  @if(!$this->documentos) disabled @endif type="button" class="btn btn-primary" wire:click='emitir'>Emitir Nota de Credito</button>
        </div>
    </div>
</div>
