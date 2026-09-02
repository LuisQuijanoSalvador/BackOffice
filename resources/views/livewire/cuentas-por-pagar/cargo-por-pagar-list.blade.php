<div>
    {{-- Be like water. --}}
    <div>
        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-1"></i> Cuentas por Pagar</h3>

                <div class="d-flex">
                    {{-- Botón para Crear Cargo Manual (Doc. Cobranza / GDS) --}}
                    <a href="{{ route('cuentas-por-pagar.crear') }}" class="btn btn-warning btn-sm mr-2">
                        <i class="fas fa-plus-circle mr-1"></i> Nuevo Cargo Manual
                    </a>

                    {{-- Botón Pagar Seleccionados --}}
                    @if (count($selectedCargos) > 0)
                        <a href="{{ route('cuentas-por-pagar.pago', ['cargos' => implode(',', $selectedCargos)]) }}"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Pagar Seleccionados
                            ({{ count($selectedCargos) }})
                        </a>
                    @else
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-hand-holding-usd mr-1"></i> Pagar
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body">
                {{-- Filtros --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Fecha Inicio</label>
                            <input type="date" wire:model="fechaInicio" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Fecha Fin</label>
                            <input type="date" wire:model="fechaFin" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Buscar</label>
                            <input type="text" wire:model.debounce.300ms="search"
                                class="form-control form-control-sm" placeholder="Proveedor, RUC, serie o número...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 30px" class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" wire:model="selectAll" class="custom-control-input"
                                            id="selectAll">
                                        <label class="custom-control-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th style="width: 10px">#</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th>Moneda</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargos as $cargo)
                                <tr>
                                    <td class="text-center align-middle">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" value="{{ (string) $cargo->id }}"
                                                wire:model="selectedCargos" class="custom-control-input"
                                                id="check-{{ $cargo->id }}">
                                            <label class="custom-control-label" for="check-{{ $cargo->id }}"></label>
                                        </div>
                                    </td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cargo->fechaEmision->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="{{ $cargo->fechaVencimiento->isPast() ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $cargo->fechaVencimiento->format('d/m/Y') }}
                                            @if ($cargo->fechaVencimiento->isPast())
                                                <i class="fas fa-exclamation-triangle ml-1"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $cargo->proveedor->razonSocial ?? 'N/A' }}</td>
                                    <td>{{ $cargo->tipoDocumento }}
                                        {{ $cargo->serieDocumento }}-{{ $cargo->numeroDocumento }}</td>
                                    <td>{{ $cargo->moneda }}</td>
                                    <td class="text-right">{{ number_format($cargo->total, 2) }}</td>
                                    <td class="text-right font-weight-bold text-warning">
                                        {{ number_format($cargo->saldo, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No se encontraron cargos
                                        pendientes en este rango de fechas.</td>
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
