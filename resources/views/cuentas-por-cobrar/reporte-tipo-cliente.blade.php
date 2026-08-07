
@extends('adminlte::page')

@section('title', 'Reporte por Tipo de Cliente')

@section('content_header')
    <h3>Reporte por Tipo de Cliente</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('cuentas-por-cobrar.reporte-tipo-cliente')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop