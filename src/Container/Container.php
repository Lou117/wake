<?php
namespace Lou117\Wake\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected array $array = [];


    /**
     * @param string $id
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $id): mixed
    {
        if ($this->has($id) === false) {
            throw new NotFoundException();
        }

        return $this->array[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->array);
    }

    /**
     * Sets given `$entry` under given `$id`.
     *
     * @param string $id
     * @param mixed $entry
     * @return $this
     */
    public function set(string $id, mixed $entry): self
    {
        $this->array[$id] = $entry;
        return $this;
    }
}
