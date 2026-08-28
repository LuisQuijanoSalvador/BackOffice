@extends('adminlte::page')
@section('title', 'Detalle de Abono')
@section('content_header') <h3>Detalle de Abono</h3> @stop
@section('content')
    @livewire('cuentas-por-pagar.abono-detail', ['abonoId' => $abonoId])
@stop
@section('css') @stop
@section('js') <script> console.log('Abono Detail'); </script> @stop