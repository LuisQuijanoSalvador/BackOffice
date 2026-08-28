<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div>
        {{-- Filtros --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de Búsqueda</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaInicio" class="font-weight-bold">Fecha Inicio</label>
                            <input type="date" id="fechaInicio" wire:model="fechaInicio" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaFin" class="font-weight-bold">Fecha Fin</label>
                            <input type="date" id="fechaFin" wire:model="fechaFin" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bancoId" class="font-weight-bold">Banco</label>
                            <select id="bancoId" wire:model="bancoId" class="form-control">
                                <option value="">Todos los bancos</option>
                                @foreach ($bancos as $banco)
                                    <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="proveedorId" class="font-weight-bold">Proveedor</label>
                            <select id="proveedorId" wire:model="proveedorId" class="form-control">
                                <option value="">Todos los proveedores</option>
                                @foreach ($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->razonSocial }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="search" class="font-weight-bold">Buscar</label>
                            <input type="text" id="search" wire:model.debounce.300ms="search" class="form-control"
                                placeholder="Proveedor, RUC, serie o número de documento...">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button wire:click="limpiarFiltros" class="btn btn-secondary btn-block">
                            <i class="fas fa-eraser"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen y Exportación --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Registros</span>
                        <span class="info-box-number">{{ $totalRegistros }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Monto Total (Página)</span>
                        <span class="info-box-number">S/ {{ number_format($totalMonto, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-end">
                <button wire:click="exportarExcel" class="btn btn-success btn-lg">
                    <i class="fas fa-file-excel mr-2"></i> Exportar a Excel
                </button>
            </div>
        </div>

        {{-- Tabla de Egresos --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Listado de Egresos</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Fecha Registro</th>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th>Banco</th>
                                <th>Moneda</th>
                                <th class="text-right">Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($egresos as $egreso)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $egreso->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $egreso->documento->proveedor->razonSocial ?? 'N/A' }}</td>
                                    <td>
                                        {{ $egreso->documento->tipoDocumento->nombre ?? 'N/A' }}
                                        {{ $egreso->documento->serie ?? '' }}-{{ $egreso->documento->numero ?? '' }}
                                    </td>
                                    <td>{{ $egreso->banco->nombre ?? 'N/A' }}</td>
                                    <td>{{ $egreso->moneda }}</td>
                                    <td class="text-right font-weight-bold text-danger">
                                        {{ number_format($egreso->monto, 2) }}
                                    </td>
                                    <td>
                                        <span
                                            class="badge badge-success">{{ $egreso->estado->descripcion ?? 'Activo' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No se encontraron egresos con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                {{ $egresos->links() }}
            </div>
        </div>
    </div>
</div>
