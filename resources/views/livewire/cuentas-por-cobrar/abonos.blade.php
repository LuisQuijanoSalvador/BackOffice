<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
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
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
        <div class="col-md-2">
            <input type="text" wire:model.lazy.defer="numDoc" id="txtNumDoc" style="width: 100%; text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();" placeholder="Buscar por Documento">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-primary" wire:click="buscarDoc" >Buscar</button>
        </div>
    </div>
    <hr>
    <div class="contenedorTablaCC">
        <table class="tabla-listado">
            <thead class="thead-listado">
                <tr>
                    <th scope="col" class="py-1">
                         
                    </th>
                    <th scope="col" class="py-1">
                        FECHA 
                    </th>
                    <th scope="col" class="py-1">
                        MONTO
                    </th>
                    <th scope="col" class="py-1">
                        DOCUMENTO
                    </th>
                    <th scope="col" class="py-1">
                        CLIENTE
                    </th>
                    <th scope="col" class="py-1">
                        MEDIO PAGO
                    </th>
                    <th scope="col" class="py-1">
                        REFERENCIA
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        BANCO
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        NUM. CUENTA
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        NUM. ABONO
                    </th>
                    <th scope="col" class="py-1 thAccion">
                        OBSERVACIONES
                    </th>
                </tr>
            </thead>
            <tbody>

                @foreach ($abonos as $abono)
    
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1">
                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                            <button type="button" class="btn btn-outline-primary mr-2 rounded" data-bs-toggle="modal" data-bs-target="#modalVer" wire:click='ver("{{$abono->numeroAbono}}")'>Ver</button>
                        </div>
                    </td>
                    <td class="py-1">{{$abono->FechaAbono}}</td>
                    <td class="py-1">{{$abono->Monto}}</td>
                    <td class="py-1">{{$abono->Documento}}</td>
                    <td class="py-1">{{$abono->Cliente}}</td>
                    <td class="py-1">{{$abono->MedioPago}}</td>
                    <td class="py-1">{{$abono->Referencia}}</td>
                    <td class="py-1">{{$abono->Banco}}</td>
                    <td class="py-1">{{$abono->numeroCuenta}}</td>
                    <td class="py-1">{{$abono->numeroAbono}}</td>
                    <td class="py-1">{{$abono->observaciones}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <button @if(!$abonos) disabled @elseif(count($abonos) == 0) disabled @endif type="button" class="btn btn-success rounded" wire:click='exportar'>Exportar</button>

    {{-- Modal para visualizar --}}
    <div class="modal fade" id="modalVer" wire:ignore.self tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Abono</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click='limpiarAbonosVista'></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="txtFechaAbono" class="form-label">F. Abono:</label>
                        <input disabled type="date" class="" style="width: 100%; display:block;font-size: 0.8em;font-size: 0.8em;" id="txtFechaAbono" wire:model.lazy.defer="fechaAbono">
                    </div>
                    <div class="col-md-4">
                        <label for="idMedioPago" class="form-label">Medio Pago:</label>
                        <select disabled name="idMedioPago" style="width: 100%;font-size: 0.8em; display:inline;" id="cboFPago" wire:model.lazy.defer="idMedioPago">
                            <option>==Seleccione una opción==</option>
                            @foreach ($medioPagos as $medioPago)
                                <option value={{$medioPago->id}}>{{$medioPago->descripcion}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="txtReferencia" class="">Nro.Tarj/Deposito:</label>
                        <input disabled type="text" class="uTextBox" id="txtReferencia" wire:model.lazy="referencia" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="cboTarjeta" class="form-label">Tarjeta:</label>
                        <select disabled name="idTarjetaCredito" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboTarjeta" wire:model.defer="idTarjetaCredito">
                            <option>Seleccione una Opción</option>
                            @foreach ($tarjetaCreditos as $tarjetaCredito)
                                <option value="{{$tarjetaCredito->id}}">{{$tarjetaCredito->descripcion}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cboBanco" class="form-label">Banco:</label>
                        <select disabled name="idBanco" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboBanco" wire:model.defer="idBanco">
                            <option>Seleccione una Opción</option>
                            @foreach ($bancos as $banco)
                                <option value="{{$banco->id}}">{{$banco->nombre}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cboMoneda" class="form-label">Moneda:</label>
                        <select disabled name="moneda" style="width: 100%;font-size: 0.8em; display:block;" id="cboMoneda" wire:model.lazy.defer="moneda">
                            <option>==Seleccione una opción==</option>
                            @foreach ($monedas as $moneda)
                                <option value={{$moneda->id}}>{{$moneda->codigo}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="txtTipoCambio" class="form-label">Tipo Cambio:</label>
                        <input type="number" disabled class="uTextBox" id="txtTipoCambio" wire:model.lazy.defer="tipoCambio">
                    </div>
                    <div class="col-md-4">
                        <label for="txtObservaciones" class="form-label">Concepto:</label>
                        <input disabled type="text" class="uTextBox" id="txtObservaciones" wire:model.lazy.defer="observaciones" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                    </div>
                    <div class="col-md-4">
                        <label for="txtTotal" class="form-label">Total:</label>
                        <input disabled type="text" class="uTextBox" id="txtTotal" wire:model.lazy.defer="totalAbono">
                    </div>
                </div>
                <hr>
                <div class="contenedorTabla">
                    <table class="tabla-listado">
                        <thead class="thead-listado">
                            <tr>
                                <th scope="col">Abono</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Documento</th>
                                <th scope="col">Cargo</th>
                                <th scope="col">Abono</th>
                                <th scope="col">Saldo</th>
                                <th scope="col">Moneda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($this->abonosVista)
                            @foreach ($this->abonosVista as $abono)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="py-1">{{$abono->numeroAbono}}</td>
                                    <td class="py-1">{{$abono->fechaAbono}}</td>
                                    <td class="py-1">{{$abono->Documento}}</td>
                                    <td class="py-1">{{$abono->montoCargo}}</td>
                                    <td class="py-1">{{$abono->Abono}}</td>
                                    <td class="py-1">{{$abono->Saldo}}</td>
                                    <td class="py-1">{{$abono->Moneda}}</td>
                                    </td>
                                </tr>
                            @endforeach
                            @endif
                            {{-- @php
                                $totalPagos = 0;
                                foreach ($this->abonos as $cargo) {
                                    $totalPagos += $pagos[$cargo->id] ?? 0;
                                }
                                $this->totalPagos = $totalPagos;
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td colspan="6"></td>
                                <td colspan="4" class="text-right font-weight-bold">Total de Pagos: {{$totalPagos}} &nbsp;&nbsp;&nbsp;</td>
                            </tr> --}}
                            
                        </tbody>
                    </table>
                </div> 
                <hr>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click='limpiarAbonosVista'>Cerrar</button>
            </div>
          </div>
        </div>
    </div>
    {{-- Fin del modal  --}}

</div>

