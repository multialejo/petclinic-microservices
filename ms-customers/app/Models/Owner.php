<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    public $timestamps = false;

    protected $fillable = ['first_name', 'last_name', 'address', 'city', 'telephone'];

    /** Relación interna del dominio Clientes (permitida). Orden por nombre = @OrderBy("name") del monolito original. */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class)->orderBy('name');
    }
}
