<div>
    {{-- The Master doesn't talk, he acts. --}}
    <div>
        {{-- Mensajes de alerta --}}
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-1"></i> Registrar Pago / Abono</h3>
                <div class="card-tools">
                    <a href="{{ route('rListaCargos') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- Información de la Deuda --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="info-box bg-light">
                            <div class="info-box-content">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="info-box-text text-muted">Proveedor</span>
                                        <span
                                            class="info-box-number font-weight-bold">{{ $cargo->proveedor->razonSocial ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="info-box-text text-muted">Documento</span>
                                        <span class="info-box-number font-weight-bold">{{ $cargo->tipoDocumento }}
                                            {{ $cargo->serieDocumento }}-{{ $cargo->numeroDocumento }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="info-box-text text-muted">Total Original</span>
                                        <span class="info-box-number font-weight-bold">{{ $cargo->moneda }}
                                            {{ number_format($cargo->total, 2) }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="info-box-text text-muted">Saldo Pendiente</span>
                                        <span class="info-box-number font-weight-bold text-danger">{{ $cargo->moneda }}
                                            {{ number_format($cargo->saldo, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Formulario de Pago --}}
                <form wire:submit.prevent="registrarPago">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="monto" class="font-weight-bold">Monto a Pagar <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{ $cargo->moneda }}</span>
                                    </div>
                                    <input type="number" step="0.01" id="monto" wire:model="monto"
                                        class="form-control @error('monto') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('monto')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Máximo permitido:
                                    {{ number_format($cargo->saldo, 2) }}</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bancoId" class="font-weight-bold">Banco / Cuenta de Origen <span
                                        class="text-danger">*</span></label>
                                <select id="bancoId" wire:model="bancoId"
                                    class="form-control @error('bancoId') is-invalid @enderror">
                                    <option value="">Seleccione Banco</option>
                                    @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id }}">{{ $banco->nombre }} -
                                            {{ $banco->cuenta ?? '' }}</option>
                                    @endforeach
                                </select>
                                @error('bancoId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="fechaPago" class="font-weight-bold">Fecha de Pago <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="fechaPago" wire:model="fechaPago"
                                    class="form-control @error('fechaPago') is-invalid @enderror">
                                @error('fechaPago')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observacion" class="font-weight-bold">Observación / N° de Operación
                                    (Opcional)</label>
                                <input type="text" id="observacion" wire:model="observacion" class="form-control"
                                    placeholder="Ej: Transferencia N° 123456">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm mr-1" role="status"
                                aria-hidden="true"></span>
                            <i class="fas fa-check mr-1" wire:loading.remove></i>
                            Confirmar y Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
