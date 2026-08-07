<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Cliente;
use App\Models\Cargo;

class CargosExport implements WithMultipleSheets, ShouldAutoSize
{
    public $idCliente, $fechaInicio, $fechaFin;

    public function __construct($id, $fecIni, $fecFin)
    {
        $this->idCliente = $id;
        $this->fechaInicio = $fecIni;
        $this->fechaFin = $fecFin;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->idCliente == 0) {
            // Opción "Todos los clientes": buscar clientes con deuda (saldo > 0)
            $clientes = Cliente::whereHas('cargos', function ($query) {
                $query->where('idEstado', 1)
                      ->where('saldo', '>', 0)
                      ->whereBetween('fechaEmision', [$this->fechaInicio, $this->fechaFin]);
            })->get();

            foreach ($clientes as $cliente) {
                $sheets[] = new CargosSheet(
                    $cliente->id,
                    $this->fechaInicio,
                    $this->fechaFin
                );
            }

            // Si no hay clientes con deuda, agregar una hoja vacía o de aviso
            if (empty($sheets)) {
                $sheets[] = new CargosSheet(0, $this->fechaInicio, $this->fechaFin, true); // bandera "vacío"
            }
        } else {
            // Cliente específico: una sola hoja
            $sheets[] = new CargosSheet(
                $this->idCliente,
                $this->fechaInicio,
                $this->fechaFin
            );
        }

        return $sheets;
    }
}