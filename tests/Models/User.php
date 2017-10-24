<?php


namespace Tests\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    public function avatars()
    {
        return $this->hasMany(UserAvatar::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function getAgeAttribute()
    {
        return 18;
    }

    public function getGenderAttribute()
    {
        return 'male';
    }
}
