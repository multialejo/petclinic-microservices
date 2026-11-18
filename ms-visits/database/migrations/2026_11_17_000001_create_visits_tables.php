<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema de ms-visits (bounded context Visitas).
 *
 * visits.pet_id es una REFERENCIA LÓGICA a ms-customers: indexada pero SIN llave foránea
 * (Database-per-Service). La existencia del pet se verifica por HTTP al crear.
 * visit_audits la escribe el worker de colas al procesar el evento VisitCreated (bonus asíncrono).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pet_id')->index();
            $table->date('visit_date');
            $table->string('description', 255);
        });

        Schema::create('visit_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('visit_id')->index();
            $table->unsignedInteger('pet_id');
            $table->unsignedInteger('owner_id');
            $table->string('event', 50);
            $table->string('detail', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_audits');
        Schema::dropIfExists('visits');
    }
};
