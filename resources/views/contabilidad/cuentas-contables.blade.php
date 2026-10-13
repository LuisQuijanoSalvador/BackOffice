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
        // Escuchar el evento nativo del navegador disparado por dispatchBrowserEvent
        window.addEventListener('open-modal', () => {
            $('#cuentaModal').modal('show');
        });

        window.addEventListener('close-modal', () => {
            $('#cuentaModal').modal('hide');
        });
        
        // Eventos para el Modal de Eliminar
        window.addEventListener('open-delete-modal', () => {
            $('#deleteModal').modal('show');
        });
        window.addEventListener('close-delete-modal', () => {
            $('#deleteModal').modal('hide');
        });
    </script>
@stop