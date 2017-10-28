<?php

namespace Exylon\Fuse\Http\Responses;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use JsonSerializable;

abstract class HttpEntityResponse implements Responsable
{
    /**
     * @var
     */
    private $entity;

    public function __construct($entity = null)
    {
        $this->entity = $entity;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $factory = Container::getInstance()->make(ResponseFactory::class);
        return $request->expectsJson() ?
            $factory->json($this->buildJson(), $this->getStatusCode(), $this->getHeaders())
            : $this->response();
    }

    protected function buildJson()
    {
        if (is_null($this->entity)) {
            return $this->getDefaultJsonArray();
        } elseif ($this->entity instanceof JsonSerializable) {
            return $this->entity->jsonSerialize();
        } elseif ($this->entity instanceof Jsonable) {
            return json_decode($this->entity->toJson(), true);
        } elseif ($this->entity instanceof Arrayable) {
            return $this->entity->toArray();
        } else {
            return $this->entity;
        }
    }

    /**
     * Default response when not expecting a JSON response
     *
     * @return mixed
     */
    protected abstract function response();

    /**
     * Response status code when expecting JSON
     *
     * @return int
     */
    protected function getStatusCode()
    {
        return 200;
    }

    /**
     * Response headers when expecting JSON
     *
     * @return array
     */
    protected function getHeaders()
    {
        return [];
    }

    /**
     * JSON response array when expecting JSON and entity is null
     *
     * @return array
     */
    protected function getDefaultJsonArray()
    {
        return ['success' => true];
    }


}
