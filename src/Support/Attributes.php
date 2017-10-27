<?php


namespace Exylon\Fuse\Support;


use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class Attributes implements ArrayAccess, Arrayable, Countable, \IteratorAggregate, Jsonable, JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $aliases;


    /**
     * @var array
     */
    protected $cachedValues = [];

    public function __construct(array $attributes, array $aliases = [])
    {
        $this->attributes = $attributes;
        $this->aliases = $aliases;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->attributes);
    }

    /**
     * Get an iterator for the attributes.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        if (array_key_exists($key, $this->aliases)) {
            $key = $this->aliases[$key];
        }
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function &offsetGet($key)
    {
        if (array_key_exists($key, $this->aliases)) {
            $key = $this->aliases[$key];
        }
        return $this->attributes[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (array_key_exists($key, $this->aliases)) {
            $key = $this->aliases[$key];
        }
        if (is_null($key)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->aliases)) {
            $key = $this->aliases[$key];
        }
        unset($this->attributes[$key]);
        if (array_key_exists($key, $this->cachedValues)) {
            unset($this->cachedValues[$key]);
        }
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->attributes);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->attributes);
    }

    public function &__get($key)
    {

        if (!$this->offsetExists($key)) {
            throw new Exception("Property [{$key}] does not exist on this attributes instance.");
        }
        $value = $this->offsetGet($key);
        if (is_array($value)) {
            if (!array_key_exists($key, $this->cachedValues)) {
                if (\Illuminate\Support\Arr::isAssoc($value)) {
                    $this->cachedValues[$key] = $this->newInstance($value);
                } else {
                    $this->cachedValues[$key] = collect($value)->map(function ($item) {
                        return $this->newInstance($item);
                    });
                }
            }
            $value = $this->cachedValues[$key];
        }
        return $value;
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    protected function newInstance($item)
    {
        return new Attributes($item);
    }
}
