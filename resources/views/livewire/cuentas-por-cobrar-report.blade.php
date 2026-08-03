<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div class="container-fluid py-4">
        <!-- Título y Botones de Exportación -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="text-dark font-weight-bold">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    CUENTAS POR COBRAR {{ $moneda === 'USD' ? 'DÓLARES' : 'SOLES' }}
                </h2>
            </div>
            <div class="col-md-4 text-right">
                <button wire:click="exportarExcel" class="btn btn-success mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                </button>
                <button wire:click="exportarPDF" wire:loading.attr="disabled" class="btn btn-danger">
                    <i class="fas fa-file-pdf mr-1"></i>
                    <span wire:loading.remove>Exportar PDF</span>
                    <span wire:loading>Generando...</span>
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white font-weight-bold">
                <i class="fas fa-filter mr-1"></i> Filtros de Búsqueda
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaDesde">Fecha Desde</label>
                            <input type="date" wire:model="fechaDesde" class="form-control" id="fechaDesde">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fechaHasta">Fecha Hasta</label>
                            <input type="date" wire:model="fechaHasta" class="form-control" id="fechaHasta">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="clienteId">Cliente</label>
                            <select wire:model="clienteId" class="form-control" id="clienteId">
                                <option value="">Todos los clientes</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->razonSocial }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="moneda">Moneda</label>
                            <select wire:model="moneda" class="form-control" id="moneda">
                                <option value="USD">Dólares (USD)</option>
                                <option value="PEN">Soles (PEN)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipoCliente">Tipo Cliente</label>
                            <select wire:model="tipoCliente" class="form-control" id="tipoCliente">
                                <option value="todos">Todos</option>
                                <option value="corporativo">Corporativo</option>
                                <option value="personal">Personal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Buscar (Razón Social / DNI)</label>
                            <input type="text" wire:model.debounce.300ms="search" class="form-control" id="search"
                                placeholder="Escriba para buscar...">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button wire:click="limpiarFiltros" class="btn btn-secondary btn-block">
                                <i class="fas fa-broom mr-1"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen (Tarjetas) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumen['totalClientes'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-secondary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Cargos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $resumen['totalCargos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monto Total</div>
                        <div class="h5 mb-0 font-weight-bold text-success">
                            {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalMonto'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-danger">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Deuda Vencida</div>
                        <div class="h5 mb-0 font-weight-bold text-danger">
                            {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalDeudaVencida'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos para los gráficos (Livewire actualizará esto, pero está oculto) -->
        <div id="chart-data" data-labels='@json($chartData['labels'])' data-montos='@json($chartData['montos'])'
            data-vencidos='@json($chartData['vencidos'])' data-moneda='{{ $moneda }}' class="d-none">
        </div>
        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white font-weight-bold">Top 10 Clientes - Monto Total</div>
                    <div class="card-body" wire:ignore>
                        <canvas id="chartMontos" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white font-weight-bold">Top 10 Clientes - Deuda Vencida</div>
                    <div class="card-body" wire:ignore>
                        <canvas id="chartVencidos" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Datos -->
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">Detalle de Cuentas por Cobrar</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-left">CLIENTE</th>
                                <th class="text-right">MONTO</th>
                                <th class="text-right">DEUDA VENCIDA</th>
                                <th class="text-center">DIAS DE ATRASO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cargosAgrupados as $cargo)
                                <tr class="{{ $cargo->deuda_vencida > 0 ? 'table-danger' : '' }}">
                                    <td class="align-middle">
                                        <div class="font-weight-bold">{{ $cargo->razonSocial }}</div>
                                        <small class="text-muted">{{ $cargo->numeroDocumentoIdentidad }}</small>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold">
                                        {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($cargo->monto_total, 2) }}
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-danger">
                                        {{ $cargo->deuda_vencida > 0 ? number_format($cargo->deuda_vencida, 2) : '0.00' }}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($cargo->max_dias_atraso > 0)
                                            <span class="badge badge-danger">{{ $cargo->max_dias_atraso }} días</span>
                                        @else
                                            <span class="badge badge-success">Al día</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p>No se encontraron registros con los filtros seleccionados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="thead-light">
                            <tr class="font-weight-bold">
                                <td class="text-left">TOTAL</td>
                                <td class="text-right text-success">
                                    {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalMonto'], 2) }}
                                </td>
                                <td class="text-right text-danger">
                                    {{ $moneda === 'USD' ? '$' : 'S/' }}{{ number_format($resumen['totalDeudaVencida'], 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @if ($cargosAgrupados->hasPages())
                <div class="card-footer bg-white">
                    {{ $cargosAgrupados->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts para Chart.js -->
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            document.addEventListener('livewire:load', function() {
                let chartMontos = null;
                let chartVencidos = null;

                function inicializarGraficos() {
                    // 1. Leer datos del div oculto que Livewire sí actualiza
                    const dataDiv = document.getElementById('chart-data');
                    if (!dataDiv) return;

                    const labels = JSON.parse(dataDiv.getAttribute('data-labels') || '[]');
                    const montos = JSON.parse(dataDiv.getAttribute('data-montos') || '[]');
                    const vencidos = JSON.parse(dataDiv.getAttribute('data-vencidos') || '[]');
                    const moneda = dataDiv.getAttribute('data-moneda') || 'USD';
                    const simbolo = moneda === 'USD' ? '$' : 'S/ ';

                    console.log('Actualizando gráficos con:', labels.length, 'clientes');

                    // 2. Si no hay datos, destruir gráficos existentes y salir
                    if (labels.length === 0) {
                        if (chartMontos) chartMontos.destroy();
                        if (chartVencidos) chartVencidos.destroy();
                        return;
                    }

                    // 3. Gráfico de Barras (Montos)
                    const ctx1 = document.getElementById('chartMontos');
                    if (ctx1) {
                        if (chartMontos) chartMontos.destroy(); // Destruir anterior para evitar superposición

                        chartMontos = new Chart(ctx1, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Monto Total',
                                    data: montos,
                                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1,
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return simbolo + value;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // 4. Gráfico de Dona (Vencidos)
                    const ctx2 = document.getElementById('chartVencidos');
                    if (ctx2) {
                        if (chartVencidos) chartVencidos.destroy();

                        // Filtrar solo clientes con deuda vencida > 0
                        const labelsVencidos = [];
                        const dataVencidos = [];
                        const colors = ['#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#1abc9c', '#3498db', '#9b59b6',
                            '#34495e', '#95a5a6', '#d35400'
                        ];

                        labels.forEach((label, index) => {
                            if (vencidos[index] > 0) {
                                labelsVencidos.push(label);
                                dataVencidos.push(vencidos[index]);
                            }
                        });

                        if (dataVencidos.length > 0) {
                            chartVencidos = new Chart(ctx2, {
                                type: 'doughnut',
                                data: {
                                    labels: labelsVencidos,
                                    datasets: [{
                                        data: dataVencidos,
                                        backgroundColor: colors.slice(0, labelsVencidos.length),
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        }
                                    }
                                }
                            });
                        } else {
                            // Mensaje si no hay deuda vencida
                            ctx2.parentNode.innerHTML =
                                '<div class="text-center text-muted py-5"><i class="fas fa-check-circle fa-3x text-success mb-3"></i><p>No hay deuda vencida en el período seleccionado</p></div>';
                        }
                    }
                }

                // Ejecutar al cargar la página por primera vez
                inicializarGraficos();

                // Ejecutar CADA VEZ que Livewire actualiza el DOM (al cambiar filtros)
                document.addEventListener('livewire:update', function() {
                    setTimeout(() => {
                        inicializarGraficos();
                    }, 100);
                });
            });
        </script>
    @endpush
</div>
