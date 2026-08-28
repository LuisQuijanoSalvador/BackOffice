@extends('adminlte::page')
@section('title', 'Listado de Abonos')
@section('content_header') <h3>Listado de Abonos</h3> @stop
@section('content')
    @livewire('cuentas-por-pagar.abono-list')
@stop
@section('css') @stop
@section('js') <script> console.log('Abonos List'); </script> @stop