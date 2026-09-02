<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Carbon\Carbon;

class PdfParserService
{
    private $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extrae movimientos del estado de cuenta según el banco
     */
    public function extractMovements(string $pdfPath, string $lastFourDigits): array
    {
        $pdf = $this->parser->parseFile($pdfPath);
        $text = $pdf->getText();

        // Detectar banco y extraer movimientos
        if ($this->isInterbank($text)) {
            return $this->parseInterbank($text, $lastFourDigits);
        } elseif ($this->isScotiabank($text)) {
            return $this->parseScotiabank($text, $lastFourDigits);
        } elseif ($this->isBBVA($text)) {
            return $this->parseBBVA($text, $lastFourDigits);
        } elseif ($this->isDinersClub($text)) {
            return $this->parseDinersClub($text, $lastFourDigits);
        }

        throw new \Exception('No se pudo detectar el banco del estado de cuenta.');
    }

    private function isInterbank(string $text): bool
    {
        return str_contains($text, 'Interbank') || str_contains($text, 'THE PLATINUM CARD');
    }

    private function isScotiabank(string $text): bool
    {
        return str_contains($text, 'Scotiabank') || str_contains($text, 'AAdvantage');
    }

    private function isBBVA(string $text): bool
    {
        return str_contains($text, 'BBVA') || str_contains($text, 'VISA SIGNATURE');
    }

    private function isDinersClub(string $text): bool
    {
        return str_contains($text, 'Diners') || str_contains($text, 'DINERS CLUB');
    }

    /**
     * Parser para Interbank
     */
    private function parseInterbank(string $text, string $lastFour): array
    {
        $movements = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Buscar patrón: "DD-Mes Comercio monto"
            if (preg_match('/(\d{1,2})-(\w{3})\s+(.+?)\s+([\d,]+\.\d{2})\s*$/', $line, $matches)) {
                $day = $matches[1];
                $month = $this->convertMonth($matches[2]);
                $description = trim($matches[3]);
                $amount = floatval(str_replace(',', '', $matches[4]));

                if ($amount > 0) {
                    $movements[] = [
                        'fecha' => Carbon::create(null, $month, $day)->format('Y-m-d'),
                        'descripcion' => $description,
                        'monto' => $amount,
                        'banco' => 'Interbank'
                    ];
                }
            }
        }

        return $movements;
    }

    /**
     * Parser para Scotiabank
     */
    private function parseScotiabank(string $text, string $lastFour): array
    {
        $movements = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Buscar patrón: "DD/MM/YY DD/MM/YY DESCRIPCION monto"
            if (preg_match('/(\d{2})\/(\d{2})\/(\d{2})\s+\d{2}\/\d{2}\/\d{2}\s+(.+?)\s+([\d,]+\.\d{2})\s*$/', $line, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = '20' . $matches[3];
                $description = trim($matches[4]);
                $amount = floatval(str_replace(',', '', $matches[5]));

                if ($amount > 0) {
                    $movements[] = [
                        'fecha' => Carbon::create($year, $month, $day)->format('Y-m-d'),
                        'descripcion' => $description,
                        'monto' => $amount,
                        'banco' => 'Scotiabank'
                    ];
                }
            }
        }

        return $movements;
    }

    /**
     * Parser para BBVA
     */
    private function parseBBVA(string $text, string $lastFour): array
    {
        $movements = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Patrón para compras sin cuotas: "DD/MM/YYYY (C) DESCRIPCION PAIS S/ monto $ monto"
            // Ejemplo: "6/6/2026 (C) SKY AIRLINE PERU AQP IATA PERU S/ 0 $352.48"
            if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})\s+\(C\)\s+(.+?)\s+(?:PERU|PE|USA|US)\s+S\/\s*([\d,]+\.?\d*)\s+\$([\d,]+\.?\d*)/i', $line, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $description = trim($matches[4]);
                $amountSoles = floatval(str_replace(',', '', $matches[5]));
                $amountDollars = floatval(str_replace(',', '', $matches[6]));

                // Tomar el monto que sea mayor a 0
                $amount = $amountSoles > 0 ? $amountSoles : $amountDollars;
                $moneda = $amountSoles > 0 ? 'PEN' : 'USD';

                if ($amount > 0) {
                    $movements[] = [
                        'fecha' => Carbon::create($year, $month, $day)->format('Y-m-d'),
                        'descripcion' => $description,
                        'monto' => $amount,
                        'moneda' => $moneda,
                        'banco' => 'BBVA'
                    ];
                }
            }
            // Patrón alternativo para montos en soles solamente
            elseif (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})\s+\(C\)\s+(.+?)\s+(?:PERU|PE|USA|US)\s+S\/\s*([\d,]+\.\d{2})/i', $line, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $description = trim($matches[4]);
                $amount = floatval(str_replace(',', '', $matches[5]));

                if ($amount > 0) {
                    $movements[] = [
                        'fecha' => Carbon::create($year, $month, $day)->format('Y-m-d'),
                        'descripcion' => $description,
                        'monto' => $amount,
                        'moneda' => 'PEN',
                        'banco' => 'BBVA'
                    ];
                }
            }
        }

        return $movements;
    }

    /**
     * Parser para Diners Club
     */
    private function parseDinersClub(string $text, string $lastFour): array
    {
        $movements = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            // Buscar patrón: "DD MMM DD MMM DESCRIPCION monto"
            if (preg_match('/(\d{2})\s+(\w{3})\s+\d{2}\s+\w{3}\s+(.+?)\s+([\d,]+\.\d{2})\s*$/', $line, $matches)) {
                $day = $matches[1];
                $month = $this->convertMonth($matches[2]);
                $description = trim($matches[3]);
                $amount = floatval(str_replace(',', '', $matches[4]));

                if ($amount > 0) {
                    $movements[] = [
                        'fecha' => Carbon::create(null, $month, $day)->format('Y-m-d'),
                        'descripcion' => $description,
                        'monto' => $amount,
                        'banco' => 'Diners Club'
                    ];
                }
            }
        }

        return $movements;
    }

    private function convertMonth(string $monthAbbr): int
    {
        $months = [
            'ENE' => 1,
            'FEB' => 2,
            'MAR' => 3,
            'ABR' => 4,
            'MAY' => 5,
            'JUN' => 6,
            'JUL' => 7,
            'AGO' => 8,
            'SEP' => 9,
            'OCT' => 10,
            'NOV' => 11,
            'DIC' => 12
        ];

        return $months[strtoupper($monthAbbr)] ?? 1;
    }
}
