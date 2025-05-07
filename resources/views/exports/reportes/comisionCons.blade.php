<table>
    <thead>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th colspan="9"> <h2>REPORTE DE COMISIONES</h2> </th>
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
                COMISION
            </th>
        </tr>
    </thead>
    <tbody>
        @if($ventass)
            @foreach ($ventass as $venta)
                <tr>
                    <td class="py-1">{{$venta['Origen']}}</td>
                    <td class="py-1">{{$venta['Tipo']}}</td>
                    <td class="py-1">{{$venta['NumeroBoleto']}}</td>
                    <td class="py-1">{{$venta['FILE']}}</td>
                    <td class="py-1">{{$venta['Cliente']}}</td>
                    <td class="py-1">{{$venta['Pasajero']}}</td>
                    <td class="py-1">{{$venta['Documento']}}</td>
                    <td class="py-1">{{$venta['Counter']}}</td>
                    <td class="py-1">{{\Carbon\Carbon::parse($venta['FechaEmision'])->format('d-m-Y')}}</td>
                    <td class="py-1">{{$venta['Ruta']}}</td>
                    <td class="py-1">{{$venta['montoComision']}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>