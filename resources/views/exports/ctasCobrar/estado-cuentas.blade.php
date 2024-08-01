<div>
    <table>
        <tr>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="6" rowspan="3">
                <h1>ESTADO DE CUENTA - CLIENTE: {{ $cliente->razonSocial }}</h1>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                <table>
                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">LINEA DE CREDITO: </td>
                        <td>USD {{ $cliente->montoCredito }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">DEUDA TOTAL: </td>
                        <td>USD {{$suma}}</td>
                    </tr>
                    <tr>
                        <td colspan="2">CREDITO UTILIZADO: </td>
                        <td>USD {{$suma}}</td>
                    </tr>
                    <tr>
                        <td colspan="2">CREDITO VENCIDO: </td>
                        <td>USD {{$suma}}</td>
                    </tr>
                    <tr>
                        <td colspan="2">CREDITO DISPONIBLE: </td>
                        <td>USD 0.00</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">
                <p>EJECUTIVO: {{ $counter->nombre}}</p>
            </td>
            <td></td>
        </tr>
    </table>

    <div class="row">
        <table>
            <thead class="thead-listadoCC">
                <tr>
                    <th></th>
                    <th scope="col" class="py-1 cursor-pointer">
                        TIPO DOC. 
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        NUM. DOC.
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        AEROLINEA
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        F. EMISION
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        F. VENCIMIENTO 
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        N° TKT
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        PASAJERO
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        N/I
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        RUTA
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        MON
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        NETO
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        INAFECTO
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        IGV
                    </th>
                    <th scope="col" class="py-1 cursor-pointer">
                        OTROS IMP.
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        TOTAL
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        SOLICITADO POR
                    </th>
                </tr>
            </thead>
            <tbody>
                @if($cargos)
                @foreach ($cargos as $estCuent)
        
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td></td>
                    {{-- <td class="py-1">@if($estCuent->tipoDocumento == '36')DOC. COBRANZA @elseif($estCuent->tipoDocumento == '01')FACTURA @elseif($estCuent->tipoDocumento == '03')BOLETA @endif</td> --}}
                    <td class="py-1">{{$estCuent->tipoDocumento}}</td>
                    <td class="py-1">{{$estCuent->numeroDocumento}}</td>
                    {{-- <td class="py-1">@if($estCuent->idAerolinea){{$estCuent->tAerolinea->razonSocial}} @else AS TRAVEL @endif</td> --}}
                    <td class="py-1">{{$estCuent->aerolinea}}</td>
                    <td class="py-1">{{\Carbon\Carbon::parse($estCuent->fechaEmision)->format('d-m-Y')}}</td>
                    <td class="py-1">{{\Carbon\Carbon::parse($estCuent->fechaVencimiento)->format('d-m-Y')}}</td>
                    <td class="py-1">{{$estCuent->numeroBoleto}}</td>
                    {{-- <td class="py-1">@if($boleto->tDocumento){{$boleto->tDocumento->serie . '-' . str_pad($boleto->tDocumento->numero,8,"0",STR_PAD_LEFT)}}@else - @endif</td> --}}
                    <td class="py-1">{{$estCuent->pasajero}}</td>
                    <td class="py-1">{{$estCuent->tipoRuta}}</td>
                    <td class="py-1">{{$estCuent->ruta}}</td>
                    <td class="py-1">{{$estCuent->moneda}}</td>
                    <td class="py-1">{{$estCuent->tarifaNeta}}</td>
                    <td class="py-1">{{$estCuent->inafecto}}</td>
                    <td class="py-1">{{$estCuent->igv}}</td>
                    <td class="py-1">{{$estCuent->otrosImpuestos}}</td>
                    <td class="py-1">{{$estCuent->saldo}}</td>
                    <td class="py-1">{{$estCuent->solicitante}}</td>
                    
                </tr>
                @endforeach
                @endif
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Totales: </td>
                </tr>
            </tbody>
        </table>
    </div>
    <table>
        <tr>
            <td></td>
            <td></td>
            <td colspan="8" rowspan="10">
                <div class="col-md-4">
                    <p>* Pago con cheque a nombre de AS Travel Perú SAC</p>
                    <p>* Pagos en soles considerar T/C  S/ 3.8100</p>
                    <p>* Pago con transferencia en nuestras Ctas Bancarias: AS Travel Perú SAC</p>
                    <p>-BANCO CREDITO CTA CTE DOLARES : 194-2473351-1-52</p>
                    <p>-BANCO CREDITO CTA CTE SOLES       : 194-2478717-0-43</p>
                    <p>-BANCO CREDITO COD INTERBANCARIO DOLARES: 002-194-002473351152-91</p>
                    <p>-BANCO CREDITO COD INTERBANCARIO SOLES:002-194-002478717043-90 </p>
                    <p>-BANCO DE LA NACION CTA DETRACCIONES SOLES : 00 058 327778 </p>
                    <p>LEYENDA:</p>
                    <p>N: NACIONAL /  I: INTERNACIONAL"	</p>
                </div>
            </td>
            <td></td>
            <td colspan="10" rowspan="13">
                <div class="col-md-4">
                    <p>CREDITOS Y COBRANZAS:</p>
                    <p>* Si a la recepción de su estado de cuenta haya realizado algun pago parcial o total, por favor no considerar este reporte. </p>
                    <p>* Solicite su usuario y password para acceder a sus reportes de compras.</p>
                    <p>* Si requiere copia virtual de sus comprobantes de pago comuniquese con Creditos y Cobranzas.</p>
                    <p>* Una vez realizado sus pagos y/o transferencias por favor enviar relacion de documentos considerados.</p>
                    <p>* Enviar copia de retencion o detraccion según corresponda.</p>
                    <p>* Usted recibira semanalmente sus estados de cuenta debidamente actualizados.</p>
                    <p>AS Travel Perú...</p>
                    <p>* Solicite sus PROMOCIONES de boletos y/o paquetes turisticos nacionales e internacionales a: vacaciones@astravel.com.pe</p>
                    <p>* Viste nuestra pagina web: www.astravel.com.pe</p>
                </div>
            </td>
        </tr>
    </table>
    <div class="row">
        <div class="col-md-2"></div>
       
        
        <div class="col-md-2"></div>
    </div>
</div>




<div>
    <div class="row">

    </div>
    
</div>