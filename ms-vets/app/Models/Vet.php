<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vet extends Model
{
    public $timestamps = false;

    protected $fillable = ['first_name', 'last_name'];

    /** Relación interna del dominio Vets (permitida). Ordenadas por nombre, como el As-Is. */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'vet_specialties')->orderBy('name');
    }
}
