<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;

class Api
{
    const TYPE_INTEGER = 'Integer';
    const TYPE_STRING = 'String';
    const TYPE_TEXT = 'Text';
    const TYPE_DATE_TIME = 'DateTime';

    private $schema;

    private $apiStructure;

    const AVAILABLE_REQUEST_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    private $requestMethod;

    public function __construct($schema, $apiStructure)
    {
        $this->schema = $schema;
        $this->apiStructure = $apiStructure;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->requestMethod, self::AVAILABLE_REQUEST_METHODS)){
            throw new \Error('Method not available');
        }
    }

    public function response($module, $id, $params)
    {
        if ($module !== null && !isset($this->apiStructure[$module])){
            throw new \Error('No module available in the api');
        }

        if ($this->requestMethod === 'GET' && $id !== null) {
            $data = $this->schema->getResult($module, $id);
        } elseif ($this->requestMethod === 'GET') {
            $data = $this->schema->getResults($module);
        } elseif ($this->requestMethod === 'OPTIONS') {
            return $this->sendOptionHeaders();
        } elseif ($this->requestMethod === 'POST') {
            $this->schema->insert($module, $params);
            $data = ['message' => 'ok'];
        } elseif ($this->requestMethod === 'PUT') {
            $this->schema->update($module, $id, $params);
            $data = ['message' => 'ok'];
        } elseif ($this->requestMethod === 'DELETE') {
            $this->schema->delete($module, $id);
            return $this->sendDeleteHeaders();
        }

        $this->sendJsonResponse($data);
    }

    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function sendOptionHeaders()
    {
        header('Access-Control-Allow-Headers: content-type, authorization, x-total-count');
        header('Access-Control-Allow-Methods: GET, OPTIONS, POST, PUT, PATCH, DELETE');
    }

    private function sendDeleteHeaders()
    {
        header('Content-Type: application/json');
    }

}
