<?php

namespace App\Listeners;

use App\Events\VisitCreated;
use App\Models\VisitAudit;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Log;

/**
 * Bonus de arquitectura (Iteración 3): al crear una visita se encola este listener en Redis.
 * El contenedor ms-visits-worker lo ejecuta: registra la auditoría y simula la notificación al dueño.
 * Si falla, se reintenta (tries) y acaba en failed_jobs, sin afectar al 201 ya devuelto al cliente.
 */
class RecordVisitAudit implements ShouldQueueAfterCommit
{
    public int $tries = 3;

    public function handle(VisitCreated $event): void
    {
        $visit = $event->visit;

        VisitAudit::create([
            'visit_id' => $visit->id,
            'pet_id' => $visit->pet_id,
            'owner_id' => $event->ownerId,
            'event' => 'visit.created',
            'detail' => "Visita {$visit->visit_date->toDateString()}: {$visit->description}",
        ]);

        Log::info(sprintf(
            '[notificación simulada] Para %s (owner %d): %s tiene visita el %s - "%s"',
            $event->ownerName,
            $event->ownerId,
            $event->petName,
            $visit->visit_date->toDateString(),
            $visit->description,
        ));
    }
}
