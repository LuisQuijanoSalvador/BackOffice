<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
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
    {{-- <div class="row">
        <div style="display: flex; gap: 10px;">
            <span>Filtrar por: </span>
            <label>
                <input type="radio" wire:model="filtro" value="fechas"> Fechas
            </label>
            <label>
                <input type="radio" wire:model="filtro" value="proveedor"> Proveedor
            </label>
            <label>
                <input type="radio" wire:model="filtro" value="documento"> Documento
            </label>
        </div>
    </div>
    <hr>
    <div class="row" style="display: {{ $filtro === 'fechas' ? 'flex' : 'none' }};">
        <div class="col-md-2">
            <p style="text-align:right">F. inicio:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="fechaInicio" id="fechaInicio">
        </div>
        <div class="col-md-2">
            <p style="text-align:right">F. Final:</p>
        </div>
        <div class="col-md-2">
            <input type="date" wire:model.lazy.defer="fechaFin" id="fechaFin">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div>
    <div class="row" style="display: {{ $filtro === 'proveedor' ? 'flex' : 'none' }};">
        <select name="selectedProveedor" style="width: 70%; display:block;font-size: 0.9em; height:31px;" class="rounded" id="cboProvedor" wire:model="filtroProveedor">
            <option value="">-- Filtrar por Proveedor --</option>
            @foreach ($proveedores as $proveedor)
                <option value="{{$proveedor->id}}">{{$proveedor->razonSocial}}</option>
            @endforeach
        </select>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div>
    <div class="row" style="display: {{ $filtro === 'documento' ? 'flex' : 'none' }};">
        <div class="col-md-6">
            <input type="text" name="" id="txtDocumento" wire:model="filtroDocumento">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" wire:click="filtrar" >Filtrar</button>
        </div>
    </div> --}}
    <hr>

    <div class="row justify-content-between">
        <div class="col-md-2">
            <a href="{{ route('nuevaCompra') }}">
                <button type="button" class="btn btn-primary rounded">Nuevo</button>
            </a>
        </div>
        {{-- <div class="col-md-2">
            <button type="button" class="btn btn-success" wire:click='exportar'>Exportar</button>
        </div> --}}
        <div class="col-md-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadXmlModal">
                <i class="fa-solid fa-file-upload me-2"></i> Subir Archivo XML
            </button>
        </div>
    </div>
    
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="col-md-4">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar por número, serie o proveedor...">
            </div>
            <div class="col-md-2">
                <select wire:model.live="perPage" class="form-select">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover caption-top">
                <caption>Listado de Compras ({{ $compras->total() }} registros)</caption>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Proveedor</th>
                        <th>Tipo Doc.</th>
                        <th>Documento</th>
                        <th>Fecha Emisión</th>
                        <th>Afecto</th>
                        <th>Inafecto</th>
                        <th>IGV</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($compras as $compra)
                        <tr>
                            <td>{{ $compra->id }}</td>
                            <td>{{ $compra->proveedor->razonSocial ?? 'N/A' }}</td>
                            <td>{{ $compra->tipoDocumentoR->descripcion ?? 'N/A' }}</td>
                            {{-- <td>{{ $compra->serie }}</td>
                            <td>{{ $compra->numero }}</td> --}}
                            <td>{{ $compra->serie }}-{{ $compra->numero }}</td> {{-- CONCATENAMOS AQUÍ --}}
                            <td>{{ $compra->fechaEmision->format('d/m/Y') }}</td>
                            <td>{{ number_format($compra->subTotal, 2) }}</td>
                            <td>{{ number_format($compra->inafecto, 2) }}</td>
                            <td>{{ number_format($compra->igv, 2) }}</td>
                            <td>{{ number_format($compra->total, 2) }}</td>
                            <td>
                                <span class="badge {{ $compra->estadoR->descripcion == 'Anulado' ? 'bg-danger' : ($compra->estadoR->nombre == 'Activo' ? 'bg-success' : 'bg-secondary') }}">
                                    {{ $compra->estadoR->descripcion ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                {{-- Botón de Editar --}}
                                <a href="{{ route('editarCompra', $compra->id) }}" class="btn btn-warning btn-sm me-1" title="Editar">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                {{-- Botón de Anular (condicional) --}}
                                @if($compra->estadoR->nombre != 'Anulado')
                                    <button type="button" class="btn btn-info btn-sm me-1" wire:click="confirmAction({{ $compra->id }}, 'anular')" title="Anular">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @endif

                                {{-- Botón de Eliminar --}}
                                <button type="button" class="btn btn-danger btn-sm" wire:click="confirmAction({{ $compra->id }}, 'eliminar')" title="Eliminar">
                                    <i class="fa-solid fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">No se encontraron compras.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $compras->links() }} {{-- Renderizar los enlaces de paginación --}}
        </div>
    </div>

    <div class="modal fade" id="confirmActionModal" tabindex="-1" aria-labelledby="confirmActionModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmActionModalLabel">Confirmar {{ $actionType == 'anular' ? 'Anulación' : 'Eliminación' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeConfirmModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($actionType == 'anular')
                        ¿Está seguro de que desea **anular** la compra seleccionada? Esto cambiará su estado a "Anulado" pero mantendrá el registro.
                    @elseif ($actionType == 'eliminar')
                        ¿Está seguro de que desea **eliminar permanentemente** la compra seleccionada? Esta acción no se puede deshacer y también eliminará sus detalles.
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeConfirmModal">Cancelar</button>
                    @if ($actionType == 'anular')
                        <button type="button" class="btn btn-info" wire:click="anularCompra">Anular</button>
                    @elseif ($actionType == 'eliminar')
                        <button type="button" class="btn btn-danger" wire:click="deleteCompra">Eliminar</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadXmlModal" tabindex="-1" aria-labelledby="uploadXmlModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadXmlModalLabel">Subir Archivo XML de Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Aquí se renderizará el componente Livewire de subida de XML --}}
                    @livewire('compras.upload-xml')
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
<script>
    // Inicializar el modal de confirmación de Bootstrap
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmActionModal'), {
        keyboard: false
    });

    // Escuchar eventos de Livewire para mostrar/ocultar el modal de confirmación
    Livewire.on('show-confirm-modal', () => {
        confirmModal.show();
    });

    Livewire.on('hide-confirm-modal', () => {
        confirmModal.hide();
    });

    // Nuevo: Escuchar un evento para cerrar el modal de subida de XML
    Livewire.on('close-upload-xml-modal', () => {
        var uploadXmlModal = bootstrap.Modal.getInstance(document.getElementById('uploadXmlModal'));
        if (uploadXmlModal) {
            uploadXmlModal.hide();
        }
    });
</script>
@endpush
