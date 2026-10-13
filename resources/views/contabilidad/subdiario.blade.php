@extends('adminlte::page')

@section('title', 'Subdiarios')

@section('content_header')
    <h3>Subdiarios Contables</h3>
@stop

@section('content')
    @livewire('contabilidad.subdiario-management')
@stop

@section('css')
@stop

@section('js')
    <script>
        // Eventos para el Modal de Crear/Editar
        window.addEventListener('open-modal', () => {
            $('#subdiarioModal').modal('show');
        });
        window.addEventListener('close-modal', () => {
            $('#subdiarioModal').modal('hide');
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