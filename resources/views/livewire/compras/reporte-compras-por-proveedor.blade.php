<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <label for="selectedProveedorId">Seleccionar Proveedor:</label>
                    <select class="form-control" wire:model.lazy="selectedProveedorId" id="selectedProveedorId">
                        <option value="">-- Seleccione un Proveedor --</option>
                        @foreach ($proveedores as $proveedor)
                            {{-- <option value="{{ $proveedor->id }}">{{ $proveedor->razonSocial }} ({{ $proveedor->ruc }})</option> --}}
                            <option value="{{ $proveedor->id }}">{{ $proveedor->razonSocial }}</option>
                        @endforeach
                    </select>
                    @error('selectedProveedorId') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-primary" wire:click="generarReporte">Generar Reporte</button>
                </div>
            </div>
    
            @if (session()->has('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
    
            @if ($mostrarResultados && !$compras->isEmpty())
                <div class="mb-3">
                    <button type="button" class="btn btn-success" wire:click="exportarExcel">Exportar a Excel</button>
                </div>
    
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo Doc.</th>
                                <th>Serie-Número</th>
                                <th>Fecha Emisión</th>
                                <th>Moneda</th>
                                <th>Afecto</th>
                                <th>Inafecto</th>
                                <th>IGV</th>
                                <th>Total</th>
                                <th>Estado</th>
                                </tr>
                        </thead>
                        <tbody>
                            @foreach ($compras as $compra)
                                <tr>
                                    <td>{{ $compra->id }}</td>
                                    <td>{{ $compra->tipoDocumentoR->descripcion ?? 'N/A' }}</td>
                                    <td>{{ $compra->serie }}-{{ $compra->numero }}</td>
                                    <td>{{ \Carbon\Carbon::parse($compra->fechaEmision)->format('d/m/Y') }}</td>
                                    <td>{{ $compra->moneda }}</td>
                                    <td>{{ number_format($compra->subTotal, 2) }}</td>
                                    <td>{{ number_format($compra->inafecto, 2) }}</td>
                                    <td>{{ number_format($compra->igv, 2) }}</td>
                                    <td>{{ number_format($compra->total, 2) }}</td>
                                    <td>{{ $compra->estadoR->descripcion ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Totales:</th>
                                <th>{{ number_format($compras->sum('subTotal'), 2) }}</th>
                                <th>{{ number_format($compras->sum('inafecto'), 2) }}</th>
                                <th>{{ number_format($compras->sum('igv'), 2) }}</th>
                                <th>{{ number_format($compras->sum('total'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @elseif ($mostrarResultados && $compras->isEmpty())
                <div class="alert alert-info">
                    No se encontraron compras para el proveedor seleccionado.
                </div>
            @endif
        </div>
    </div>
</div>
