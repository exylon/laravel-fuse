<?php


namespace Tests\Repositories;


use Exylon\Fuse\Repositories\Database\Repository;
use Illuminate\Support\Collection;

class SampleDatabaseRepository extends Repository
{
    public function __construct($tableName, $primaryKeyName = 'id', $withTimestamps = true)
    {
        parent::__construct($tableName, $primaryKeyName, $withTimestamps);

        $this->registerAppendResolver('age', function () {
            return 18;
        });
        $this->registerAppendResolver('gender', function () {
            return 'male';
        });

        $this->registerRelationResolver('avatars', function ($item) {
            $items = Collection::wrap($item);
            $primaryKeys = $items->pluck($this->primaryKeyName);

            $avatars = $this->db->table('user_avatars')
                ->whereIn('user_id', $primaryKeys)
                ->get()
                ->map(function ($avatar
                ) {
                    return $this->transform((array)$avatar);
                });

            $items->transform(function ($entity) use ($avatars) {
                $entity['avatars'] = $avatars->where('user_id', $entity[$this->primaryKeyName]);
                return $entity;
            });

            return (!is_array($item) && !$item instanceof Collection) ? $item->first() : $items;
        });
    }

}
