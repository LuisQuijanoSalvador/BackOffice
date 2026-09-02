<div>
    {{-- In work, do what you enjoy. --}}
    <div>
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show">{{ $message }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif
        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show">{{ $error }}<button type="button"
                    class="close" data-dismiss="alert"><span>&times;</span></button></div>
        @endif

        <div class="card card-warning card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Registro Manual de Cargo (Doc.
                    Cobranza / GDS)</h3>
                <a href="{{ route('rListaCargos') }}" class="btn btn-sm btn-default"><i
                        class="fas fa-arrow-left"></i> Volver al Listado</a>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="guardar">
                    {{-- Datos del Documento y Proveedor --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Proveedor (Mayorista / IATA) <span
                                        class="text-danger">*</span></label>
                                <select wire:model="proveedorId"
                                    class="form-control @error('proveedorId') is-invalid @enderror">
                                    <option value="">Seleccione Proveedor</option>
                                    @foreach ($proveedores as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->razonSocial }}</option>
                                    @endforeach
                                </select>
                                @error('proveedorId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Tipo Documento</label>
                                <input type="text" wire:model="tipoDocumento" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Serie <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="serieDocumento"
                                            class="form-control @error('serieDocumento') is-invalid @enderror">
                                        @error('serieDocumento')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Número <span
                                                class="text-danger">*</span></label>
                                        <input type="text" wire:model="numeroDocumento"
                                            class="form-control @error('numeroDocumento') is-invalid @enderror">
                                        @error('numeroDocumento')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fechas y Moneda --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha Emisión <span class="text-danger">*</span></label>
                                <input type="date" wire:model="fechaEmision" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Días Crédito</label>
                                <input type="number" wire:model="diasCredito" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Fecha Vencimiento</label>
                                <input type="date" wire:model="fechaVencimiento" class="form-control bg-light"
                                    readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Moneda</label>
                                        <select wire:model="moneda" class="form-control">
                                            <option value="PEN">PEN</option>
                                            <option value="USD">USD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tipo Cambio</label>
                                        <input type="number" step="0.001" wire:model="tipoCambio"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Montos --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Tarifa Neta (Gravado)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                    <input type="number" step="0.01" wire:model="tarifaNeta" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Inafecto / Exonerado</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                    <input type="number" step="0.01" wire:model="inafecto" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">IGV</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                    <input type="number" step="0.01" wire:model="igv" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Otros Impuestos</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">S/</span></div>
                                    <input type="number" step="0.01" wire:model="otrosImpuestos"
                                        class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Observación (Opcional)</label>
                                <input type="text" wire:model="observacion" class="form-control"
                                    placeholder="Ej: Consolidado boletos GDS del 01 al 15">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">TOTAL A PAGAR</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span
                                            class="input-group-text bg-warning text-white font-weight-bold">S/</span>
                                    </div>
                                    <input type="text" class="form-control bg-light font-weight-bold text-dark h5"
                                        value="{{ number_format($total, 2) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-warning btn-lg" wire:loading.attr="disabled">
                            <span wire:loading class="spinner-border spinner-border-sm mr-1" role="status"
                                aria-hidden="true"></span>
                            <i class="fas fa-save mr-1" wire:loading.remove></i>
                            Registrar Cargo Manual
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
