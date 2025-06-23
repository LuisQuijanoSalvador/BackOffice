<div>
    {{-- The best athlete wants his opponent at his best. --}}
    <div class="row g-3 border p-3 rounded mb-3">
        <div class="col-md-2">
            <label for="cantidad-{{ $index }}" class="form-label">Cantidad</label>
            <input type="number" id="cantidad-{{ $index }}" wire:model.blur="item.cantidad" class="form-control">
            @error('item.cantidad') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-2">
            <label for="unidadMedida-{{ $index }}" class="form-label">Unidad</label>
            <input type="text" id="unidadMedida-{{ $index }}" wire:model.blur="item.unidadMedida" class="form-control">
            @error('item.unidadMedida') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-5">
            <label for="descripcion-{{ $index }}" class="form-label">Descripción</label>
            <input type="text" id="descripcion-{{ $index }}" wire:model.blur="item.descripcion" class="form-control">
            @error('item.descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-3">
            <label for="valorUnitario-{{ $index }}" class="form-label">Valor Unitario</label>
            <input type="number" step="0.01" id="valorUnitario-{{ $index }}" wire:model.blur="item.valorUnitario" class="form-control">
            @error('item.valorUnitario') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    </div>
</div>