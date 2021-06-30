<?php
/**
 * Created by PhpStorm.
 * User: Voicu Tibea
 * Date: 2019-01-21
 * Time: 17:22
 */

namespace ApiGenerator;

use Error;

/**
 * Class Api
 * @package ApiGenerator
 */
class Api
{
//    const TYPE_INTEGER = 'Integer';
//    const TYPE_STRING = 'String';
//    const TYPE_TEXT = 'Text';
//    const TYPE_DATE_TIME = 'DateTime';
    /**
     *
     */
    public const AVAILABLE_REQUEST_METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var array
     */
    private array $apiStructure;

    /**
     * @var mixed
     */
    private $requestMethod;

    /**
     * Api constructor.
     * @param Schema $schema
     * @param array $apiStructure
     */
    public function __construct(Schema $schema, array $apiStructure = [])
    {
        $this->schema = $schema;
        $this->apiStructure = $apiStructure;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->requestMethod, self::AVAILABLE_REQUEST_METHODS)){
            throw new Error('Method not available');
        }
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function response($module, $id, $params)
    {
        if ($module !== null && !isset($this->apiStructure[$module])){
            throw new Error('No module available in the api');
        }

        $data = [];

        $this->sendGeneralHeaders();
//        var_dump($this->requestMethod); exit;
        switch ($this->requestMethod){
            case 'PUT':
            case 'PATCH':
//                var_dump($params); exit;
                $this->schema->update($module, $id, $params);
                $data = ['message' => 'ok'];
                break;
            case 'GET' && $id !== null:
                $data = $this->schema->getResult($module, $id);
                break;
            case 'GET':
                $data = $this->schema->getResults($module);
                break;
            case 'OPTIONS':
                $this->sendOptionHeaders();
                break;
            case 'POST':
                $this->schema->insert($module, $params);
                $data = ['message' => 'ok'];
                break;
            case 'DELETE':
                $this->schema->delete($module, $id);
                $this->sendDeleteHeaders();
                break;
            default:
                break;
        }

        $this->sendJsonResponse($data);
    }

    /**
     * @param $data
     */
    private function sendJsonResponse($data):void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     *
     */
    private function sendGeneralHeaders():void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: X-Requested-With");
    }

    /**
     *
     */
    private function sendOptionHeaders():void
    {
        header('Access-Control-Allow-Headers: content-type, authorization, x-total-count');
        header('Access-Control-Allow-Methods: GET, OPTIONS, POST, PUT, PATCH, DELETE');
    }

    /**
     *
     */
    private function sendDeleteHeaders():void
    {
        header('Content-Type: application/json');
    }
}
