<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;

class CompraDetalleItem extends Component
{
    public $item; // Propiedad para el array de datos del detalle
    public $index; // Para identificar el ítem en el array principal

    // No necesitamos reglas aquí si solo es para mostrar
    // y la validación de nuevos items se hace en el padre.

    public function mount($item, $index) // Asegúrate de que item siempre llegue con datos
    {
        $this->item = $item;
        $this->index = $index;
    }

    public function render()
    {
        return view('livewire.compras.compra-detalle-item');
    }
}