@extends('adminlte::page')

@section('title', 'Cuentas Contables')

@section('content_header')
    <h3>Cuentas Contables (Compras)</h3>
@stop

@section('content')
    @livewire('contabilidad.cuenta-contable-management')
@stop

@section('css')
@stop

@section('js')
    {{-- Script necesario para que los Modals de Bootstrap 4 funcionen perfectamente con Livewire 2 --}}
    <script>
        window.livewire.on('open-modal', () => {
            $('#cuentaModal').modal('show');
        });
        window.livewire.on('close-modal', () => {
            $('#cuentaModal').modal('hide');
        });
    </script>
@stop