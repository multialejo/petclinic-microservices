<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialty extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function vets(): BelongsToMany
    {
        return $this->belongsToMany(Vet::class, 'vet_specialties');
    }
}
