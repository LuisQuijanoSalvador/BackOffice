<table class="tabla-listado">
    <thead class="thead-listadoCC">
        <tr>
            <th scope="col" class="py-1">
                F. EMISION
            </th>
            <th scope="col" class="py-1">
                PROVEEDOR
            </th>
            <th scope="col" class="py-1">
                SERVICIO
            </th>
            <th scope="col" class="py-1">
                NUM. BOLETO
            </th>
            <th scope="col" class="py-1">
                CLIENTE
            </th>
            <th scope="col" class="py-1">
                PASAJERO
            </th>
            <th scope="col" class="py-1">
                TIPO RUTA
            </th>
            <th scope="col" class="py-1">
                RUTA
            </th>
            <th scope="col" class="py-1">
                MONEDA
            </th>
            <th scope="col" class="py-1">
                TARIFA
            </th>
            <th scope="col" class="py-1">
                XM
            </th>
            <th scope="col" class="py-1">
                NETO XM
            </th>
            <th scope="col" class="py-1">
                NUM DOCUMENTO
            </th>
            <th scope="col" class="py-1">
                MEDIO PAGO
            </th>
            <th scope="col" class="py-1">
                %COMISION
            </th>
            <th scope="col" class="py-1">
                COMISION
            </th>
        </tr>
    </thead>
    <tbody>
        @if($ventass)
            @foreach ($ventass as $venta)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1">{{\Carbon\Carbon::parse($venta->fechaEmision)->format('d-m-Y')}}</td>
                    <td class="py-1">{{$venta->Proveedor}}</td>
                    <td class="py-1">{{$venta->servicio}}</td>
                    <td class="py-1">{{$venta->numeroBoleto}}</td>
                    <td class="py-1">{{$venta->Cliente}}</td>
                    <td class="py-1">{{$venta->pasajero}}</td>
                    <td class="py-1">{{$venta->tipoRuta}}</td>
                    <td class="py-1">{{$venta->ruta}}</td>
                    <td class="py-1">{{$venta->Moneda}}</td>
                    <td class="py-1">{{$venta->tarifaNeta}}</td>
                    <td class="py-1">{{$venta->xm}}</td>
                    <td class="py-1">{{$venta->netoXM}}</td>
                    <td class="py-1">{{$venta->numeroDocumento}}</td>
                    <td class="py-1">{{$venta->medioPago}}</td>
                    <td class="py-1">{{$venta->Porcentaje}}</td>
                    <td class="py-1">{{$venta->Comision}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>