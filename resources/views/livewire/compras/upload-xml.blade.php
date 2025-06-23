<div>
    {{-- The Master doesn't talk, he acts. --}}
    <div class="p-3">
        @if ($message)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    
        @if ($error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    
        <form wire:submit.prevent="processXml">
            <div class="mb-3">
                <label for="xmlFile" class="form-label">Seleccionar Archivo XML</label>
                <input type="file" id="xmlFile" wire:model="xmlFile" class="form-control @error('xmlFile') is-invalid @enderror">
                @error('xmlFile') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
    
            @if ($xmlFile)
                <div class="mb-3 text-muted small">
                    Archivo seleccionado: {{ $xmlFile->getClientOriginalName() }} ({{ round($xmlFile->getSize() / 1024, 1) }} KB)
                </div>
            @endif
    
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary" @if($loading) disabled @endif>
                    @if($loading)
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Procesando...
                    @else
                        Subir y Procesar XML
                    @endif
                </button>
            </div>
        </form>
    </div>
</div>
