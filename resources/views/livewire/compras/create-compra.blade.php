<div>
    <div class="container py-4">
    
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    
        <div class="card shadow-sm p-4">
            <form wire:submit.prevent="saveCompra">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="tipoDocumento" class="form-label">Tipo Documento</label>
                        <select id="tipoDocumento" wire:model.live="tipoDocumento" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($tiposDocumento as $td)
                                <option value="{{ $td->id }}">{{ $td->descripcion }}</option>
                            @endforeach
                        </select>
                        @error('tipoDocumento') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="serie" class="form-label">Serie</label>
                        <input type="text" id="serie" wire:model.blur="serie" class="form-control">
                        @error('serie') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="numero" class="form-label">Número</label>
                        <input type="text" id="numero" wire:model.blur="numero" class="form-control">
                        @error('numero') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="idProveedor" class="form-label">Proveedor</label>
                        <select id="idProveedor" wire:model.live="idProveedor" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->razonSocial }}</option>
                            @endforeach
                        </select>
                        @error('idProveedor') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="idCliente" class="form-label">Cliente</label>
                        <select id="idCliente" wire:model.live="idCliente" class="form-select" disabled>
                            <option value="">Seleccione...</option>
                            @foreach ($clientes as $cli)
                                <option value="{{ $cli->id }}">{{ $cli->razonSocial }}</option>
                            @endforeach
                        </select>
                        @error('idCliente') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="numeroFile" class="form-label">Número de File</label>
                        <input type="text" id="numeroFile" wire:model.blur="numeroFile" class="form-control">
                        @error('numeroFile') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="formaPago" class="form-label">Forma de Pago</label>
                        <select id="formaPago" wire:model.live="formaPago" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($formasPago as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('formaPago') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="fechaEmision" class="form-label">Fecha de Emisión</label>
                        <input type="date" id="fechaEmision" wire:model.live="fechaEmision" class="form-control">
                        @error('fechaEmision') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="moneda" class="form-label">Moneda</label>
                        <select id="moneda" wire:model.live="moneda" class="form-select">
                            <option value="">Seleccione...</option>
                            @foreach ($monedas as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('moneda') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea id="observacion" wire:model.blur="observacion" rows="3" class="form-control"></textarea>
                        @error('observacion') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    {{-- Campo para subir PDF --}}
                    <div class="col-md-6 mb-3">
                        <label for="pdfFile">Adjuntar Factura PDF</label>
                        <input type="file" class="form-control" id="pdfFile" wire:model="pdfFile" accept="application/pdf">
                        @error('pdfFile') <span class="text-danger">{{ $message }}</span> @enderror

                        {{-- Mostrar progreso de subida --}}
                        <div x-data="{ isUploading: false, progress: 0 }"
                            x-on:livewire-upload-start="isUploading = true"
                            x-on:livewire-upload-finish="isUploading = false"
                            x-on:livewire-upload-error="isUploading = false"
                            x-on:livewire-upload-progress="progress = $event.detail.progress">
                            <div x-show="isUploading" class="progress mt-2">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" :style="`width: ${progress}%`" aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        {{-- Mostrar PDF existente en modo edición --}}
                        @if ($pdf_path_existente)
                            <div class="mt-2">
                                <p>PDF Actual: <a href="{{ Storage::url($pdf_path_existente) }}" target="_blank">Ver PDF</a></p>
                                <button type="button" class="btn btn-danger btn-sm" wire:click="removeExistingPdf">Eliminar PDF Actual</button>
                            </div>
                        @endif
                    </div>
                </div>
    
                <hr class="my-4">
    
                <h3 class="mb-3 d-flex justify-content-between align-items-center">
                    Detalles de la Compra
                    <button type="button" class="btn btn-success" wire:click="openAddDetalleModal">
                        Agregar Detalle
                    </button>
                </h3>
    
                <div class="bg-light p-4 rounded mb-4">
                    @if (empty($detalles))
                        <p class="text-center text-muted">Aún no hay detalles agregados. Haz clic en "Agregar Detalle" para empezar.</p>
                    @else
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">Cant.</th>
                                    <th style="width: 15%;">Unidad</th>
                                    <th style="width: 45%;">Descripción</th>
                                    <th style="width: 20%;">V. Unitario</th>
                                    <th style="width: 10%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detalles as $index => $detalle)
                                    <tr wire:key="detalle-row-{{ $index }}">
                                        <td>{{ $detalle['cantidad'] }}</td>
                                        <td>{{ $detalle['unidadMedida'] }}</td>
                                        <td>{{ $detalle['descripcion'] }}</td>
                                        <td>{{ number_format($detalle['valorUnitario'], 2) }}</td>
                                        <td>
                                            <button type="button" wire:click="removeDetalle({{ $index }})" class="btn btn-danger btn-sm">
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
    
                <hr class="my-4">
    
                <h3 class="mb-3">Totales</h3>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="subTotal" class="form-label">Afecto</label>
                        <input type="text" id="subTotal" wire:model="subTotal" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-3">
                        <label>Inafecto:</label>
                        <input type="text" class="form-control" value="{{ number_format($inafecto, 2) }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="igv" class="form-label">IGV</label>
                        <input type="text" id="igv" wire:model="igv" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="total" class="form-label">Total</label>
                        <input type="text" id="total" wire:model="total" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="totalLetras" class="form-label">Total en Letras</label>
                        <input type="text" id="totalLetras" wire:model="totalLetras" class="form-control bg-light" readonly>
                    </div>
                </div>
    
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        Guardar Compra
                    </button>
                </div>
            </form>
        </div>
    
        <div class="modal fade" id="detalleModal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detalleModalLabel">Agregar Detalle de Compra</h5>
                        <button type="button" class="btn-close" wire:click="closeDetalleModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="addDetalleToCompra">
                            <div class="mb-3">
                                <label for="currentDetalle.cantidad" class="form-label">Cantidad</label>
                                <input type="number" id="currentDetalle.cantidad" wire:model.blur="currentDetalle.cantidad" class="form-control">
                                @error('currentDetalle.cantidad') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currentDetalle.unidadMedida" class="form-label">Unidad</label>
                                <input type="text" id="currentDetalle.unidadMedida" wire:model.blur="currentDetalle.unidadMedida" class="form-control">
                                @error('currentDetalle.unidadMedida') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currentDetalle.descripcion" class="form-label">Descripción</label>
                                <input type="text" id="currentDetalle.descripcion" wire:model.blur="currentDetalle.descripcion" class="form-control">
                                @error('currentDetalle.descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="currentDetalle.valorUnitario" class="form-label">Valor Unitario</label>
                                <input type="number" step="0.01" id="currentDetalle.valorUnitario" wire:model.blur="currentDetalle.valorUnitario" class="form-control">
                                @error('currentDetalle.valorUnitario') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    {{-- <input type="checkbox" class="form-check-input" id="afectoIGV-{{ $index }}" wire:model.lazy="detalles.{{ $index }}.afectoIGV"> --}}
                                    <input type="checkbox" role="switch" class="form-check-input" id="currentDetalle.afectoIgv" wire:model="currentDetalle.afectoIgv">
                                    <label class="form-check-label" for="currentDetalle.afectoIGV">Afecto IGV</label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeDetalleModal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Aceptar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
        <script>
            // Inicializar el modal de Bootstrap
            var detalleModal = new bootstrap.Modal(document.getElementById('detalleModal'), {
                keyboard: false
            });
        
            // Escuchar eventos de Livewire para mostrar/ocultar el modal
            Livewire.on('show-detalle-modal', () => {
                detalleModal.show();
            });
        
            Livewire.on('hide-detalle-modal', () => {
                detalleModal.hide();
            });
        
            // Opcional: Escuchar el error de "no-detalles" para dar feedback extra
            Livewire.on('no-detalles-error', () => {
                // Puedes, por ejemplo, resaltar el botón "Agregar Detalle"
                // o hacer un scroll suave hasta la sección de detalles.
                const detalleSection = document.querySelector('.bg-light.p-4.rounded.mb-4');
                if (detalleSection) {
                    detalleSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    detalleSection.classList.add('border', 'border-danger'); // Añadir un borde rojo temporal
                    setTimeout(() => {
                        detalleSection.classList.remove('border', 'border-danger');
                    }, 3000);
                }
            });
        </script>
    @endpush
</div>
