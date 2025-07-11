<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label for="cboCounter" class="form-label">Counter:</label>
                    <select name="idCounter" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboCounter" wire:model="idCounter">
                        @foreach ($counters as $counter)
                            <option value={{$counter->id}}>{{$counter->nombre}}</option>
                        @endforeach
                    </select>
                    @error('idCounter')
                        <span class="error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <label for="cboCounter" class="form-label">Area:</label>
                    <select name="idArea" style="width: 100%; display:block;font-size: 0.8em;" class="" id="cboArea" wire:model="idArea">
                        @foreach ($areas as $area)
                            <option value={{$area->id}}>{{$area->descripcion}}</option>
                        @endforeach
                    </select>
                    @error('idArea')
                        <span class="error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <div class="col-md-2">
                            <input type="checkbox" class=" mt-16" name="chkFile" id="chkFile" wire:model="checkFile">
                        </div>
                        <div class="col-md-10">
                            <label for="txtFile" class="form-label">File:</label>
                            <input type="text" class="uTextBox" name="txtFile" id="txtFile" wire:model="numeroFile" @if (!$checkFile) disabled @endif style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
                        </div>
                    </div>
                    
                </div>
            </div>
            <br>
            <div class="mb-3">
                <label class="form-label d-block">Obtener Boleto desde:</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="sourceApi" value="api" wire:model.live="source">
                    <label class="form-check-label" for="sourceApi">API</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="sourceFile" value="file" wire:model.live="source">
                    <label class="form-check-label" for="sourceFile">Archivo JSON</label>
                </div>
                @error('source') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            @if ($source === 'api')
                <div class="mb-3">
                    <label for="ticketNumber" class="form-label">Número de Boleto:</label>
                    <input type="text" class="form-control" id="ticketNumber" wire:model.lazy="ticketNumber" placeholder="Ingrese el número de boleto">
                    @error('ticketNumber') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            @endif
            
            @if ($source === 'file')
            <div class="mb-3">
                <label for="jsonFile" class="form-label">Subir Archivo JSON:</label>
                <input type="file" class="form-control" id="jsonFile" wire:model="jsonFile" accept=".json">
                @error('jsonFile') <span class="text-danger">{{ $message }}</span> @enderror
                <div wire:loading wire:target="jsonFile" class="text-muted mt-2">Cargando archivo...</div>
            </div>
        @endif
    
            <button class="btn btn-primary" wire:click="buscarBoleto" wire:loading.attr="disabled">
                <span wire:loading wire:target="buscarBoleto" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span wire:loading.remove wire:target="buscarBoleto">Obtener Boleto</span>
                <span wire:loading wire:target="buscarBoleto">Buscando...</span>
            </button>
    
            @if (session()->has('success'))
                <div class="alert alert-success mt-3">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger mt-3">{{ session('error') }}</div>
            @endif
    
            {{-- Mostrar resultados --}}
            @if ($isLoading)
                <div class="mt-4 text-center">
                    <p>Cargando datos del boleto...</p>
                </div>
            @elseif ($ticketData)
                <div class="mt-4 p-3 border rounded bg-light">
                    <h4>Datos del Boleto Encontrado:</h4>
                    {{-- Aquí deberás iterar y mostrar los datos del boleto.
                         La estructura de $ticketData dependerá de lo que la API te devuelva.
                         Por ahora, lo mostraremos en formato JSON legible.
                         Una vez que tengas una respuesta exitosa real, podrás parsearla mejor. --}}
                    <pre class="bg-white p-2 rounded" style="max-height: 400px; overflow-y: auto;">{{ json_encode($ticketData, JSON_PRETTY_PRINT) }}</pre>
    
                    {{-- EJEMPLO: Si sabes la estructura, puedes acceder a campos específicos --}}
                    {{--
                    <p><strong>Número de Boleto:</strong> {{ $ticketData['TicketNumber'] ?? 'N/A' }}</p>
                    <p><strong>Pasajero:</strong> {{ $ticketData['PassengerName'] ?? 'N/A' }}</p>
                    <p><strong>Fecha de Emisión:</strong> {{ \Carbon\Carbon::parse($ticketData['IssueDate'] ?? '')->format('d/m/Y') }}</p>
                    --}}
                </div>
            @elseif ($errorMessage)
                <div class="alert alert-warning mt-4">
                    <strong>Error:</strong> {{ $errorMessage }}
                    <br>
                    Por favor, verifique el número de boleto o intente de nuevo.
                </div>
            @endif
        </div>
    </div>
</div>
