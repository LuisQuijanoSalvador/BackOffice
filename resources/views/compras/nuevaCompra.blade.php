@extends('adminlte::page')

@section('title', 'Nueva Compra')

@section('content_header')
    <h3>Nueva Compra</h3>
@stop

{{-- Inicio del contenido de la Página --}}
@section('content')
    @livewire('compras.create-compra')
@stop

{{-- Fin del contenido de la Página --}}

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop