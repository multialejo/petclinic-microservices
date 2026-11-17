<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Visita médica. Sin relaciones Eloquent: pet_id apunta a otro microservicio (ms-customers)
 * y solo se valida vía HTTP (docs/fase2 §2, regla crítica de datos).
 */
class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = ['pet_id', 'visit_date', 'description'];

    protected function casts(): array
    {
        return [
            'pet_id' => 'integer',
            'visit_date' => 'date:Y-m-d',
        ];
    }
}
