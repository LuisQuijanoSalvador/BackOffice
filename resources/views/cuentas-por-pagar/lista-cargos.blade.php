
@extends('adminlte::page')

@section('title', 'Cargos')

@section('content_header')
    <h3>Cargos</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('cuentas-por-pagar.cargo-por-pagar-list')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop