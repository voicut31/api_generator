<?php

declare(strict_types=1);

namespace ApiGenerator\Contract;

/**
 * Interface ResponseInterface
 * @package ApiGenerator\Contract
 */
interface ResponseInterface
{
    /**
     * Send a response
     *
     * @param array $data
     * @return void
     */
    public function send(array $data): void;

    /**
     * Send headers
     *
     * @return void
     */
    public function sendHeaders(): void;
}

