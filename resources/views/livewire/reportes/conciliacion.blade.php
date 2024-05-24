<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <div class="row">
        <div class="col-md-2">
            <p style="text-align:right">F. inicio:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="fechaInicio" id="fechaInicio">
        </div>
        <div class="col-md-2">
            <p style="text-align:right">F. Final:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="fechaFin" id="fechaFin">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div>
    <hr>
    <button @if(!$this->ventas) disabled @elseif(count($this->ventas) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>
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
                        NUM. BOLETO
                    </th>
                    <th scope="col" class="py-1">
                        CLIENTE
                    </th>
                    <th scope="col" class="py-1">
                        PROVEEDOR
                    </th>
                    <th scope="col" class="py-1">
                        F. EMISION
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
                    <th scope="col" class="py-1">
                        PASAJERO
                    </th>
                    <th scope="col" class="py-1">
                        COD4
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($this->ventas)
                    @foreach ($this->ventas as $venta)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{$venta->Origen}}</td>
                            <td class="py-1">{{$venta->Tipo}}</td>
                            <td class="py-1">{{$venta->Documento}}</td>
                            <td class="py-1">{{$venta->NumeroBoleto}}</td>
                            <td class="py-1">{{$venta->Cliente}}</td>
                            <td class="py-1">{{$venta->Proveedor}}</td>
                            <td class="py-1">{{$venta->FechaEmision}}</td>
                            <td class="py-1">{{$venta->totalOrigen}}</td>
                            <td class="py-1">{{$venta->XM}}</td>
                            <td class="py-1">{{$venta->total}}</td>
                            <td class="py-1">{{$venta->pasajero}}</td>
                            <td class="py-1">{{$venta->cod4}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
