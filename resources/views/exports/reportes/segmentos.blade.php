<div class="contenedorTablaReport">
    <table class="tabla-listado">
        <thead class="thead-listadoCC">
            <tr>
                <th scope="col" class="py-1">
                    CONSOLIDADOR
                </th>
                <th scope="col" class="py-1">
                    AEROLINEA
                </th>
                <th scope="col" class="py-1">
                    FECHAEMISION
                </th>
                <th scope="col" class="py-1">
                    BOLETO
                </th>
                <th scope="col" class="py-1">
                    CIUDADSALIDA
                </th>
                <th scope="col" class="py-1">
                    CIUDADLLEGADA
                </th>
                <th scope="col" class="py-1">
                    VUELO
                </th>
                <th scope="col" class="py-1">
                    CLASE
                </th>
                <th scope="col" class="py-1">
                    FECHASALIDA
                </th>
                <th scope="col" class="py-1">
                    HORASALIDA
                </th>
                <th scope="col" class="py-1">
                    FECHALLEGADA
                </th>
                <th scope="col" class="py-1">
                    HORALLEGADA
                </th>
                <th scope="col" class="py-1">
                    FAREBASIS
                </th>
            </tr>
        </thead>
        <tbody>
            @if($ventass)
                @foreach ($ventass as $segmento)
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