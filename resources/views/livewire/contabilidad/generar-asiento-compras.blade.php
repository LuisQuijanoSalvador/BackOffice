<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div>
        {{-- Mensajes --}}
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle mr-2"></i> {{ $message }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ $error }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-excel mr-1"></i> Generar Asiento Contable de Compras
                    (Concar)</h3>
            </div>

            <div class="card-body">
                {{-- Información del período --}}
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Período seleccionado:</strong>
                    {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al
                    {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    <br>
                    <strong>Compras disponibles:</strong> {{ $comprasCount }} registros con cuenta contable y subdiario
                    asignados.
                </div>

                {{-- Formulario de selección de fechas --}}
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="fechaInicio" class="font-weight-bold">
                                <i class="fas fa-calendar-alt mr-1"></i> Fecha Inicio
                            </label>
                            <input type="date" id="fechaInicio" wire:model="fechaInicio"
                                class="form-control @error('fechaInicio') is-invalid @enderror">
                            @error('fechaInicio')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="fechaFin" class="font-weight-bold">
                                <i class="fas fa-calendar-alt mr-1"></i> Fecha Fin
                            </label>
                            <input type="date" id="fechaFin" wire:model="fechaFin"
                                class="form-control @error('fechaFin') is-invalid @enderror">
                            @error('fechaFin')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button wire:click="generar" class="btn btn-primary btn-block btn-lg"
                            @if ($generating) disabled @endif>
                            @if ($generating)
                                <i class="fas fa-spinner fa-spin mr-2"></i> Generando...
                            @else
                                <i class="fas fa-cogs mr-2"></i> Generar
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Barra de progreso --}}
                @if ($generating)
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="font-weight-bold">
                                <i class="fas fa-hourglass-half mr-1"></i> Procesando compras...
                            </span>
                            <span class="font-weight-bold text-primary">
                                {{ $processedCompras }} / {{ $totalCompras }}
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" style="width: {{ $progress }}%;"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $progress }}%
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle mr-1"></i>
                            Generando archivo Excel con los asientos contables...
                        </small>
                    </div>
                @endif

                {{-- Botón de descarga --}}
                @if ($generatedFile)
                    <div class="mt-4">
                        <div class="alert alert-success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Archivo generado:</strong> {{ $generatedFile }}
                                </div>
                                <button wire:click="descargar" class="btn btn-success btn-lg">
                                    <i class="fas fa-download mr-2"></i> Descargar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Instrucciones --}}
                <div class="mt-4">
                    <h5 class="font-weight-bold"><i class="fas fa-lightbulb mr-1 text-warning"></i> Instrucciones:</h5>
                    <ul class="text-muted small">
                        <li>Selecciona el rango de fechas (generalmente el mes anterior).</li>
                        <li>El sistema procesará todas las compras que tengan cuenta contable y subdiario asignados.
                        </li>
                        <li>Se generarán los asientos contables según el tipo de comprobante (Factura con/sin IGV, con
                            detracción, Recibo por Honorarios).</li>
                        <li>Descarga el archivo Excel y súbelo a Concar para importar los asientos.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
