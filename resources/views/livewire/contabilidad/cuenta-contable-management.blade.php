<div>
    {{-- Do your work, then step back. --}}
    <div>
        {{-- Mensajes Flash --}}
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-book mr-1"></i> Mantenimiento de Cuentas Contables</h3>
                <button wire:click="create()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nueva Cuenta
                </button>
            </div>

            <div class="card-body">
                {{-- Buscador --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" wire:model.debounce.300ms="search" class="form-control"
                                placeholder="Buscar por cuenta o descripción...">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Cuenta</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Creado por</th>
                                <th style="width: 100px" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cuentas as $index => $cuenta)
                                <tr>
                                    <td>{{ $cuentas->firstItem() + $index }}</td>
                                    <td class="font-weight-bold">{{ $cuenta->cuenta }}</td>
                                    <td>{{ $cuenta->descripcion }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $cuenta->estado->descripcion == 'Activo' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $cuenta->estado->descripcion ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td><small>{{ $cuenta->userCreacion->name ?? 'Sistema' }}</small></td>
                                    <td class="text-center">
                                        <button wire:click="edit({{ $cuenta->id }})" class="btn btn-xs btn-warning"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="delete({{ $cuenta->id }})"
                                            wire:confirm="¿Estás seguro de eliminar esta cuenta contable?"
                                            class="btn btn-xs btn-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No se encontraron cuentas
                                        contables.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer clearfix">
                {{ $cuentas->links() }}
            </div>
        </div>

        {{-- MODAL DE CREAR / EDITAR (Bootstrap 4) --}}
        <div wire:ignore.self class="modal fade" id="cuentaModal" tabindex="-1" role="dialog"
            aria-labelledby="cuentaModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="cuentaModalLabel">
                            <i class="fas fa-book mr-1"></i>
                            {{ $cuentaId ? 'Editar Cuenta Contable' : 'Nueva Cuenta Contable' }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="store">
                            <div class="form-group">
                                <label for="cuenta" class="font-weight-bold">Número de Cuenta <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="cuenta" wire:model="cuenta"
                                    class="form-control @error('cuenta') is-invalid @enderror"
                                    placeholder="Ej: 40111001">
                                @error('cuenta')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="descripcion" class="font-weight-bold">Descripción <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="descripcion" wire:model="descripcion"
                                    class="form-control @error('descripcion') is-invalid @enderror"
                                    placeholder="Ej: Tributos por pagar - IGV">
                                @error('descripcion')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-secondary mr-2"
                                    data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> {{ $cuentaId ? 'Actualizar' : 'Guardar' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
