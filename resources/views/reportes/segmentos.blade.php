
@extends('adminlte::page')

@section('title', 'Reporte - Segmentos')

@section('content_header')
    <h3>Reporte - Segmentos</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('reportes.rep-segmentos')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop