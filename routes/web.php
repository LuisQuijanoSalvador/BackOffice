<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\loginController;
use App\Http\Controllers\admin\IndexController;
use App\Http\Controllers\entidades\UsuarioController;
use App\Http\Livewire\Entidades\Usuarios;
use App\Http\Controllers\AbonoController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified'
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });

Route::group(['prefix'=>'admin'],function(){
    Route::get('Inicio',[IndexController::class, 'index'])->name('inicio');
});
// Route::group(['prefix'=>'entidades'],function(){
    
// });

Route::middleware(['auth'])->group(function () {
    Route::group(['prefix'=>'gestion'],function(){
        Route::get('boletos', function(){ return view('gestion.boletos');})->name('listaBoletos');
        Route::get('servicios', function(){ return view('gestion.servicios');})->name('listaServicios');
        Route::get('integrador', function(){ return view('gestion.integrador');})->name('integradorBoletos');
    });

    Route::group(['prefix'=>'facturacion'],function(){
        Route::get('inmediata', function(){ return view('gestion.facturacion');})->name('factinmediata');
        Route::get('acumuladaboletos', function(){ return view('gestion.facturacionac');})->name('factboletosac');

        Route::get('inmediataservicios', function(){ return view('gestion.facturacionserv');})->name('factinmediataserv');
        Route::get('acumuladaservicios', function(){ return view('gestion.facturacionservac');})->name('factacumuladaserv');

        Route::get('notascredito', function(){ return view('gestion.notas-credito');})->name('notaCredito');
        
        Route::get('documentos', function(){ return view('gestion.documentos');})->name('listaDocumentos');
        
    });

    Route::group(['prefix'=>'cuentaporcobrar'],function(){
        Route::get('cargos', function(){ return view('cuentas-por-cobrar.abonos');})->name('rCargos');
        Route::get('abonos', function(){ return view('cuentas-por-cobrar.abonosedit');})->name('rAbonosVista');
        Route::get('abonopago/{datosJson}', function($datosJson){
            $datos = json_decode($datosJson, true);
            return view('cuentas-por-cobrar.abonopago', compact('datos'));
        })->name('rAbonopago');

        Route::get('estadodecuenta', function(){ return view('cuentas-por-cobrar.estado-cuenta');})->name('rEstadosdecuenta');
    });

    Route::group(['prefix'=>'entidades'],function(){
        Route::get('usuarios', function(){ return view('entidades.usuarios');})->name('listaUsuarios');
        Route::get('counters', function(){ return view('entidades.counters');})->name('listaCounters');
        Route::get('cobradores', function(){ return view('entidades.cobradors');})->name('listaCobradores');
        Route::get('vendedores', function(){ return view('entidades.vendedors');})->name('listaVendedores');
        Route::get('clientes', function(){ return view('entidades.clientes');})->name('listaClientes');
        Route::get('proveedores', function(){ return view('entidades.proveedors');})->name('listaProveedores');
        Route::get('solicitantes', function(){ return view('entidades.solicitantes');})->name('listaSolicitantes');
        Route::get('aerolineas', function(){ return view('entidades.aerolineas');})->name('listaAerolineas');
    });

    Route::group(['prefix'=>'tablas'],function(){
        Route::get('estados', function(){ return view('tablas.estados');})->name('listaEstados');
        Route::get('roles', function(){ return view('tablas.roles');})->name('listaRoles');
        Route::get('tipodocumentoidentidad', function(){ return view('tablas.tipo-documento-identidad');})->name('listaTipoDocIdentidad');
        Route::get('tipocliente', function(){ return view('tablas.tipo-clientes');})->name('listaTipoCLiente');
        Route::get('tipocambio', function(){ return view('tablas.tipo-cambios');})->name('listaTipoCambio');
        Route::get('tipodocumento', function(){ return view('tablas.tipo-documentos');})->name('listaTipoDocumento');
        Route::get('mediopago', function(){ return view('tablas.medio-pagos');})->name('listaMedioPago');
        Route::get('tipofacturacion', function(){ return view('tablas.tipo-facturacions');})->name('listaTipoFacturacion');
        Route::get('tipopasajero', function(){ return view('tablas.tipo-pasajeros');})->name('listaTipoPasajeros');
        Route::get('tiposervicio', function(){ return view('tablas.tipo-servicios');})->name('listaTipoServicios');
        Route::get('tarjetacredito', function(){ return view('tablas.tarjeta-creditos');})->name('listaTarjetaCreditos');
        Route::get('monedas', function(){ return view('tablas.monedas');})->name('listaMonedas');
        Route::get('areas', function(){ return view('tablas.areas');})->name('listaAreas');
        Route::get('correlativos', function(){ return view('tablas.correlativos');})->name('listaCorrelativos');
        Route::get('gds', function(){ return view('tablas.gdss');})->name('listaGds');
        Route::get('tipoTickets', function(){ return view('tablas.tipo-tickets');})->name('listaTipoTickets');
        Route::get('tipoPagos', function(){ return view('tablas.tipo-pagos');})->name('listaTipoPagos');
        Route::get('bancos', function(){ return view('tablas.bancos');})->name('listaBancos');
    });

    Route::group(['prefix'=>'reportes'],function(){
        Route::get('margenes', function(){ return view('reportes.margenes');})->name('rptMargenes');
        Route::get('conciliacion', function(){ return view('reportes.conciliacion');})->name('rptConciliacion');
        Route::get('comisiones', function(){ return view('reportes.comisiones');})->name('rptComision');
        Route::get('ventas', function(){ return view('reportes.reporte-ventas');})->name('rptVentas');
        Route::get('ventasalltech', function(){ return view('reportes.reporte-alltech');})->name('rptVentasAlltech');
        Route::get('segmentos', function(){ return view('reportes.segmentos');})->name('rptSegmentos');
    });
    Route::group(['prefix'=>'files'],function(){
        Route::get('files', function(){ return view('files.files');})->name('listaFiles');
        Route::get('editarFile/{id}', function(){ return view('files.editar-files');})->name('editarFiles');
    });
    Route::group(['prefix'=>'contabilidad'],function(){
        Route::get('integrador', function(){ return view('contabilidad.integrador');})->name('rIntegrador');
    });
});

