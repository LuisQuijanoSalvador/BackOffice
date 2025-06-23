<div>
    {{-- Success is as dangerous as failure. --}}
    <div class="div-filtro">
        <input type="text" class="txtFiltro" wire:model="search" placeholder="Filtrar por Razon Social">
        <div>
            {{-- <button type="button" class="btn btn-success" wire:click='exportar'>Exportar</button> --}}
            <button type="button" class="btn btn-primary rounded" data-bs-toggle="modal"
                data-bs-target="#FormularioModal">Nuevo</button>
        </div>

    </div>
    <table class="tabla-listado">
        <thead class="thead-listado">
            <tr>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('id')">
                    ID
                    @if ($sort == 'id')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('razonSocial')">
                    Razon Social
                    @if ($sort == 'razonSocial')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('numeroDocumentoIdentidad')">
                    Num. Doc.
                    @if ($sort == 'numeroDocumentoIdentidad')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('numeroTelefono')">
                    Telefono
                    @if ($sort == 'numeroTelefono')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('correo')">
                    Correo
                    @if ($sort == 'correo')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('estado')">
                    Estado
                    @if ($sort == 'estado')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 thAccion">
                    Acción
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($proveedors as $proveedor)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="py-1">{{ $proveedor->id }}</td>
                    <td class="py-1">{{ $proveedor->razonSocial }}</td>
                    <td class="py-1">{{ $proveedor->numeroDocumentoIdentidad }}</td>
                    <td class="py-1">{{ $proveedor->numeroTelefono }}</td>
                    <td class="py-1">{{ $proveedor->correo }}</td>
                    <td class="py-1">{{ $proveedor->tEstado->descripcion }}</td>
                    <td class="py-1">
                        <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                            <button type="button" class="btn btn-outline-primary mr-2 rounded" data-bs-toggle="modal"
                                data-bs-target="#FormularioModal"
                                wire:click='editar("{{ $proveedor->id }}")'>Editar</button>
                            <button type="button" class="btn btn-danger rounded" data-bs-toggle="modal"
                                data-bs-target="#ModalEliminacion"
                                wire:click='encontrar("{{ $proveedor->id }}")'>Eliminar</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $proveedors->links() }}

    {{-- Modal para Insertar y Actualizar --}}
    @include('components.modalheaderxl')
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="accordion accordion-flush" id="accordionFlushExample">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed text-bg-primary" type="button" data-bs-toggle="collapse"
                    data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                    Datos
                </button>
            </h2>
            <div id="flush-collapseOne" class="accordion-collapse show" data-bs-parent="#accordionFlushExample">
                <div class="accordion-body">
                    <form class="row g-3">
                        <div class="col-md-5">
                            <label for="txtRazonSocial" class="form-label">RazonSocial:</label>
                            <input type="text" class="form-control" id="txtRazonSocial" wire:model.lazy="razonSocial"
                                style="text-transform:uppercase;"
                                onkeyup="javascript:this.value=this.value.toUpperCase();">
                            @error('razonSocial')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="cboTipoDoc" class="form-label">Tipo Documento:</label>
                            <select name="tipoDocumentoIdentidad" class="form-select" id="cboTipoDoc"
                                wire:model="tipoDocumentoIdentidad">
                                <option>==Seleccione una opción==</option>
                                @foreach ($tipoDocumentoIdentidads as $tipoDocumentoIdentidad)
                                    <option value={{ $tipoDocumentoIdentidad->id }}>
                                        {{ $tipoDocumentoIdentidad->descripcion }}</option>
                                @endforeach
                            </select>
                            @error('tipoDocumentoIdentidad')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="txtNumDoc" class="form-label">Numero Documento:</label>
                            <input type="text" class="form-control" id="txtNumDoc"
                                wire:model.lazy="numeroDocumentoIdentidad" style="text-transform:uppercase;"
                                onkeyup="javascript:this.value=this.value.toUpperCase();">
                            @error('numeroDocumentoIdentidad')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="txtTelefono" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" id="txtTelefono"
                                wire:model.lazy="numeroTelefono">
                            @error('numeroTelefono')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="txtdireccionFiscal" class="form-label">Dirección Fiscal:</label>
                            <input type="text" class="form-control" id="txtdireccionFiscal"
                                wire:model.lazy="direccionFiscal" style="text-transform:uppercase;"
                                onkeyup="javascript:this.value=this.value.toUpperCase();">
                            @error('direccionFiscal')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="txtCorreo" class="form-label">E-mail:</label>
                            <input type="email" class="form-control" id="txtCorreo" wire:model.lazy="correo"
                                style="text-transform:uppercase;"
                                onkeyup="javascript:this.value=this.value.toUpperCase();">
                            @error('correo')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="cboEstados" class="form-label">Estado:</label>
                            <select name="estado" class="form-select" id="cboEstados" wire:model="estado">
                                <option>==Seleccione una opción==</option>
                                @foreach ($estados as $estado)
                                    <option value={{ $estado->id }}>{{ $estado->descripcion }}</option>
                                @endforeach
                            </select>
                            @error('estado')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('components.modalfooter')

    {{-- Modal para Eliminar --}}
    @include('components.modaldelete')
</div>
