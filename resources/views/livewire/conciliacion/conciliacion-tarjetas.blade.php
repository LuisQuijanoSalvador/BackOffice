<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div>
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card mr-1"></i> Conciliación de Tarjetas de Crédito</h3>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="procesar">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Estado de Cuenta (PDF) <span
                                        class="text-danger">*</span></label>
                                <input type="file" wire:model="pdfFile" class="form-control-file" accept=".pdf">
                                @error('pdfFile')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Formatos: Interbank, Scotiabank, BBVA, Diners Club</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Últimos 4 Dígitos de la TC <span
                                        class="text-danger">*</span></label>
                                <input type="text" wire:model="lastFourDigits" class="form-control"
                                    placeholder="Ej: 2672" maxlength="4">
                                @error('lastFourDigits')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fechaInicio" class="form-control">
                                @error('fechaInicio')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha Fin <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fechaFin" class="form-control">
                                @error('fechaFin')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm mr-1"></span>
                            <i class="fas fa-search mr-1" wire:loading.remove></i>
                            Procesar Conciliación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (count($results) > 0)
            <div class="card card-success card-outline mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-double mr-1"></i> Resultados de la Conciliación
                    </h3>
                    @if ($pdfFilename)
                        <button wire:click="descargarPdf" class="btn btn-success btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i> Descargar Reporte PDF
                        </button>
                    @endif
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha TC</th>
                                    <th>Descripción</th>
                                    <th class="text-right">Monto</th>
                                    <th>Moneda</th>
                                    <th>Tipo</th>
                                    <th>Cliente</th>
                                    <th>N° Boleto</th>
                                    <th>Documento</th>
                                    <th>Tipo Servicio</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $result)
                                    <tr class="{{ $result['coincidencia'] ? 'table-success' : 'table-danger' }}">
                                        <td>{{ \Carbon\Carbon::parse($result['fecha_transaccion'])->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $result['descripcion_tarjeta'] }}</td>
                                        <td class="text-right">{{ number_format($result['monto_tarjeta'], 2) }}</td>
                                        <td>{{ $result['moneda_tarjeta'] ?? 'PEN' }}</td>
                                        <td>{{ $result['tipo'] ?? '-' }}</td>
                                        <td>{{ $result['cliente'] ?? '-' }}</td>
                                        <td>{{ $result['numero_boleto'] ?? '-' }}</td>
                                        <td>{{ $result['serie_documento'] ?? '-' }}-{{ $result['numero_documento'] ?? '-' }}
                                        </td>
                                        <td>{{ $result['tipo_servicio'] ?? '-' }}</td>
                                        <td class="text-center">
                                            @if ($result['coincidencia'])
                                                <span class="badge badge-success"><i class="fas fa-check"></i>
                                                    Match</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-times"></i> Sin
                                                    match</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Movimientos</span>
                                    <span class="info-box-number">{{ count($results) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Coincidencias</span>
                                    <span
                                        class="info-box-number">{{ collect($results)->where('coincidencia', true)->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sin Coincidencia</span>
                                    <span
                                        class="info-box-number">{{ collect($results)->where('coincidencia', false)->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
