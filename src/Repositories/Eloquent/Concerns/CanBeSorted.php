<?php


namespace Exylon\Fuse\Repositories\Eloquent\Concerns;


use Exylon\Fuse\Contracts\Sortable;

trait CanBeSorted
{

    protected $sortableField;
    protected $sortMethod = 'asc';

    public function orderBy($field, $method = 'asc')
    {
        $this->sortableField = $field;
        $this->sortMethod = $method;
        return $this;
    }

    protected function resetSort()
    {
        $this->sortableField = null;
        $this->sortMethod = 'asc';
    }

    protected function applySort($query)
    {
        if(!is_null($this->sortableField)){
            if($this->sortMethod === Sortable::DESCENDING){
                $query->orderBy($this->sortableField, Sortable::DESCENDING);
            } else {
                $query->orderBy($this->sortableField, Sortable::ASCENDING);
            }
        }
        return $query;
    }
}
