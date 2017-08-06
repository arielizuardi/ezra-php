<?php
namespace App;

use Illuminate\Database\Eloquent\Collection;

abstract class GoogleArrayResponse
{
    protected $collection;
    protected $response;

    /**
     * ArrayResponse constructor.
     * @param Collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $this->response = [];
    }

    abstract public function toTable();

    protected function addColumns($position, ...$values)
    {
        foreach ($values as $index => $value) {
            $this->response[$index][$position] = $value;
        }
    }

    abstract public function toArray();
}