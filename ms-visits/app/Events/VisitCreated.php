<?php

namespace App\Events;

use App\Models\Visit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Se dispara al guardar una visita. Lo procesa el worker de colas (Redis) de forma asíncrona. */
class VisitCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Visit $visit,
        public int $ownerId,
        public string $petName,
        public string $ownerName,
    ) {}
}
