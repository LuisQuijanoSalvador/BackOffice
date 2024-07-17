<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <div class="row">
        <div class="col-md-3">
            <select name="consolidador" class="form-select" id="cboComsolidador" wire:model.lazy.defer="idConsolidador">
                <option>==Seleccione Consolidador==</option>
                @foreach ($consolidadors as $consolidador)
                    <option value={{$consolidador->id}}>{{$consolidador->razonSocial}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <p style="text-align:right">F. inicio:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="fechaInicio" id="fechaInicio">
        </div>
        <div class="col-md-1">
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
    <button @if(!$this->segmentos) disabled @elseif(count($this->segmentos) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>
    
    <div class="contenedorTablaReport">
        <table class="tabla-listado">
            <thead class="thead-listadoCC">
                <tr>
                    <th scope="col" class="py-1">
                        Consolidador
                    </th>
                    <th scope="col" class="py-1">
                        AEROLINEA
                    </th>
                    <th scope="col" class="py-1">
                        FechaEmision
                    </th>
                    <th scope="col" class="py-1">
                        Boleto
                    </th>
                    <th scope="col" class="py-1">
                        CiudadSalida
                    </th>
                    <th scope="col" class="py-1">
                        CiudadLLEGADA
                    </th>
                    <th scope="col" class="py-1">
                        Vuelo
                    </th>
                    <th scope="col" class="py-1">
                        Clase
                    </th>
                    <th scope="col" class="py-1">
                        FechaSalida
                    </th>
                    <th scope="col" class="py-1">
                        HoraSalida
                    </th>
                    <th scope="col" class="py-1">
                        FechaLlegada
                    </th>
                    <th scope="col" class="py-1">
                        HoraLlegada
                    </th>
                    <th scope="col" class="py-1">
                        FareBasis
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($this->segmentos)
                    @foreach ($this->segmentos as $segmento)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{$segmento->consolidador}}</td>
                            <td class="py-1">{{$segmento->aerolinea}}</td>
                            <td class="py-1">{{\Carbon\Carbon::parse($segmento->fechaEmision)->format('d-m-Y')}}</td>
                            <td class="py-1">{{$segmento->numeroBoleto}}</td>
                            <td class="py-1">{{$segmento->ciudadSalida}}</td>
                            <td class="py-1">{{$segmento->ciudadLlegada}}</td>
                            <td class="py-1">{{$segmento->vuelo}}</td>
                            <td class="py-1">{{$segmento->clase}}</td>
                            <td class="py-1">{{$segmento->fechaSalida}}</td>
                            <td class="py-1">{{$segmento->horaSalida}}</td>
                            <td class="py-1">{{$segmento->fechaLlegada}}</td>
                            <td class="py-1">{{$segmento->horaLlegada}}</td>
                            <td class="py-1">{{$segmento->farebasis}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
