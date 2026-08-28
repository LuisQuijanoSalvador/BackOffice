<div>
    {{-- A good traveler has no fixed plans and is not intent upon arriving. --}}
    <div>
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show">{{ $message }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif
        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show">{{ $error }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif

        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i> Detalle del Abono</h3>
                <div class="card-tools">
                    <a href="{{ route('abonos.index') }}" class="btn btn-sm btn-default"><i
                            class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Proveedor:</strong> {{ $abono->cargo->proveedor->razonSocial ?? 'N/A' }}</p>
                        <p><strong>Documento:</strong> {{ $abono->cargo->tipoDocumento }}
                            {{ $abono->cargo->serieDocumento }}-{{ $abono->cargo->numeroDocumento }}</p>
                        <p><strong>Fecha de Pago:</strong> {{ $abono->fechaPago->format('d/m/Y') }}</p>
                        <p><strong>Banco:</strong> {{ $abono->banco->nombre ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Moneda:</strong> {{ $abono->moneda }}</p>
                        <p><strong>Monto Abonado:</strong> <span
                                class="text-success font-weight-bold h4">{{ number_format($abono->monto, 2) }}</span>
                        </p>
                        <p><strong>Estado:</strong>
                            <span
                                class="badge {{ $abono->estado->descripcion == 'Anulado' ? 'badge-danger' : 'badge-success' }} p-2">
                                {{ $abono->estado->descripcion ?? 'Activo' }}
                            </span>
                        </p>
                        <p><strong>Observación:</strong> {{ $abono->observacion ?? 'Sin observaciones' }}</p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                @if ($abono->estado->descripcion != 'Anulado')
                    <button wire:click="anular"
                        wire:confirm="¿Estás seguro de anular este abono? Se restaurará el saldo de la deuda."
                        class="btn btn-warning mr-2">
                        <i class="fas fa-ban"></i> Anular Abono
                    </button>
                    <button wire:click="eliminar"
                        wire:confirm="¿ELIMINAR PERMANENTEMENTE? Esta acción no se puede deshacer."
                        class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                @else
                    <div class="alert alert-warning mb-0">Este abono se encuentra anulado.</div>
                @endif
            </div>
        </div>
    </div>
</div>
