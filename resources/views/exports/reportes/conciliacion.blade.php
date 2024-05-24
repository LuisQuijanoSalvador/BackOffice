<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
    }
</style>
<table>
    <thead>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th colspan="9"> <h2>REPORTE DE VENTAS</h2> </th>
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
                DOCUMENTO
            </th>
            <th scope="col">
                NUM. BOLETO
            </th>
            <th scope="col">
                CLIENTE
            </th>
            <th scope="col">
                PROVEEDOR
            </th>
            <th scope="col">
                F. EMISION
            </th>
            <th scope="col">
                TOTAL ORIGEN
            </th>
            <th scope="col">
                XM
            </th>
            <th scope="col">
                TOTAL
            </th>
            <th scope="col">
                PASAJERO
            </th>
            <th scope="col">
                COD4
            </th>
        </tr>
    </thead>
    <tbody>
        @if($ventas)
            @foreach ($ventas as $venta)
                <tr>
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