@extends('adminlte::page')

@section('title', 'Conciliación de Tarjetas')

@section('content_header')
    <h3>Conciliación de Tarjetas de Crédito</h3>
@stop

@section('content')
    @livewire('conciliacion.conciliacion-tarjetas')
@stop

@section('css')
@stop

@section('js')
    <script> console.log('Conciliación de Tarjetas'); </script>
@stop