@extends('adminlte::page')

@section('title', 'Registrar Pago')

@section('content_header')
    <h3>Registrar Pago / Abono</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    {{-- Pasamos el $cargoId al componente Livewire --}}
    @livewire('cuentas-por-pagar.pago-cargo-por-pagar', ['cargos' => $cargosIds])
@stop
{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop