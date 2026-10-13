<div>
    {{-- The Master doesn't talk, he acts. --}}
    <div class="p-3">
        {{-- Mensaje de éxito (Corregido a Bootstrap 4) --}}
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Mensaje de error (Corregido a Bootstrap 4) --}}
        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form wire:submit.prevent="processXml">
            <div class="row">
                {{-- Columna 1: Archivo XML --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="xmlFile" class="font-weight-bold">Seleccionar Archivo XML</label>
                        <input type="file" id="xmlFile" wire:model="xmlFile"
                            class="form-control-file @error('xmlFile') is-invalid @enderror"
                            accept=".xml,application/xml,text/xml">
                        @error('xmlFile')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Información del archivo seleccionado --}}
            @if ($xmlFile)
                <div class="mb-3 text-muted small">
                    <i class="fa fa-file-code-o"></i>
                    Archivo seleccionado: <strong>{{ $xmlFile->getClientOriginalName() }}</strong>
                    ({{ round($xmlFile->getSize() / 1024, 1) }} KB)
                </div>
            @endif

            <div class="row">
                {{-- Columna 2: Medio de Pago --}}
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="medioPagoId" class="font-weight-bold">Medio de Pago</label>
                        <select id="medioPagoId" wire:model="medioPagoId"
                            class="form-control @error('medioPagoId') is-invalid @enderror">
                            <option value="">Seleccione</option>
                            @foreach ($mediosPago as $medioPago)
                                <option value="{{ $medioPago->id }}">{{ $medioPago->descripcion }}</option>
                            @endforeach
                        </select>
                        @error('medioPagoId')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- Columna 3: Banco / Cuenta (Solo visible/activo si NO es Crédito) --}}
                @if ($medioPagoId && $medioPagoId != 10)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="bancoId" class="font-weight-bold">Banco / Cuenta de Origen</label>
                                <select id="bancoId" wire:model="bancoId"
                                    class="form-control @error('bancoId') is-invalid @enderror">
                                    <option value="">Seleccione Banco</option>
                                    @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id }}">{{ $banco->nombre }} -
                                            {{ $banco->cuenta ?? '' }}</option>
                                    @endforeach
                                </select>
                                @error('bancoId')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="idCuentaContable" class="font-weight-bold">Cuenta Contable <span
                                class="text-danger">*</span></label>
                        <select id="idCuentaContable" wire:model="idCuentaContable"
                            class="form-control @error('idCuentaContable') is-invalid @enderror">
                            <option value="">Seleccione Cuenta</option>
                            @foreach ($cuentasContables as $cuenta)
                                <option value="{{ $cuenta->id }}">{{ $cuenta->cuenta }} -
                                    {{ $cuenta->descripcion }}</option>
                            @endforeach
                        </select>
                        @error('idCuentaContable')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="idSubdiario" class="font-weight-bold">Subdiario <span
                                class="text-danger">*</span></label>
                        <select id="idSubdiario" wire:model="idSubdiario"
                            class="form-control @error('idSubdiario') is-invalid @enderror">
                            <option value="">Seleccione Subdiario</option>
                            @foreach ($subdiarios as $subdiario)
                                <option value="{{ $subdiario->id }}">{{ $subdiario->codigo }} -
                                    {{ $subdiario->descripcion }}</option>
                            @endforeach
                        </select>
                        @error('idSubdiario')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            
            {{-- Botón de envío --}}
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" @if ($loading || !$xmlFile || empty($medioPagoId) || ($medioPagoId != 10 && empty($bancoId))) disabled @endif>
                    @if ($loading)
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Procesando...
                    @else
                        <i class="fa fa-upload"></i> Subir y Procesar XML
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>
