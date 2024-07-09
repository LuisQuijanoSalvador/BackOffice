<div>
    {{-- The best athlete wants his opponent at his best. --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-3">
            <select name="vendedor" class="" id="cboVendedor" wire:model.lazy="vendedor">
                <option>==Seleccione un Vendedor==</option>
                @foreach ($vendedors as $vendedor)
                    <option value={{$vendedor->id}}>{{$vendedor->nombre}}</option>
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
            <select name="vendedor" class="" id="cboVendedor" wire:model.lazy="tipoComision">
                <option>Seleccione Tipo</option>
                <option value="BOLETOS">BOLETOS</option>
                <option value="SERVICIOS">SERVICIOS</option>
            
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div>
    <hr>
    <button @if(!$this->comisiones) disabled @elseif(count($this->comisiones) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>
    <div class="contenedorTablaReport">
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
                @if($this->comisiones)
                    @foreach ($this->comisiones as $comision)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="py-1">{{\Carbon\Carbon::parse($comision->fechaEmision)->format('d-m-Y')}}</td>
                            <td class="py-1">{{$comision->Proveedor}}</td>
                            <td class="py-1">{{$comision->servicio}}</td>
                            <td class="py-1">{{$comision->numeroBoleto}}</td>
                            <td class="py-1">{{$comision->Cliente}}</td>
                            <td class="py-1">{{$comision->pasajero}}</td>
                            <td class="py-1">{{$comision->tipoRuta}}</td>
                            <td class="py-1">{{$comision->ruta}}</td>
                            <td class="py-1">{{$comision->Moneda}}</td>
                            <td class="py-1">{{$comision->tarifaNeta}}</td>
                            <td class="py-1">{{$comision->xm}}</td>
                            <td class="py-1">{{$comision->netoXM}}</td>
                            <td class="py-1">{{$comision->numeroDocumento}}</td>
                            <td class="py-1">{{$comision->medioPago}}</td>
                            <td class="py-1">{{$comision->Porcentaje}}</td>
                            <td class="py-1">{{$comision->Comision}}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
