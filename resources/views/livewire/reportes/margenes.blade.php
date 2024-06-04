<div>
    {{-- Success is as dangerous as failure. --}}
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
    <button @if(!$this->margenes) disabled @elseif(count($this->margenes) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>
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
                        NUM. BOLETO
                    </th>
                    <th scope="col" class="py-1">
                        FILE
                    </th>
                    <th scope="col" class="py-1">
                        CLIENTE
                    </th>
                    <th scope="col">
                        PASAJERO
                    </th>
                    <th scope="col">
                        DOCUMENTO
                    </th>
                    <th scope="col" class="py-1">
                        COUNTER
                    </th>
                    <th scope="col" class="py-1">
                        F. EMISION
                    </th>
                    <th scope="col" class="py-1">
                        RUTA
                    </th>
                    <th scope="col" class="py-1">
                        MARGEN
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($this->margenes)
                    @foreach ($this->margenes as $margen)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{$margen->Origen}}</td>
                            <td class="py-1">{{$margen->Tipo}}</td>
                            <td class="py-1">{{$margen->NumeroBoleto}}</td>
                            <td class="py-1">{{$margen->FILE}}</td>
                            <td class="py-1">{{$margen->Cliente}}</td>
                            <td class="py-1">{{$margen->Pasajero}}</td>
                            <td class="py-1">{{$margen->Documento}}</td>
                            <td class="py-1">{{$margen->Counter}}</td>
                            <td class="py-1">{{\Carbon\Carbon::parse($margen->FechaEmision)->format('d-m-Y')}}</td>
                            <td class="py-1">{{$margen->Ruta}}</td>
                            <td class="py-1">{{$margen->XM}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
