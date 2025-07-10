<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache; // Para almacenar el token
use Illuminate\Support\Facades\Log; // Para loguear errores

class ContinentalTravelApiService
{
    protected $tokenUrl;
    protected $ticketUrl;
    protected $username;
    protected $password;
    protected $cacheKey = 'continental_travel_access_token';
    protected $cacheTtl = 3500; // 3500 segundos, un poco menos que los 3600 de la API para margen

    public function __construct()
    {
        $this->tokenUrl = config('services.continental_travel.token_url');
        $this->ticketUrl = config('services.continental_travel.ticket_url');
        $this->username = config('services.continental_travel.username');
        $this->password = config('services.continental_travel.password');
    }

    /**
     * Obtiene el token de autenticación, desde caché o solicitándolo a la API.
     * @return string|null El access_token si es exitoso, null en caso contrario.
     */
    protected function getAccessToken(): ?string
    {
        // Intenta obtener el token de la caché
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        // Si no está en caché o ha expirado, solicitar uno nuevo
        try {
            $response = Http::asJson()->post($this->tokenUrl, [
                'grant_type' => 'string', // Usar "string" como te indicaron. Si falla, probar con "password"
                'username' => $this->username,
                'password' => $this->password,
            ]);

            $response->throw()->json(); // Lanza una excepción si el estado de la respuesta es un error (4xx o 5xx)

            $data = $response->json();

            if (isset($data['access_token'])) {
                // Guarda el token en caché con un TTL ligeramente menor al de la API
                Cache::put($this->cacheKey, $data['access_token'], $this->cacheTtl);
                return $data['access_token'];
            }

            Log::error('API Continental Travel: No se recibió access_token en la respuesta de token.', ['response' => $data]);
            return null;

        } catch (\Exception $e) {
            Log::error('API Continental Travel: Error al obtener token de autenticación: ' . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Obtiene los datos de un boleto específico desde la API.
     * @param string $ticketNumber El número de boleto a buscar.
     * @return array|null Los datos del boleto si es exitoso, null en caso contrario.
     */
    public function getTicketData(string $ticketNumber): ?array
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::warning('API Continental Travel: No se pudo obtener access_token para solicitar datos de boleto.');
            return null;
        }

        try {
            $response = Http::withToken($accessToken) // Añade el token Bearer automáticamente
                            ->asJson()
                            ->post($this->ticketUrl, [
                                'ticketnumber' => $ticketNumber,
                                'esPayload' => false,
                            ]);

            $response->throw()->json(); // Lanza una excepción si el estado de la respuesta es un error

            $data = $response->json();

            // Aquí puedes añadir más lógica para verificar la respuesta de la API
            // Por ejemplo, si el mensaje "No existe el siguiente boleto" está en un campo específico
            // y quieres devolver null si ese mensaje aparece.
            if (isset($data['Message']) && str_contains($data['Message'], 'No existe el siguiente boleto')) {
                Log::info('API Continental Travel: Boleto no encontrado: ' . $ticketNumber);
                return ['error' => $data['Message']]; // Devuelve el mensaje de error de la API
            }

            return $data;

        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Este catch es para errores HTTP específicos (4xx, 5xx)
            $responseBody = $e->response ? $e->response->body() : 'No response body';
            Log::error('API Continental Travel: Error HTTP al obtener datos de boleto: ' . $e->getMessage(), [
                'status' => $e->response->status(),
                'response_body' => $responseBody,
                'ticket_number' => $ticketNumber,
                'exception' => $e,
            ]);
            return ['error' => 'Error al conectar con la API: ' . $e->getMessage()];
        } catch (\Exception $e) {
            // Este catch es para otros tipos de errores
            Log::error('API Continental Travel: Error general al obtener datos de boleto: ' . $e->getMessage(), ['ticket_number' => $ticketNumber, 'exception' => $e]);
            return ['error' => 'Ocurrió un error inesperado al buscar el boleto.'];
        }
    }
}