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

    protected $documentTypeMap = [
        '01' => 1, // Ejemplo: Factura
        '03' => 2, // Ejemplo: Boleta de Venta
        '07' => 3, // Ejemplo: Nota de Crédito
        '08' => 4, // Ejemplo: Nota de Débito
    ];

    public function mount()
    {
        // Obtener la tasa de IGV de la configuración 
        $this->tasaIGV = config('taxes.igv_rate', 0.18); 
    }

    public function processXml()
    {
        $this->reset(['message', 'error']);
        $this->loading = true;

        $this->validate([
            'xmlFile' => 'required|file|mimes:xml|max:5120',
        ], [
            'xmlFile.required' => 'Debe seleccionar un archivo XML.',
            'xmlFile.file'     => 'El archivo seleccionado no es válido.',
            'xmlFile.mimes'    => 'El archivo debe ser de tipo XML.',
            'xmlFile.max'      => 'El tamaño máximo permitido para el archivo es 5MB.',
        ]);

        try {
            $xmlContent = $this->xmlFile->get();
            $xml = new SimpleXMLElement($xmlContent);

            $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $xml->registerXPathNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
            $xml->registerXPathNamespace('i', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
            $xml->registerXPathNamespace('cn', 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2');
            $xml->registerXPathNamespace('dn', 'urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2');

            // --- Extracción de Datos de Cabecera ---
            $documentTypeNodes = $xml->xpath('//cbc:InvoiceTypeCode | //cbc:CreditNoteTypeCode | //cbc:DebitNoteTypeCode');
            $documentType = !empty($documentTypeNodes) ? (string)$documentTypeNodes[0] : '';

            $idNodes = $xml->xpath('//cbc:ID');
            $serieNumero = !empty($idNodes) ? (string)$idNodes[0] : '';
            list($serie, $numero) = array_pad(explode('-', $serieNumero), 2, '');

            $issueDateNodes = $xml->xpath('//cbc:IssueDate');
            $fechaEmision = !empty($issueDateNodes) ? (string)$issueDateNodes[0] : '';

            $currencyCodeNodes = $xml->xpath('//cbc:DocumentCurrencyCode');
            $moneda = !empty($currencyCodeNodes) ? (string)$currencyCodeNodes[0] : 'PEN';

            // --- Datos del Proveedor (AccountingSupplierParty) ---
            $proveedorRucNodes = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID[@schemeID="6"]');
            $proveedorRuc = !empty($proveedorRucNodes) ? (string)$proveedorRucNodes[0] : '';

            $proveedorRazonSocialNodes = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName');
            $proveedorRazonSocial = !empty($proveedorRazonSocialNodes) ? (string)$proveedorRazonSocialNodes[0] : '';


            // --- Montos: Gravado (subTotal), Inafecto, IGV, Total ---
            $subTotalGravado = 0;
            $montoInafecto = 0;
            $igvTotal = 0; // Este es el IGV consolidado de todos los subtotales gravados.
            $total = 0; // Se inicializa también aquí por claridad

            // 1. Extraer el IGV total directamente del TaxTotal principal
            // Este es el cbc:TaxAmount global del documento
            $igvTotalNodes = $xml->xpath('//cac:TaxTotal/cbc:TaxAmount[@currencyID="' . $moneda . '"]');
            $igvTotal = !empty($igvTotalNodes) ? floatval((string)$igvTotalNodes[0]) : 0;

            // 2. Extraer el monto Gravado (base imponible para IGV)
            // Busca el TaxableAmount dentro del TaxSubtotal cuyo TaxScheme ID es '1000' (IGV)
            $subTotalGravadoNodes = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal[cac:TaxCategory/cac:TaxScheme/cbc:ID="1000"]/cbc:TaxableAmount');
            $subTotalGravado = !empty($subTotalGravadoNodes) ? floatval((string)$subTotalGravadoNodes[0]) : 0;

            // 3. Extraer el monto Inafecto/Exonerado
            // En lugar de una XPath amplia con 'or' y luego un foreach,
            // vamos a buscar cada tipo de inafecto/exonerado por separado y sumarlo.
            // Esto asegura que cada monto se agregue una sola vez si solo hay una instancia por ID.

            // Monto Inafecto (Código 9998: INA)
            $inafecto9998Nodes = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal[cac:TaxCategory/cac:TaxScheme/cbc:ID="9998"]/cbc:TaxableAmount');
            $montoInafecto += !empty($inafecto9998Nodes) ? floatval((string)$inafecto9998Nodes[0]) : 0;

            // Monto Exonerado (Código 9997: EXONERADO) - Si aplica en tu sistema
            $inafecto9997Nodes = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal[cac:TaxCategory/cac:TaxScheme/cbc:ID="9997"]/cbc:TaxableAmount');
            $montoInafecto += !empty($inafecto9997Nodes) ? floatval((string)$inafecto9997Nodes[0]) : 0;

            // Monto Gratuito (Código 9996: GRATUITO) - Si aplica en tu sistema
            $inafecto9996Nodes = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal[cac:TaxCategory/cac:TaxScheme/cbc:ID="9996"]/cbc:TaxableAmount');
            $montoInafecto += !empty($inafecto9996Nodes) ? floatval((string)$inafecto9996Nodes[0]) : 0;

            // Monto Exportación (Código 9995: EXPORTACIÓN) - Si aplica en tu sistema
            $inafecto9995Nodes = $xml->xpath('//cac:TaxTotal/cac:TaxSubtotal[cac:TaxCategory/cac:TaxScheme/cbc:ID="9995"]/cbc:TaxableAmount');
            $montoInafecto += !empty($inafecto9995Nodes) ? floatval((string)$inafecto9995Nodes[0]) : 0;

             // El total siempre es el PayableAmount del LegalMonetaryTotal
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
                'subTotalGravado' => $subTotalGravado, // Este irá a 'subTotal' en DB
                'montoInafecto' => $montoInafecto,     // Este irá a 'inafecto' en DB
                'igvTotal' => $igvTotal,               // Este irá a 'igv' en DB
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

            $proveedor = Proveedor::where('numeroDocumentoIdentidad', $proveedorRuc)->first();
            if (!$proveedor) {
                $this->error = 'Proveedor con RUC ' . $proveedorRuc . ' no encontrado en la base de datos. Por favor, registre al proveedor antes de subir la compra.';
                $this->loading = false;
                return;
            }

            $tipoDocumentoId = $this->documentTypeMap[$documentType] ?? null;
            if (is_null($tipoDocumentoId)) {
                $this->error = 'Tipo de documento UBL (' . $documentType . ') no mapeado en el sistema. Asegúrese de que el tipo de documento del XML esté configurado en su "$documentTypeMap" del componente.';
                $this->loading = false;
                return;
            }

            $estadoActivo = Estado::where('descripcion', 'Activo')->first();
            if (!$estadoActivo) {
                 $this->error = 'Estado "Activo" no encontrado en la base de datos. Por favor, cree el estado.';
                 $this->loading = false;
                 return;
            }

            DB::beginTransaction();

            try {
                $compra = Compra::create([
                    'tipoDocumento'    => $tipoDocumentoId,
                    'serie'            => $serie,
                    'numero'           => $numero,
                    'idProveedor'      => $proveedor->id,
                    'idCliente'        => 132,
                    'numeroFile'       => null,
                    'formaPago'        => 'Contado', // Ajustar si viene del XML
                    'fechaEmision'     => Carbon::parse($fechaEmision),
                    'moneda'           => $moneda,
                    'subTotal'         => $subTotalGravado, // Monto Gravado
                    'igv'              => $igvTotal,        // IGV Total
                    'inafecto'         => $montoInafecto,    // Nuevo campo para Inafecto
                    'total'            => $total,            // Total General
                    'totalLetras'      => '',
                    'observacion'      => 'Registrada desde XML. Emisor: ' . $proveedorRazonSocial,
                    'estado'           => $estadoActivo->id,
                    'usuarioCreacion'  => auth()->id(),
                    'usuarioModificacion' => auth()->id(),
                ]);

                $lineas = $xml->xpath('//cac:InvoiceLine | //cac:CreditNoteLine | //cac:DebitNoteLine');

                if (empty($lineas)) {
                    Log::warning('No se encontraron líneas de detalle en el XML para la compra ' . $serieNumero);
                }

                foreach ($lineas as $linea) {
                    $cantidadNodes = $linea->xpath('.//cbc:InvoicedQuantity | .//cbc:CreditedQuantity | .//cbc:DebitedQuantity');
                    $cantidad = !empty($cantidadNodes) ? (string)$cantidadNodes[0] : '1';

                    $unidadMedidaNodes = $linea->xpath('.//cbc:InvoicedQuantity/@unitCode | .//cbc:CreditedQuantity/@unitCode | .//cbc:DebitedQuantity/@unitCode');
                    $unidadMedida = !empty($unidadMedidaNodes) ? (string)$unidadMedidaNodes[0] : 'NIU';

                    $descripcionNodes = $linea->xpath('.//cac:Item/cbc:Description');
                    $descripcion = !empty($descripcionNodes) ? (string)$descripcionNodes[0] : 'Sin descripción';

                    // Para el valor unitario en la línea, el TaxInclusiveAmount es el precio con impuestos
                    // Y el PriceAmount dentro de cac:Price es el valor unitario sin impuestos
                    $valorUnitarioNodes = $linea->xpath('.//cac:Price/cbc:PriceAmount');
                    $valorUnitario = !empty($valorUnitarioNodes) ? (string)$valorUnitarioNodes[0] : '0';

                    // --- NUEVA LÓGICA PARA afectoIgv EN CADA DETALLE ---
                    $afectoIgvLinea = true; // Por defecto, asumimos que está afecto
                    $taxSchemeIdNodes = $linea->xpath('.//cac:TaxTotal/cac:TaxSubtotal/cac:TaxCategory/cac:TaxScheme/cbc:ID');

                    // Si se encuentra un TaxScheme/ID en la línea de detalle
                    if (!empty($taxSchemeIdNodes)) {
                        $taxSchemeId = (string)$taxSchemeIdNodes[0];
                        // Si el ID del esquema tributario NO es '1000' (IGV),
                        // o si es uno de los códigos de inafecto/exonerado, entonces no está afecto.
                        if ($taxSchemeId !== '1000' || in_array($taxSchemeId, ['9998', '9997', '9996', '9995'])) {
                            $afectoIgvLinea = false;
                        }
                    }
                    // Si no se encuentra un TaxScheme/ID, y el monto es 0, podría ser inafecto,
                    // pero el XML UBL generalmente lo indica con un TaxSubtotal.
                    // Nos basamos principalmente en el TaxScheme/ID.

                    // Si hay un monto de IGV en la línea, es afecto.
                    // Esto es una validación adicional si el TaxScheme/ID no es concluyente.
                    $lineTaxAmountNodes = $linea->xpath('.//cac:TaxTotal/cbc:TaxAmount');
                    $lineTaxAmount = !empty($lineTaxAmountNodes) ? floatval((string)$lineTaxAmountNodes[0]) : 0;
                    if ($lineTaxAmount > 0.001) { // Usar un umbral para flotantes
                        $afectoIgvLinea = true;
                    }

                    Log::info("Detalle Linea: " . json_encode([
                        'descripcion' => $descripcion,
                        'cantidad' => $cantidad,
                        'valorUnitario' => $valorUnitario,
                        'afectoIgv' => $afectoIgvLinea, // Valor determinado
                    ]));

                    $compra->detalles()->create([
                        'cantidad'      => floatval($cantidad),
                        'unidadMedida'  => (string)$unidadMedida,
                        'descripcion'   => (string)$descripcion,
                        'valorUnitario' => floatval($valorUnitario),
                        'afectoIgv'     => $afectoIgvLinea, // <-- ¡Guardar este valor!
                        'estado'        => $estadoActivo->id,
                    ]);
                }

                DB::commit();
                $this->message = 'Compra registrada exitosamente desde el XML.';
                $this->reset('xmlFile');

                $this->emit('close-upload-xml-modal');
                $this->emit('refresh-compras-list');

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