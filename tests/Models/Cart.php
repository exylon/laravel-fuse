<?php


namespace Tests\Models;


use Exylon\Fuse\Support\Eloquent\CascadeDelete;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use CascadeDelete;

    protected $cascade = [
        'items'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

}
