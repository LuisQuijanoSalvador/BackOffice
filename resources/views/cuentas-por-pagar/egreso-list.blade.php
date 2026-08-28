@extends('adminlte::page')

@section('title', 'Listado de Egresos')

@section('content_header')
    <h3>Listado de Egresos</h3>
@stop

@section('content')
    @livewire('cuentas-por-pagar.egreso-list')
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log('Egresos List'); </script>
@stop