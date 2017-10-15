<?php


namespace Tests\Models;


use Exylon\Fuse\Support\Eloquent\CascadeDelete;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    use CascadeDelete;

    protected $cascade = [
        'carts'
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
