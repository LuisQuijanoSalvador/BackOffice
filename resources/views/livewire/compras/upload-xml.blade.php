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

            {{-- Botón de envío --}}
            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-primary" @if($loading || !$xmlFile || empty($medioPagoId) || ($medioPagoId != 10 && empty($bancoId))) disabled @endif>
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
