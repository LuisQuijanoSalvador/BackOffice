<table>
    <thead>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th colspan="9"> <h2>REPORTE DE MARGENES</h2> </th>
        </tr>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th scope="col">
                ORIGEN
            </th>
            <th scope="col">
                TIPO
            </th>
            <th scope="col">
                NUM. BOLETO
            </th>
            <th scope="col">
                FILE
            </th>
            <th scope="col">
                CLIENTE
            </th>
            <th scope="col">
                PASAJERO
            </th>
            <th scope="col">
                DOCUMENTO
            </th>
            <th scope="col">
                COUNTER
            </th>
            <th scope="col">
                F. EMISION
            </th>
            <th scope="col">
                RUTA
            </th>
            <th scope="col">
                MARGEN
            </th>
        </tr>
    </thead>
    <tbody>
        @if($margenes)
            @foreach ($margenes as $margen)
                <tr>
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