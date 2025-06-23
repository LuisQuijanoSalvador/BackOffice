<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use Livewire\WithFileUploads; // Importar el trait para subida de archivos
use App\Models\Proveedor;
use App\Models\TipoDocumento;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement; // Para parsear el XML
use Carbon\Carbon; // Para manejar fechas

class UploadXml extends Component
{
    use WithFileUploads;

    public $xmlFile;
    public $message = '';
    public $error = '';
    public $loading = false; // Para mostrar un spinner de carga

    // Mapeo de tipos de documento UBL a tus IDs de TipoDocumento
    // DEBES AJUSTAR ESTO SEGÚN LOS ID'S DE TU TABLA TIPO_DOCUMENTOS
    protected $documentTypeMap = [
        '01' => 1, // Factura
        '03' => 2, // Boleta de Venta (menos común en compras, pero puede existir)
        '07' => 3, // Nota de Crédito (para ajustes en compras)
        '08' => 4, // Nota de Débito (para ajustes en compras)
        // Agrega más si tienes otros tipos de documentos relevantes en tu BD
    ];

    public function processXml()
    {
        $this->reset(['message', 'error']); // Limpiar mensajes anteriores
        $this->loading = true; // Mostrar spinner

        $this->validate([
            'xmlFile' => 'required|file|mimes:xml|max:5120', // Máx 5MB
        ], [
            'xmlFile.required' => 'Debe seleccionar un archivo XML.',
            'xmlFile.file'     => 'El archivo seleccionado no es válido.',
            'xmlFile.mimes'    => 'El archivo debe ser de tipo XML.',
            'xmlFile.max'      => 'El tamaño máximo permitido para el archivo es 5MB.',
        ]);

        try {
            // Obtener el contenido del archivo subido
            $xmlContent = $this->xmlFile->get();

            // Cargar el XML
            $xml = new SimpleXMLElement($xmlContent);

            // Registrar los namespaces para poder hacer xpath correctamente
            // Estos son namespaces comunes en el UBL peruano
            $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $xml->registerXPathNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $xml->registerXPathNamespace('udt', 'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
            $xml->registerXPathNamespace('ccts', 'urn:un:unece:uncefact:data:specification:CoreComponentTypeSchemaModule:2');
            $xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#'); // Para firma digital

            // Extraer datos de la cabecera del documento
            $documentType = (string)$xml->xpath('//cbc:InvoiceTypeCode')[0] ?? ''; // Para Factura
            if (empty($documentType)) {
                 $documentType = (string)$xml->xpath('//cbc:CreditNoteTypeCode')[0] ?? ''; // Para Nota Crédito
            }
             if (empty($documentType)) {
                 $documentType = (string)$xml->xpath('//cbc:DebitNoteTypeCode')[0] ?? ''; // Para Nota Débito
             }
            // Agrega más tipos si es necesario (ej. boletas si las procesaras)


            $serieNumero = (string)$xml->xpath('//cbc:ID')[0] ?? ''; // Ejemplo: F001-00000001
            list($serie, $numero) = explode('-', $serieNumero);

            $fechaEmision = (string)$xml->xpath('//cbc:IssueDate')[0] ?? '';

            // Datos del Proveedor (Emisor del CPE)
            $proveedorRuc = (string)$xml->xpath('//cac:Party[cac:PartyIdentification/cbc:ID/@schemeID="6"]/cac:PartyIdentification/cbc:ID')[0] ?? '';
            $proveedorRazonSocial = (string)$xml->xpath('//cac:Party[cac:PartyIdentification/cbc:ID/@schemeID="6"]/cac:PartyLegalEntity/cbc:RegistrationName')[0] ?? '';

            // Montos
            $subTotal = (string)$xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount')[0] ?? 0;
            $igv = (string)$xml->xpath('//cac:TaxTotal[cac:TaxSubtotal/cac:TaxCategory/cac:TaxScheme/cbc:ID="1000"]/cbc:TaxAmount')[0] ?? 0;
            $total = (string)$xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount')[0] ?? 0; // Total
            $moneda = (string)$xml->xpath('//cbc:DocumentCurrencyCode')[0] ?? 'PEN'; // Moneda del documento

            // Convertir a float
            $subTotal = floatval($subTotal);
            $igv = floatval($igv);
            $total = floatval($total);

            // Buscar Proveedor
            $proveedor = Proveedor::where('ruc', $proveedorRuc)->first();

            if (!$proveedor) {
                // Si el proveedor no existe, puedes decidir:
                // 1. Mostrar un error para que el usuario lo registre primero.
                // 2. Crear automáticamente el proveedor (esto requeriría más campos en el XML para el proveedor).
                $this->error = 'Proveedor con RUC ' . $proveedorRuc . ' no encontrado. Por favor, registre al proveedor antes de subir la compra.';
                $this->loading = false;
                return;
            }

            // Mapear tipo de documento UBL al ID de tu BD
            $tipoDocumentoId = $this->documentTypeMap[$documentType] ?? null;

            if (is_null($tipoDocumentoId)) {
                $this->error = 'Tipo de documento UBL (' . $documentType . ') no mapeado en el sistema.';
                $this->loading = false;
                return;
            }

            // Obtener estado 'Activo' (asumiendo que siempre iniciamos una compra activa)
            $estadoActivo = Estado::where('nombre', 'Activo')->first();
            if (!$estadoActivo) {
                 $this->error = 'Estado "Activo" no encontrado en la base de datos. Por favor, cree el estado.';
                 $this->loading = false;
                 return;
            }

            // Iniciar transacción de base de datos
            DB::beginTransaction();

            try {
                // Crear la Compra
                $compra = Compra::create([
                    'tipoDocumento'    => $tipoDocumentoId,
                    'serie'            => $serie,
                    'numero'           => $numero,
                    'idProveedor'      => $proveedor->id,
                    'idCliente'        => null, // No se suele incluir en XML de compra
                    'numeroFile'       => null, // No se suele incluir en XML
                    'formaPago'        => 'No especificado XML', // O un valor por defecto
                    'fechaEmision'     => Carbon::parse($fechaEmision),
                    'moneda'           => $moneda,
                    'subTotal'         => $subTotal,
                    'igv'              => $igv,
                    'total'            => $total,
                    'totalLetras'      => 'Generar en cliente', // Esto es complejo de sacar del XML, mejor generarlo después
                    'observacion'      => 'Registrada desde XML',
                    'estado'           => $estadoActivo->id,
                    'usuarioCreacion'  => auth()->id(), // Asigna el usuario logueado
                    'usuarioModificacion' => auth()->id(),
                ]);

                // Extraer y crear detalles de la compra
                $lineas = $xml->xpath('//cac:InvoiceLine | //cac:CreditNoteLine | //cac:DebitNoteLine'); // Para diferentes tipos de documentos
                foreach ($lineas as $linea) {
                    $cantidad = (string)$linea->xpath('.//cbc:InvoicedQuantity')[0] ?? (string)$linea->xpath('.//cbc:CreditedQuantity')[0] ?? (string)$linea->xpath('.//cbc:DebitedQuantity')[0] ?? 1;
                    $unidadMedida = (string)$linea->xpath('.//cbc:InvoicedQuantity/@unitCode')[0] ?? (string)$linea->xpath('.//cbc:CreditedQuantity/@unitCode')[0] ?? (string)$linea->xpath('.//cbc:DebitedQuantity/@unitCode')[0] ?? 'NIU';
                    $descripcion = (string)$linea->xpath('.//cac:Item/cbc:Description')[0] ?? 'Sin descripción';
                    $valorUnitario = (string)$linea->xpath('.//cac:Price/cbc:PriceAmount')[0] ?? 0;

                    $compra->detalles()->create([
                        'cantidad'      => floatval($cantidad),
                        'unidadMedida'  => (string)$unidadMedida,
                        'descripcion'   => (string)$descripcion,
                        'valorUnitario' => floatval($valorUnitario),
                        'estado'        => $estadoActivo->id,
                    ]);
                }

                DB::commit();
                $this->message = 'Compra registrada exitosamente desde el XML.';
                $this->reset('xmlFile'); // Limpiar el input de archivo

                // Emitir eventos para cerrar el modal y refrescar la lista de compras
                $this->dispatch('close-upload-xml-modal');
                $this->dispatch('refresh-compras-list'); // Notifica a ListCompras para que se actualice

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error = 'Error al guardar la compra: ' . $e->getMessage();
                Log::error('Error al procesar XML y guardar compra: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            $this->error = 'Error al procesar el archivo XML: ' . $e->getMessage();
            Log::error('Error al cargar XML: ' . $e->getMessage());
        } finally {
            $this->loading = false; // Ocultar spinner
        }
    }


    public function render()
    {
        return view('livewire.compras.upload-xml');
    }
}
