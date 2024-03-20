
@extends('adminlte::page')

@section('title', 'Reporte - Ventas')

@section('content_header')
    <h3>Reporte - Ventas</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('reportes.reporte-ventas')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop