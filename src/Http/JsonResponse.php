<?php

declare(strict_types=1);

namespace ApiGenerator\Http;

use ApiGenerator\Contract\ResponseInterface;

/**
 * Class JsonResponse
 * Handles JSON response formatting and sending
 *
 * @package ApiGenerator\Http
 */
class JsonResponse implements ResponseInterface
{
    /**
     * Send a JSON response
     *
     * @param array $data
     * @return void
     */
    public function send(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Send general CORS headers
     *
     * @return void
     */
    public function sendHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Requested-With, content-type, authorization, x-total-count');
    }
}

