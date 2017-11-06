<?php

namespace Exylon\Fuse\Http\Responses;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;

class EntityResponseBuilder implements Responsable
{


    /**
     * @var
     */
    private $entity;
    /**
     * @var int
     */
    private $status;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var int
     */
    private $options;

    /**
     * @var array|\Illuminate\Http\JsonResponse
     */
    private $defaultJson;

    /**
     * @var \Symfony\Component\HttpFoundation\Response|callable
     */
    private $httpResponse;

    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $view;

    public function __construct($entity, $status = 200, array $headers = [], int $options = 0)
    {

        $this->entity = $entity;
        $this->status = $status;
        $this->headers = $headers;
        $this->options = $options;
        $this->defaultJson = [
            'success' => true
        ];

        $this->responseFactory = app(ResponseFactory::class);

    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $this->entity = $this->entity ?: $this->defaultJson ?: [];

        return $request->expectsJson() ? $this->buildJsonResponse() : $this->buildHttpResponse($request);
    }

    protected function buildJsonResponse()
    {
        if ($this->entity instanceof JsonResponse) {
            return $this->entity;
        }
        return $this->responseFactory->json(
            $this->buildJson($this->entity),
            $this->status,
            $this->headers,
            $this->options);

    }

    protected function buildJson($entity)
    {
        if (is_null($entity)) {
            return [];
        } elseif ($entity instanceof JsonSerializable) {
            return $entity->jsonSerialize();
        } elseif ($entity instanceof Jsonable) {
            return json_decode($entity->toJson(), true);
        } elseif ($entity instanceof Arrayable) {
            return $entity->toArray();
        } else {
            return $entity;
        }
    }

    protected function buildHttpResponse($request)
    {
        if (is_callable($this->httpResponse)) {
            return Container::getInstance()->call($this->httpResponse, [$request]);
        } elseif (!is_null($this->view)) {
            return $this->view;
        } else {
            return $this->httpResponse ?: redirect()->back();
        }
    }

    /**
     * Sets the default JSON response if the entity is null
     *
     * @param array|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Support\Jsonable $jsonResponse
     *
     * @return $this
     */
    public function withDefaultJsonResponse($jsonResponse)
    {
        $this->defaultJson = $jsonResponse ?: [];
        return $this;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response|callable $response
     *
     * @return $this
     */
    public function withHttpResponse($response)
    {
        $this->httpResponse = $response;
        return $this;
    }

    /**
     * @param       $name
     * @param array $data
     * @param array $mergeData
     *
     * @return $this
     */
    public function withView($name, array $data = [], array $mergeData = [])
    {
        if (!is_null($this->entity)) {
            $data['entity'] = $this->entity;
        }
        $this->view = view($name, $data, $mergeData);
        return $this;
    }

    /**
     * @param       $route
     * @param array $parameters
     * @param int   $status
     * @param array $headers
     *
     * @return $this
     */
    public function withRedirectRoute($route, $parameters = [], $status = 302, $headers = [])
    {
        $this->httpResponse = redirect()->route($route, $parameters, $status, $headers);
        return $this;
    }

    public function withRedirectAction($action, $parameters = [], $status = 302, $headers = [])
    {
        $this->httpResponse = redirect()->action($action, $parameters, $status, $headers);
        return $this;
    }
}
