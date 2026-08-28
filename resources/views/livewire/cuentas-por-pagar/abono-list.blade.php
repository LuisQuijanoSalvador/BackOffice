<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-check-alt mr-1"></i> Listado de Abonos</h3>
                <div class="card-tools">
                    <input type="text" wire:model.debounce.300ms="search" class="form-control form-control-sm"
                        placeholder="Buscar proveedor u observación...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th>Banco</th>
                                <th class="text-right">Monto</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($abonos as $abono)
                                <tr>
                                    <td>{{ $abono->fechaPago->format('d/m/Y') }}</td>
                                    <td>{{ $abono->cargo->proveedor->razonSocial ?? 'N/A' }}</td>
                                    <td>{{ $abono->cargo->tipoDocumento }}
                                        {{ $abono->cargo->serieDocumento }}-{{ $abono->cargo->numeroDocumento }}</td>
                                    <td>{{ $abono->banco->nombre ?? 'N/A' }}</td>
                                    <td class="text-right font-weight-bold">{{ $abono->moneda }}
                                        {{ number_format($abono->monto, 2) }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $abono->estado->descripcion == 'Anulado' ? 'badge-danger' : 'badge-success' }}">
                                            {{ $abono->estado->descripcion ?? 'Activo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('abonos.detalle', $abono->id) }}" class="btn btn-sm btn-info"
                                            title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No hay abonos registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">{{ $abonos->links() }}</div>
        </div>
    </div>
</div>
