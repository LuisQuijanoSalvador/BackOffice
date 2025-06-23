<?php

namespace App\Http\Livewire\Compras;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Proveedor;
use App\Models\TipoDocumento;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Carbon\Carbon;

class UploadXml extends Component
{
    use WithFileUploads;

    public $xmlFile;
    public $message = '';
    public $error = '';
    public $loading = false;

    // Mapeo de tipos de documento UBL a tus IDs de TipoDocumento
    // POR FAVOR, ASEGÚRATE QUE ESTOS ID'S COINCIDAN CON TU TABLA TIPO_DOCUMENTOS
    protected $documentTypeMap = [
        '01' => 1, // Ejemplo: Factura
        '03' => 2, // Ejemplo: Boleta de Venta
        '07' => 3, // Ejemplo: Nota de Crédito
        '08' => 4, // Ejemplo: Nota de Débito
    ];

    public function processXml()
    {
        $this->reset(['message', 'error']);
        $this->loading = true;

        $this->validate([
            'xmlFile' => 'required|file|mimes:xml|max:5120', // Máx 5MB
        ], [
            'xmlFile.required' => 'Debe seleccionar un archivo XML.',
            'xmlFile.file'     => 'El archivo seleccionado no es válido.',
            'xmlFile.mimes'    => 'El archivo debe ser de tipo XML.',
            'xmlFile.max'      => 'El tamaño máximo permitido para el archivo es 5MB.',
        ]);

        try {
            $xmlContent = $this->xmlFile->get();
            $xml = new SimpleXMLElement($xmlContent);

            // Registrar namespaces CLAVE, incluyendo el namespace por defecto 'i'
            $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $xml->registerXPathNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $xml->registerXPathNamespace('i', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2'); // Namespace por defecto para <Invoice>
            $xml->registerXPathNamespace('cn', 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2'); // Para Notas de Crédito
            $xml->registerXPathNamespace('dn', 'urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2'); // Para Notas de Débito
            // No es necesario registrar 'ds', 'qdt', 'udt' si no los usarás en XPath

            // Depuración: Asegurarse que el XML se cargó y los namespaces están OK
            // Log::info('XML Content: ' . $xmlContent);


            // --- Extracción de Datos de Cabecera (más robusta) ---

            // Tipo de Documento: Puede ser Invoice, CreditNote, DebitNote, etc.
            $documentTypeNodes = $xml->xpath('//cbc:InvoiceTypeCode | //cbc:CreditNoteTypeCode | //cbc:DebitNoteTypeCode');
            $documentType = !empty($documentTypeNodes) ? (string)$documentTypeNodes[0] : '';

            // Serie y Número
            $idNodes = $xml->xpath('//cbc:ID');
            $serieNumero = !empty($idNodes) ? (string)$idNodes[0] : '';
            list($serie, $numero) = array_pad(explode('-', $serieNumero), 2, ''); // array_pad para evitar error si no hay '-'

            // Fecha de Emisión
            $issueDateNodes = $xml->xpath('//cbc:IssueDate');
            $fechaEmision = !empty($issueDateNodes) ? (string)$issueDateNodes[0] : '';

            // Moneda
            $currencyCodeNodes = $xml->xpath('//cbc:DocumentCurrencyCode');
            $moneda = !empty($currencyCodeNodes) ? (string)$currencyCodeNodes[0] : 'PEN';


            // --- Datos del Proveedor (AccountingSupplierParty) ---
            // Asegurarse de tomar el RUC y Razón Social del emisor (Supplier)
            $proveedorRucNodes = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeID="6"]');
            $proveedorRuc = !empty($proveedorRucNodes) ? (string)$proveedorRucNodes[0] : '';

            $proveedorRazonSocialNodes = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName');
            $proveedorRazonSocial = !empty($proveedorRazonSocialNodes) ? (string)$proveedorRazonSocialNodes[0] : '';


            // --- Montos (LegalMonetaryTotal y TaxTotal) ---
            $subTotalNodes = $xml->xpath('//cac:LegalMonetaryTotal/cbc:LineExtensionAmount'); // Valor de venta sin impuestos
            $subTotal = !empty($subTotalNodes) ? floatval((string)$subTotalNodes[0]) : 0;

            // IGV: Buscar el TaxAmount del TaxTotal principal (el que no está dentro de TaxSubtotal o es el consolidado)
            // Ojo: Para comprobantes inafectos/exonerados, el IGV es 0 y el código tributario es 9998, 9997, etc.
            $igvNodes = $xml->xpath('//cac:TaxTotal/cbc:TaxAmount[@currencyID="' . $moneda . '"]');
            $igv = !empty($igvNodes) ? floatval((string)$igvNodes[0]) : 0;


            // Total: Siempre es PayableAmount
            $totalNodes = $xml->xpath('//cac:LegalMonetaryTotal/cbc:PayableAmount');
            $total = !empty($totalNodes) ? floatval((string)$totalNodes[0]) : 0;

            // Log para verificar los datos extraídos
            Log::info("XML Data Extracted: " . json_encode([
                'documentType' => $documentType,
                'serie' => $serie,
                'numero' => $numero,
                'fechaEmision' => $fechaEmision,
                'moneda' => $moneda,
                'proveedorRuc' => $proveedorRuc,
                'proveedorRazonSocial' => $proveedorRazonSocial,
                'subTotal' => $subTotal,
                'igv' => $igv,
                'total' => $total,
            ]));


            // Validaciones
            if (empty($serie) || empty($numero)) {
                $this->error = 'No se pudo extraer la serie y/o el número del documento del XML.';
                $this->loading = false;
                return;
            }

            if (empty($proveedorRuc)) {
                 $this->error = 'No se pudo extraer el RUC del proveedor del XML.';
                 $this->loading = false;
                 return;
            }

            // Buscar Proveedor
            $proveedor = Proveedor::where('numeroDocumentoIdentidad', $proveedorRuc)->first();

            if (!$proveedor) {
                $this->error = 'Proveedor con RUC ' . $proveedorRuc . ' no encontrado en la base de datos. Por favor, registre al proveedor antes de subir la compra.';
                $this->loading = false;
                return;
            }

            // Mapear tipo de documento UBL al ID de tu BD
            $tipoDocumentoId = $this->documentTypeMap[$documentType] ?? null;

            if (is_null($tipoDocumentoId)) {
                $this->error = 'Tipo de documento UBL (' . $documentType . ') no mapeado en el sistema. Asegúrese de que el tipo de documento del XML esté configurado en su "$documentTypeMap" del componente.';
                $this->loading = false;
                return;
            }

            // Obtener estado 'Activo'
            $estadoActivo = Estado::where('descripcion', 'Activo')->first();
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
                    'idCliente'        => 132,
                    'numeroFile'       => null,
                    'formaPago'        => 'Contado', // Tomado del XML, o si no, un valor por defecto
                    'fechaEmision'     => Carbon::parse($fechaEmision),
                    'moneda'           => $moneda,
                    'subTotal'         => $subTotal,
                    'igv'              => $igv,
                    'total'            => $total,
                    'totalLetras'      => '', // Esto es complejo de sacar del XML, mejor generarlo después
                    'observacion'      => 'Registrada desde XML. Emisor: ' . $proveedorRazonSocial,
                    'estado'           => $estadoActivo->id,
                    'usuarioCreacion'  => auth()->id(),
                    'usuarioModificacion' => auth()->id(),
                ]);

                // Extraer y crear detalles de la compra
                // La XPath 'InvoiceLine' es para Facturas. Si el documento es NC/ND, buscar 'CreditNoteLine'/'DebitNoteLine'.
                $lineas = $xml->xpath('//cac:InvoiceLine | //cac:CreditNoteLine | //cac:DebitNoteLine');

                if (empty($lineas)) {
                    // Si no se encuentran líneas, podría ser un documento sin detalles (raro para facturas)
                    Log::warning('No se encontraron líneas de detalle en el XML para la compra ' . $serieNumero);
                }

                foreach ($lineas as $linea) {
                    $cantidadNodes = $linea->xpath('.//cbc:InvoicedQuantity | .//cbc:CreditedQuantity | .//cbc:DebitedQuantity');
                    $cantidad = !empty($cantidadNodes) ? (string)$cantidadNodes[0] : '1';

                    $unidadMedidaNodes = $linea->xpath('.//cbc:InvoicedQuantity/@unitCode | .//cbc:CreditedQuantity/@unitCode | .//cbc:DebitedQuantity/@unitCode');
                    $unidadMedida = !empty($unidadMedidaNodes) ? (string)$unidadMedidaNodes[0] : 'NIU';

                    $descripcionNodes = $linea->xpath('.//cac:Item/cbc:Description');
                    $descripcion = !empty($descripcionNodes) ? (string)$descripcionNodes[0] : 'Sin descripción';

                    $valorUnitarioNodes = $linea->xpath('.//cac:Price/cbc:PriceAmount');
                    $valorUnitario = !empty($valorUnitarioNodes) ? (string)$valorUnitarioNodes[0] : '0';

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
                $this->emit('close-upload-xml-modal');
                // IMPORTANTE: Asegúrate de que ListCompras.php esté escuchando este evento
                $this->emit('refresh-compras-list'); // Notifica a ListCompras para que se actualice

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error = 'Error al guardar la compra en la base de datos: ' . $e->getMessage();
                Log::error('Error al procesar XML y guardar compra: ' . $e->getMessage() . ' en línea ' . $e->getLine());
            }

        } catch (\Exception $e) {
            $this->error = 'Error al procesar el archivo XML: ' . $e->getMessage();
            Log::error('Error al cargar/parsear XML: ' . $e->getMessage() . ' en línea ' . $e->getLine());
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.compras.upload-xml');
    }
}