<table>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td colspan="5"> 
            <h3>LISTADO DE SERVICIOS FACTURADOS</h3>
        </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th scope="col">
                
            </th>
            <th scope="col">
                F. Emisión
            </th>
            <th scope="col">
                Pasajero
            </th>
            <th scope="col">
                TKT
            </th>
            <th scope="col">
                Aerolinea
            </th>
            <th scope="col">
                Ruta
            </th>
            <th scope="col">
                Tipo Ruta
            </th>
            <th scope="col">
                Neto 
            </th>
            <th scope="col">
                Inafecto 
            </th>
            <th scope="col">
                IGV 
            </th>
            <th scope="col">
                Otros Imp. 
            </th>
            <th scope="col">
                Total 
            </th>
            <th scope="col">
                Solicitante 
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalNeto = 0;
            $totalInafecto = 0;
            $totalIgv = 0;
            $totalOtrosImpuestos = 0;
            $totalTotal = 0;
        @endphp
        {{-- @if($servicios) --}}
            @foreach ($boletos as $boleto)
                <tr>
                    <td></td>
                    <td>{{\Carbon\Carbon::parse($boleto->fechaEmision)->format('d-m-Y')}}</td>
                    <td>{{$boleto->pasajero}}</td>
                    <td>{{$boleto->numeroBoleto}}</td>
                    <td>@if($boleto->tAerolinea){{$boleto->tAerolinea->razonSocial}} @else AS TRAVEL PERU SAC @endif</td>
                    <td>{{$boleto->ruta}}</td>
                    <td>{{$boleto->tipoRuta}}</td>
                    <td>{{$boleto->tarifaNeta}}</td>
                    <td>{{$boleto->inafecto}}</td>
                    <td>{{$boleto->igv}}</td>
                    <td>{{$boleto->otrosImpuestos}}</td>
                    <td>{{$boleto->total}}</td>
                    <td>@if($boleto->tSolicitante){{$boleto->tSolicitante->nombres}}@else -- @endif</td>
                </tr>
                {{$totalTotal += $boleto->total}}
                {{$totalInafecto += $boleto->inafecto}}
                {{$totalIgv += $boleto->igv}}
                {{$totalOtrosImpuestos += $boleto->otrosImpuestos}}
                {{$totalNeto += $boleto->tarifaNeta}}
            @endforeach
            <tr>
                <td colspan="6"></td>
                <td>Totales: </td>
                <td>{{$totalNeto}}</td>
                <td>{{$totalInafecto}}</td>
                <td>{{$totalIgv}}</td>
                <td>{{$totalOtrosImpuestos}}</td>
                <td>{{$totalTotal}}</td>
            </tr>
        {{-- @endif --}}
    </tbody>
</table>