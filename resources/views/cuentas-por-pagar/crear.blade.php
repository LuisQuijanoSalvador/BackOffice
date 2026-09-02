@extends('adminlte::page')

@section('title', 'Crear Cargo Manual')

@section('content_header')
    <h3>Crear Cargo Manual</h3>
@stop

@section('content')
    @livewire('cuentas-por-pagar.crear-cargo-manual')
@stop

@section('css') @stop
@section('js') <script> console.log('Crear Cargo Manual'); </script> @stop