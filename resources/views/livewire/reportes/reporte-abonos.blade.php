<div>
    {{-- Care about people's approval and you will be their prisoner. --}}
    <div class="card">
        <div class="card-body">
            <div class="row mb-3 align-items-end"> {{-- Usamos align-items-end para alinear los elementos --}}
                <div class="col-md-3">
                    <label for="fechaInicio">Fecha Inicio:</label>
                    <input type="date" class="form-control" id="fechaInicio" wire:model.live="fechaInicio">
                    @error('fechaInicio') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <label for="fechaFin">Fecha Fin:</label>
                    <input type="date" class="form-control" id="fechaFin" wire:model.live="fechaFin">
                    @error('fechaFin') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    {{-- No es estrictamente necesario un botón "Generar Reporte" si usas wire:model.live,
                         pero puedes mantenerlo si quieres una acción explícita.
                         Si lo dejas, wire:click="generarReporte" --}}
                    <button class="btn btn-primary" wire:click="generarReporte">Generar Reporte</button>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-success" wire:click="exportarExcel">Exportar a Excel</button>
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
    
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th wire:click="sortBy('numeroAbono')" style="cursor: pointer;">Número Abono
                                @if ($sortBy === 'numeroAbono')
                                    <i class="fa fa-sort-{{ $sortDirection }}"></i>
                                @else
                                    <i class="fa fa-sort"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('fechaAbono')" style="cursor: pointer;">Fecha Abono
                                @if ($sortBy === 'fechaAbono')
                                    <i class="fa fa-sort-{{ $sortDirection }}"></i>
                                @else
                                    <i class="fa fa-sort"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('montoTotalAbonado')" style="cursor: pointer;">Monto Abonado
                                @if ($sortBy === 'montoTotalAbonado')
                                    <i class="fa fa-sort-{{ $sortDirection }}"></i>
                                @else
                                    <i class="fa fa-sort"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('nombreCliente')" style="cursor: pointer;">Cliente
                                @if ($sortBy === 'nombreCliente')
                                    <i class="fa fa-sort-{{ $sortDirection }}"></i>
                                @else
                                    <i class="fa fa-sort"></i>
                                @endif
                            </th>
                            <th>Documentos de Cargo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($abonos as $abono)
                            <tr>
                                <td>{{ $abono->numeroAbono }}</td>
                                <td>{{ \Carbon\Carbon::parse($abono->fechaAbono)->format('d/m/Y') }}</td>
                                <td>{{ number_format($abono->montoTotalAbonado, 2) }}</td>
                                <td>{{ $abono->nombreCliente }}</td>
                                <td>{{ $abono->documentosCargos }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No se encontraron abonos en el rango de fechas seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    
            {{ $abonos->links() }}
        </div>
    </div>
</div>
