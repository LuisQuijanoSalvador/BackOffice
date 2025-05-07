
@extends('adminlte::page')

@section('title', 'Reporte - Comisiones')

@section('content_header')
    <h3>Reporte - Comisiones</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('reportes.comision-consolidador')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop