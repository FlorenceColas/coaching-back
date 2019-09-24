<?php
declare(strict_types=1);

namespace App\Handler;

use App\Model\Athletes;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stdlib\Parameters;

class AthletesHandler implements RequestHandlerInterface
{
    protected $config;
    protected $model;

    public function __construct (array $config, Athletes $model)
    {
        $this->config = $config;
        $this->model  = $model;
    }

    private function get(ServerRequestInterface $request): ResponseInterface
    {
        $key = $request->getAttribute('key', false);
        $params = new Parameters($request->getAttributes());

        if ($key) {
            $data = $this->model->getAthlete($key, $params);
        } else {
            $data = $this->model->getAthletes($params);
        }

        $response = [
            'athletes' => $data,
        ];

        return new JsonResponse($response, StatusCodeInterface::STATUS_OK);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $this->get($request);
            case 'DELETE':
                return $this->delete($request);
            case 'POST':
                return $this->post($request);
            case 'PUT':
                return $this->put($request);
            default:
                throw new \RuntimeException('Method not implemented');
        }
    }

    protected function post(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        $key = $this->model->createAthlete($data);

        return new JsonResponse(['key' => $key], StatusCodeInterface::STATUS_CREATED);
    }

    protected function put(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $id   = $request->getAttribute('id');

        $res = $this->model->updateAthlete($id, $data);

        return new JsonResponse(null, StatusCodeInterface::STATUS_ACCEPTED);
    }

    protected function delete(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id', false);

        $this->model->deleteAthlete($id);

        return new JsonResponse(null, StatusCodeInterface::STATUS_ACCEPTED);
    }
}
