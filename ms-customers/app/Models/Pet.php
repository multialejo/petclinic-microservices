<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pet extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'birth_date', 'type_id', 'owner_id'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
            'type_id' => 'integer',
            'owner_id' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PetType::class, 'type_id');
    }
}
