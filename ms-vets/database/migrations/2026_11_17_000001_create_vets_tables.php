<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema de ms-vets (bounded context Veterinarios). Paridad con
 * spring-petclinic/src/main/resources/db/mysql/schema.sql: sin timestamps,
 * FK solo dentro del propio dominio (docs/fase2 §2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 30);
            $table->string('last_name', 30)->index();
        });

        Schema::create('specialties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 80)->index();
        });

        Schema::create('vet_specialties', function (Blueprint $table) {
            $table->unsignedInteger('vet_id');
            $table->unsignedInteger('specialty_id');
            $table->foreign('vet_id')->references('id')->on('vets');
            $table->foreign('specialty_id')->references('id')->on('specialties');
            $table->unique(['vet_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_specialties');
        Schema::dropIfExists('specialties');
        Schema::dropIfExists('vets');
    }
};
