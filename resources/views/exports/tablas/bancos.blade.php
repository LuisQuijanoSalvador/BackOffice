<table>
    <thead>
        <tr>
            <th scope="col">
                ID
            </th>
            <th scope="col">
                Nombre
            </th>
            <th scope="col">
                Num. Cuenta
            </th>
            <th scope="col">
                CCI
            </th>
            <th scope="col">
                CUENTA CONTABLE
            </th>
            <th scope="col">
                Estado
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($bancos as $banco)
            <tr>
                <td class="py-1">{{$banco->id}}</td>
                <td class="py-1">{{$banco->nombre}}</td>
                <td class="py-1">{{$banco->numeroCuenta}}</td>
                <td class="py-1">{{$banco->cci}}</td>
                <td class="py-1">@if(!$banco->cuentaContableSoles){{$banco->cuentaContableDolares}}@else{{$banco->cuentaContableSoles}}@endif</td>
                <td class="py-1">{{$banco->tEstado->descripcion}}</td>
            </tr>
        @endforeach
    </tbody>
</table>