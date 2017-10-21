<?php


namespace Tests\Repositories;


use Tests\Models\User;

class SimpleTransformer
{

    public function __construct(User $user)
    {
        if ($user === null) {
            throw new \InvalidArgumentException('Dependency not injected');
        }
    }

    public function transform(User $model, $metadata)
    {
        return $model->name;
    }
}
