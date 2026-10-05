<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'description'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    // so {{ $room->location }} prints the name
    public function __toString()
    {
        return (string) $this->name;
    }
}
