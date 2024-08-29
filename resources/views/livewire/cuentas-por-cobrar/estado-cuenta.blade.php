<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
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
    <div class="contenedorFiltro">
        <div class="row">
            <div class="col-md-4">
                <label for="selectedCliente">Seleccione Cliente:</label>
                <select name="selectedCliente" style="width: 100%; display:block;font-size: 0.9em; height:31px;" class="rounded" id="cboCliente" wire:model.lazy.defer="idCliente">
                    <option value="">-- Buscar Cliente --</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{$cliente->id}}">{{$cliente->razonSocial}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="txtFechaInicio">Fecha Inicial:</label>
                <input type="date" class="rounded" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em; height:31px;" id="txtFechaInicio" wire:model="fechaInicio">
            </div>
            <div class="col-md-3">
                <label for="txtFechaFinal">Fecha Final:</label>
                <input type="date" class="rounded" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em; height:31px;" id="txtFechaFinal" wire:model="fechaFinal">
            </div>
            <div class="col-md-2">
                <br>
                <button type="button" class="btn btn-primary rounded" wire:click='buscar'>
                    Buscar
                </button>
            </div>
        </div>
    </div>
    <hr>
    <button @if(!$estadoCuentas) disabled @elseif(count($estadoCuentas) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>

    <div class="contenedorTablaCC">
        <table class="tabla-listado">
            <thead class="thead-listadoCC">
                <tr>
                    <th scope="col" class="py-1 cursor-pointer">
                        Tipo Doc. 
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Num. Doc 
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Aerolinea
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        F. Emision
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        F. Vencimiento 
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        N° TKT
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Pasajero
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        N/I
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Ruta
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Mon
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Neto
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Inafecto
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        IGV
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        Otros Imp.
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        Total
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        Solicitado por
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($estadoCuentas)
                @foreach ($estadoCuentas as $estCuent)
    
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    {{-- <td class="py-1">@if($estCuent->tipoDocumento == '36')DOC. COBRANZA @elseif($estCuent->tipoDocumento == '01')FACTURA @elseif($estCuent->tipoDocumento == '03')BOLETA @endif</td> --}}
                    <td class="py-1">{{$estCuent->tipoDocumento}}</td>
                    <td class="py-1">{{$estCuent->numeroDocumento}}</td>
                    {{-- <td class="py-1">@if($estCuent->idAerolinea){{$estCuent->tAerolinea->razonSocial}} @else AS TRAVEL @endif</td> --}}
                    <td class="py-1">{{$estCuent->aerolinea}}</td>
                    <td class="py-1">{{\Carbon\Carbon::parse($estCuent->fechaEmision)->format('d-m-Y')}}</td>
                    <td class="py-1">{{\Carbon\Carbon::parse($estCuent->fechaVencimiento)->format('d-m-Y')}}</td>
                    <td class="py-1">{{$estCuent->numeroBoleto}}</td>
                    {{-- <td class="py-1">@if($boleto->tDocumento){{$boleto->tDocumento->serie . '-' . str_pad($boleto->tDocumento->numero,8,"0",STR_PAD_LEFT)}}@else - @endif</td> --}}
                    <td class="py-1">{{$estCuent->pasajero}}</td>
                    <td class="py-1">{{$estCuent->tipoRuta}}</td>
                    <td class="py-1">{{$estCuent->ruta}}</td>
                    <td class="py-1">{{$estCuent->moneda}}</td>
                    <td class="py-1">{{$estCuent->tarifaNeta}}</td>
                    <td class="py-1">{{$estCuent->inafecto}}</td>
                    <td class="py-1">{{$estCuent->igv}}</td>
                    <td class="py-1">{{$estCuent->otrosImpuestos}}</td>
                    <td class="py-1">{{$estCuent->saldo}}</td>
                    <td class="py-1">{{$estCuent->solicitante}}</td>
                    
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
