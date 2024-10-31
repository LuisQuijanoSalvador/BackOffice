<?php
namespace App\Clases;

use App\Models\Correlativo;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class Funciones
{
    public $numero,$idRegistro;

    public function generaFile($tabla){
        $this->numero = 0;
        $this->idRegistro = 0;
        $file = Correlativo::where('tabla',$tabla)->first();
        // $file = Correlativo::find()
        $this->numero = $file->numero;
        $this->idRegistro = $file->id;

        //$correlativo = Correlativo::find($this->idRegistro);
        $nuevoNumero = $this->numero + 1;
        $file->numero =$nuevoNumero;
        $file->usuarioModificacion = auth()->user()->id;
        $file->save();
        
        return $this->numero;
    }

    public function numeroServicio($tabla){
        $this->numero = 0;
        $servicio = Correlativo::where('tabla',$tabla)->first();
        $this->numero = $servicio->numero;

        $nuevoNumero = $this->numero + 1;
        $servicio->numero =$nuevoNumero;
        $servicio->usuarioModificacion = auth()->user()->id;
        $servicio->save();
        
        return $this->numero;
    }

    public function numeroComprobante($tabla){
        $this->numero = 0;
        $comprobante = Correlativo::where('tabla',$tabla)->first();
        $this->numero = $comprobante->numero;
        
        return $this->numero;
    }

    public function grabarCorrelativo($tabla,$numero){
        $nuevoNumero = $numero + 1;

        $comprobante = Correlativo::where('tabla',$tabla)->first();
        $comprobante->numero = $nuevoNumero;
        $comprobante->usuarioModificacion = auth()->user()->id;
        $comprobante->save();
    }

    public function numeroAbono($tabla){
        $this->numero = 0;
        $comprobante = Correlativo::where('tabla',$tabla)->first();
        $this->numero = $comprobante->numero;

        $nuevoNumero = $this->numero + 1;
        $comprobante->numero =$nuevoNumero;
        $comprobante->usuarioModificacion = auth()->user()->id;
        $comprobante->save();
        
        return $this->numero;
    }


    public function enviarCPE($arrayData){
        $client = new Client();
        $jsonData = json_encode($arrayData, JSON_PRETTY_PRINT);
        // dd($jsonData);
        $respuesta = $client->request('POST', 'https://int.sendaefact.pe/webservice/emitir_comprobante', [
        'headers' => [
                        'Authorization' => 'Bearer HfcH40sn5PFtDsN0eGaLTOBXb46Zf5C3I8sym7NESx1ZlAvNnIkpAlzb11Nd',
                        'Content-Type' => 'application/json',
                    ],
        'body' => $jsonData,
                ]);
        $dataRespuesta = json_decode($respuesta->getBody(), true);
        return $dataRespuesta;
    }

    public function enviarDC($arrayData){
        $client = new Client();
        $jsonData = json_encode($arrayData, JSON_PRETTY_PRINT);
        // dd($jsonData);
        $respuesta = $client->request('POST', 'https://int.sendaefact.pe/webservice/emitir_dcto_cobranza', [
        'headers' => [
                        'Authorization' => 'Bearer HfcH40sn5PFtDsN0eGaLTOBXb46Zf5C3I8sym7NESx1ZlAvNnIkpAlzb11Nd',
                        'Content-Type' => 'application/json',
                    ],
        'body' => $jsonData,
                ]);
        $dataRespuesta = json_decode($respuesta->getBody(), true);
        return $dataRespuesta;
    }

    public function anularCPE($arrayData){
        $client = new Client();
        $jsonData = json_encode($arrayData, JSON_PRETTY_PRINT);
        dd("Llego!!!");
        // dd($jsonData);
        $respuesta = $client->request('POST', 'https://int.sendaefact.pe/webservice/anular_comprobante', [
        'headers' => [
                        'Authorization' => 'Bearer HfcH40sn5PFtDsN0eGaLTOBXb46Zf5C3I8sym7NESx1ZlAvNnIkpAlzb11Nd',
                        'Content-Type' => 'application/json',
                    ],
        'body' => $jsonData,
                ]);
        $dataRespuesta = json_decode($respuesta->getBody(), true);
        return $dataRespuesta;
    }
    
}