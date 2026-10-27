@extends('adminlte::page')

@section('title', 'Generar Asiento Compras')

@section('content_header')
    <h3>Generar Asiento Contable de Compras</h3>
@stop

@section('content')
    @livewire('contabilidad.generar-asiento-compras')
@stop

@section('css')
@stop

@section('js')
    <script>
        // Animación de progreso
        window.addEventListener('start-progress', event => {
            // La barra de progreso ya está animada con Bootstrap
        });

        window.addEventListener('complete-progress', event => {
            Swal.fire({
                icon: 'success',
                title: '¡Archivo Generado!',
                text: 'El archivo Excel está listo para descargar.',
                timer: 3000,
                timerProgressBar: true,
            });
        });

        window.addEventListener('error-progress', event => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al generar el archivo.',
            });
        });
    </script>
@stop