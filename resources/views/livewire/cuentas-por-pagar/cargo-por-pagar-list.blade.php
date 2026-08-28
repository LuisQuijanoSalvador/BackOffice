<div>
    {{-- Be like water. --}}
    <div>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Cuentas por Pagar</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" wire:model.debounce.300ms="search" class="form-control float-right"
                            placeholder="Buscar proveedor, serie o número...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th>Moneda</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Saldo Pendiente</th>
                                <th style="width: 120px" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargos as $cargo)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cargo->fechaEmision->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="{{ $cargo->fechaVencimiento->isPast() && $cargo->saldo > 0 ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $cargo->fechaVencimiento->format('d/m/Y') }}
                                            @if ($cargo->fechaVencimiento->isPast() && $cargo->saldo > 0)
                                                <i class="fas fa-exclamation-triangle ml-1" title="Vencido"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $cargo->proveedor->razonSocial ?? 'N/A' }}</td>
                                    <td>{{ $cargo->tipoDocumento }}
                                        {{ $cargo->serieDocumento }}-{{ $cargo->numeroDocumento }}</td>
                                    <td>{{ $cargo->moneda }}</td>
                                    <td class="text-right">{{ number_format($cargo->total, 2) }}</td>
                                    <td
                                        class="text-right font-weight-bold {{ $cargo->saldo <= 0 ? 'text-success' : 'text-warning' }}">
                                        {{ number_format($cargo->saldo, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($cargo->saldo > 0)
                                            <a href="{{ route('cuentas-por-pagar.pago', $cargo->id) }}"
                                                class="btn btn-sm btn-success" title="Registrar Pago">
                                                <i class="fas fa-money-bill-wave"></i> Pagar
                                            </a>
                                        @else
                                            <span class="badge badge-success">Pagado</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No se encontraron cuentas por pagar registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer clearfix">
                {{ $cargos->links() }}
            </div>
        </div>
    </div>
</div>
