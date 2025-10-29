<div>
    {{-- Stop trying to control. --}}
    <div class="row">
        <div class="col-md-3">
            <select name="cliente" class="form-select" id="cboCliente" wire:model.live="idCliente">
                <option>==Seleccione un Cliente==</option>
                @foreach ($clientes as $cliente)
                    <option value={{$cliente->id}}>{{$cliente->razonSocial}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <p style="text-align:right">F. inicio:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.live="fechaInicio" id="fechaInicio">
        </div>
        <div class="col-md-1">
            <p style="text-align:right">F. Final:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.live="fechaFin" id="fechaFin">
        </div>
        <div class="col-md-2">
            {{--<button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button> --}}
        </div>
    </div>
    <hr>
    <button @if(!$datos) disabled @elseif(count($datos) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>
    <div wire:loading wire:target="exportar" class="text-primary">
        <img src="{{ asset('img/loading.gif') }}" alt=""> <span style="color: rgb(66, 66, 66)">Procesando...</span>
    </div>
    {{-- {{$this->fechaInicio}}{{$this->fechaFin}}{{$this->idCliente}} --}}
    <div class="contenedorTablaReport">
        <table class="tabla-listado">
            <thead class="thead-listadoCC">
                <tr>
                    <th scope="col" class="py-1">
                        ORIGEN
                    </th>
                    <th scope="col" class="py-1">
                        TIPO
                    </th>
                    <th scope="col" class="py-1">
                        DOCUMENTO
                    </th>
                    <th scope="col" class="py-1">
                        TIPO DOC
                    </th>
                    <th scope="col" class="py-1">
                        FILE
                    </th>
                    <th scope="col" class="py-1">
                        BOLETO
                    </th>
                    <th scope="col" class="py-1">
                        PASAJERO
                    </th>
                    <th scope="col" class="py-1">
                        SOLICITANTE
                    </th>
                    <th scope="col" class="py-1">
                        RUTA
                    </th>
                    <th scope="col" class="py-1">
                        TIPO RUTA
                    </th>
                    <th scope="col" class="py-1">
                        COUNTER
                    </th>
                    <th scope="col" class="py-1">
                        CENTRO COSTO
                    </th>
                    <th scope="col" class="py-1">
                        COD1
                    </th>
                    <th scope="col" class="py-1">
                        COD2
                    </th>
                    <th scope="col" class="py-1">
                        COD3
                    </th>
                    <th scope="col" class="py-1">
                        COD4
                    </th>
                    <th scope="col" class="py-1">
                        CLIENTE
                    </th>
                    <th scope="col" class="py-1">
                        PROVEEDOR
                    </th>
                    <th scope="col" class="py-1">
                        CONSOLIDADOR
                    </th>
                    <th scope="col" class="py-1">
                        FECHA EMISION
                    </th>
                    <th scope="col" class="py-1">
                        MONEDA
                    </th>
                    <th scope="col" class="py-1">
                        TARIFA NETA
                    </th>
                    <th scope="col" class="py-1">
                        INAFECTO
                    </th>
                    <th scope="col" class="py-1">
                        OTROS IMP.
                    </th>
                    <th scope="col" class="py-1">
                        IGV
                    </th>
                    <th scope="col" class="py-1">
                        TOTAL ORIGEN
                    </th>
                    <th scope="col" class="py-1">
                        XM
                    </th>
                    <th scope="col" class="py-1">
                        TOTAL
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($datos)
                    @foreach ($datos as $venta)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{$venta->Origen}}</td>
                            <td class="py-1">{{$venta->Tipo}}</td>
                            <td class="py-1">{{$venta->Documento}}</td>
                            <td class="py-1">{{$venta->TipoDoc}}</td>
                            <td class="py-1">{{$venta->numeroFile}}</td>
                            <td class="py-1">{{$venta->NumeroBoleto}}</td>
                            <td class="py-1">{{$venta->pasajero}}</td>
                            <td class="py-1">{{$venta->Solicitante}}</td>
                            <td class="py-1">{{$venta->Ruta}}</td>
                            <td class="py-1">{{$venta->TipoRuta}}</td>
                            <td class="py-1">{{$venta->Counter}}</td>
                            <td class="py-1">{{$venta->CentroCosto}}</td>
                            <td class="py-1">{{$venta->Cod1}}</td>
                            <td class="py-1">{{$venta->Cod2}}</td>
                            <td class="py-1">{{$venta->Cod3}}</td>
                            <td class="py-1">{{$venta->Cod4}}</td>
                            <td class="py-1">{{$venta->Cliente}}</td>
                            <td class="py-1">{{$venta->Proveedor}}</td>
                            <td class="py-1">{{$venta->Consolidador}}</td>
                            <td class="py-1">{{\Carbon\Carbon::parse($venta->FechaEmision)->format('d-m-Y')}}</td>
                            <td class="py-1">{{$venta->Moneda}}</td>
                            <td class="py-1">{{$venta->TarifaNeta}}</td>
                            <td class="py-1">{{$venta->Inafecto}}</td>
                            <td class="py-1">{{$venta->OtrosImpuestos}}</td>
                            <td class="py-1">{{$venta->IGV}}</td>
                            <td class="py-1">{{$venta->TotalOrigen}}</td>
                            <td class="py-1">{{$venta->XM}}</td>
                            <td class="py-1">{{$venta->Total}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    {{$datos->links()}}
</div>
