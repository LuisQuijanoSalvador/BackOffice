<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <div>
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-bookmark mr-1"></i> Mantenimiento de Subdiarios</h3>
                <button wire:click="create()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo Subdiario
                </button>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" wire:model.debounce.300ms="search" class="form-control"
                                placeholder="Buscar por código o descripción...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th style="width: 100px" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subdiarios as $index => $subdiario)
                                <tr>
                                    <td>{{ $subdiarios->firstItem() + $index }}</td>
                                    <td class="font-weight-bold">{{ $subdiario->codigo }}</td>
                                    <td>{{ $subdiario->descripcion }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $subdiario->estado->descripcion == 'Activo' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $subdiario->estado->descripcion ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="edit({{ $subdiario->id }})" class="btn btn-xs btn-warning"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $subdiario->id }})"
                                            class="btn btn-xs btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No se encontraron subdiarios.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer clearfix">
                {{ $subdiarios->links() }}
            </div>
        </div>

        {{-- MODAL CREAR/EDITAR --}}
        <div wire:ignore.self class="modal fade" id="subdiarioModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-bookmark mr-1"></i>
                            {{ $subdiarioId ? 'Editar Subdiario' : 'Nuevo Subdiario' }}</h5>
                        <button type="button" class="close text-white"
                            data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="store">
                            <div class="form-group">
                                <label class="font-weight-bold">Código <span class="text-danger">*</span></label>
                                <input type="text" wire:model="codigo"
                                    class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: 01">
                                @error('codigo')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Descripción <span class="text-danger">*</span></label>
                                <input type="text" wire:model="descripcion"
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    placeholder="Ej: Compras Nacionales">
                                @error('descripcion')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary mr-2"
                                    data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>
                                    {{ $subdiarioId ? 'Actualizar' : 'Guardar' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL ELIMINAR --}}
        <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-1"></i> Confirmar Eliminación
                        </h5>
                        <button type="button" class="close text-white"
                            data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">¿Estás seguro de que deseas eliminar este subdiario? Esta acción no se puede
                            deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete"
                            data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                        <button type="button" class="btn btn-danger" wire:click="delete"><i
                                class="fas fa-trash mr-1"></i> Sí, Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
