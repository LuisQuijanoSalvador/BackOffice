
@extends('adminlte::page')

@section('title', 'Buscar Boleto')

@section('content_header')
    <h3>Buscar Boleto</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('api-boletos.buscar-boleto')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop