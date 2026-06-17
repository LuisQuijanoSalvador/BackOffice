<div>
    {{-- Close your eyes. Count to one. That is how long forever feels. --}}
    <div class="div-filtro">
        <input type="text" class="txtFiltro" wire:model="search" placeholder="Filtrar por Descripcion">
        <div>
            <button type="button" class="btn btn-success" wire:click='exportar'>Exportar</button>
            <button type="button" class="btn btn-primary rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal">Nuevo</button>
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
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('descripcion')">
                    Descripcion 
                    @if ($sort == 'descripcion')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('codigo')">
                    Codigo 
                    @if ($sort == 'codigo')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 cursor-pointer" wire:click="order('cuentaContableDolares')">
                    Cuenta 
                    @if ($sort == 'cuentaContableDolares')
                        <i class="fas fa-sort float-right py-1 px-1"></i>
                    @endif
                </th>
                <th scope="col" class="py-1 thAccion">
                    Acción
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tipoServicios as $tipoServicio)

            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td class="py-1">{{$tipoServicio->id}}</td>
                <td class="py-1">{{$tipoServicio->descripcion}}</td>
                <td class="py-1">{{$tipoServicio->codigo}}</td>
                <td class="py-1">{{$tipoServicio->cuentaContableDolares}}</td>
                <td class="py-1">
                    <div class="btn-group text-end" role="group" aria-label="Botones de accion">
                        <button type="button" class="btn btn-outline-primary mr-2 rounded" data-bs-toggle="modal" data-bs-target="#FormularioModal" wire:click='editar("{{$tipoServicio->id}}")'>Editar</button>
                        <button type="button" class="btn btn-danger rounded" data-bs-toggle="modal" data-bs-target="#ModalEliminacion" wire:click='encontrar("{{$tipoServicio->id}}")'>Eliminar</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{$tipoServicios->links()}}
    {{-- Modal para Insertar y Actualizar --}}
    @include('components.modalheader')
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="mb-3">
            <label for="txtFecha" class="form-label">Descripcion:</label>
            <input type="text" class="form-control" id="txtFecha" wire:model="descripcion" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
            @error('descripcion')
                <span class="error">{{$message}}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="txtMonto" class="form-label">Codigo:</label>
            <input type="text" class="form-control" id="txtMonto" wire:model="codigo" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
            @error('codigo')
                <span class="error">{{$message}}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="txtMonto" class="form-label">Cuenta Contable:</label>
            <input type="text" class="form-control" id="txtMonto" wire:model="cuentaContableDolares" style="text-transform:uppercase;" onkeyup="javascript:this.value=this.value.toUpperCase();">
            @error('cuentaContableDolares')
                <span class="error">{{$message}}</span>
            @enderror
        </div>
    @include('components.modalfooter')
    
    {{-- Modal para Eliminar --}}
    @include('components.modaldelete')
</div>
