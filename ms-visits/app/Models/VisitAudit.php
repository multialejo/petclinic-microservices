<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['visit_id', 'pet_id', 'owner_id', 'event', 'detail'];
}
