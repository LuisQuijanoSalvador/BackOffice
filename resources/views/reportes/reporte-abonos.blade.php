
@extends('adminlte::page')

@section('title', 'Reporte - Abonos')

@section('content_header')
    <h3>Reporte - Abonos</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('reportes.reporte-abonos')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop