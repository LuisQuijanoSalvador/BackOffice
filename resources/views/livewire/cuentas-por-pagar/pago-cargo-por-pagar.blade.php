<div>
    {{-- The Master doesn't talk, he acts. --}}
    <div>
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show">{{ $message }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif
        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show">{{ $error }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif

        <div class="card card-success card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-hand-holding-usd mr-1"></i> Registrar Pago Múltiple</h3>
                <a href="{{ route('rListaCargos') }}" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i>
                    Volver</a>
            </div>

            <div class="card-body">
                <h5 class="text-muted mb-3">Documentos a Pagar ({{ count($cargos) }})</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th class="text-right">Saldo Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cargos as $cargo)
                                <tr>
                                    <td>{{ $cargo->proveedor->razonSocial ?? 'N/A' }}</td>
                                    <td>{{ $cargo->tipoDocumento }}
                                        {{ $cargo->serieDocumento }}-{{ $cargo->numeroDocumento }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($cargo->saldo, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td colspan="2" class="text-right font-weight-bold">TOTAL A CANCELAR:</td>
                                <td class="text-right font-weight-bold text-danger h5">
                                    {{ number_format($totalSeleccionado, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <form wire:submit.prevent="registrarPago">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="aplicaDetraccion"
                                    wire:model="aplicaDetraccion">
                                <label class="custom-control-label font-weight-bold text-warning"
                                    for="aplicaDetraccion">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> ¿Afecto a Detracción SUNAT?
                                </label>
                            </div>
                        </div>

                        @if ($aplicaDetraccion)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Monto Detracción <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                        <input type="number" step="0.01" wire:model="montoDetraccion"
                                            class="form-control @error('montoDetraccion') is-invalid @enderror"
                                            placeholder="0.00">
                                    </div>
                                    @error('montoDetraccion')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Banco Detracción (Banco Nación) <span
                                            class="text-danger">*</span></label>
                                    <select wire:model="bancoDetraccionId"
                                        class="form-control @error('bancoDetraccionId') is-invalid @enderror">
                                        <option value="">Seleccione Banco</option>
                                        @foreach ($bancos as $banco)
                                            <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('bancoDetraccionId')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Monto a Pagar al Proveedor <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-success text-white">S/</span>
                                    </div>
                                    {{-- Usamos wire:model para que sea editable y reactivo al mismo tiempo --}}
                                    <input type="number" step="0.01" wire:model="montoAPagar"
                                        class="form-control font-weight-bold text-success @error('montoAPagar') is-invalid @enderror">
                                </div>
                                @error('montoAPagar')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">
                                    Sugerido: {{ number_format($totalSeleccionado - $montoDetraccion, 2) }}
                                    (Total {{ number_format($totalSeleccionado, 2) }} - Detracción
                                    {{ number_format($montoDetraccion, 2) }})
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Banco Pago Proveedor <span
                                        class="text-danger">*</span></label>
                                <select wire:model="bancoId"
                                    class="form-control @error('bancoId') is-invalid @enderror">
                                    <option value="">Seleccione Banco</option>
                                    @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('bancoId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fechaPago"
                                    class="form-control @error('fechaPago') is-invalid @enderror">
                                @error('fechaPago')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Observación / N° Operación</label>
                                <input type="text" wire:model="observacion" class="form-control"
                                    placeholder="Ej: Transferencia N° 123456">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm mr-1" role="status"
                                aria-hidden="true"></span>
                            <i class="fas fa-check mr-1" wire:loading.remove></i> Confirmar y Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
