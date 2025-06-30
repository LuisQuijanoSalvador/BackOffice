@extends('adminlte::page')

@section('title', 'Reporte por Fechas')

@section('content_header')
    <h3>Reporte por Fechas</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('compras.reporte-compras-por-fecha')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop