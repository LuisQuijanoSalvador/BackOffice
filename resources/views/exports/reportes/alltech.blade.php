<div class="contenedorTablaReport">
    <table class="tabla-listado">
        <thead class="thead-listadoCC">
            <tr>
                <th scope="col" class="py-1">
                    F. EMISION
                </th>
                <th scope="col" class="py-1">
                    PASAJERO
                </th>
                <th scope="col" class="py-1">
                    AEROLINEA
                </th>
                <th scope="col" class="py-1">
                    CLASE
                </th>
                <th scope="col" class="py-1">
                    F. SALIDA
                </th>
                <th scope="col" class="py-1">
                    F. LLEGADA
                </th>
                <th scope="col" class="py-1">
                    RUTA
                </th>
                <th scope="col" class="py-1">
                    MONEDA
                </th>
                <th scope="col" class="py-1">
                    AFECTO
                </th>
                <th scope="col" class="py-1">
                    INAFECTO
                </th>
                <th scope="col" class="py-1">
                    IGV
                </th>
                <th scope="col" class="py-1">
                    OTROS IMP.
                </th>
                <th scope="col" class="py-1">
                    TOTAL
                </th>
                <th scope="col" class="py-1">
                    NETO FEE
                </th>
                <th scope="col" class="py-1">
                    IGV FEE
                </th>
                <th scope="col" class="py-1">
                    TOTAL FEE
                </th>
                <th scope="col" class="py-1">
                    TICKET
                </th>
                <th scope="col" class="py-1">
                    DOCUMENTO
                </th>
                <th scope="col" class="py-1">
                    SOLICITANTE
                </th>
                <th scope="col" class="py-1">
                    CENTRO COSTO
                </th>
                <th scope="col" class="py-1">
                    NUM. FILE
                </th>
            </tr>
        </thead>
        <tbody>
            @if($ventass)
                @foreach ($ventass as $venta)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td class="py-1">{{\Carbon\Carbon::parse($venta->fechaEmision)->format('d-m-Y')}}</td>
                        <td class="py-1">{{$venta->pasajero}}</td>
                        <td class="py-1">{{$venta->Aerolinea}}</td>
                        <td class="py-1">{{$venta->clase}}</td>
                        <td class="py-1">{{$venta->fechaSalida}}</td>
                        <td class="py-1">{{$venta->fechaLlegada}}</td>
                        <td class="py-1">{{$venta->ruta}}</td>
                        <td class="py-1">{{$venta->codigo}}</td>
                        <td class="py-1">{{$venta->tarifaNeta}}</td>
                        <td class="py-1">{{$venta->Inafecto}}</td>
                        <td class="py-1">{{$venta->igv}}</td>
                        <td class="py-1">{{$venta->otrosImpuestos}}</td>
                        <td class="py-1">{{$venta->total}}</td>
                        <td class="py-1">{{$venta->NetoFee}}</td>
                        <td class="py-1">{{$venta->IGVFee}}</td>
                        <td class="py-1">{{$venta->TotalFee}}</td>
                        <td class="py-1">{{$venta->Ticket}}</td>
                        <td class="py-1">{{$venta->Documento}}</td>
                        <td class="py-1">{{$venta->Solicitante}}</td>
                        <td class="py-1">{{$venta->centroCosto}}</td>
                        <td class="py-1">{{$venta->numeroFile}}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>